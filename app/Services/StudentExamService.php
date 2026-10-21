<?php

namespace App\Services;

use App\Enums\ExamSessionStatus;
use App\Models\Event;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Test;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class StudentExamService
{
    public function getStudentExams(User $student, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $events = Event::query()
            ->where('is_active', true)
            ->where('end_time', '>=', now())
            ->whereHas('participants', fn ($q) => $q->where('users.id', $student->id))
            ->with(['tests' => fn ($q) => $q->where('is_active', true)->withCount('questions')])
            ->get();

        if ($events->isEmpty()) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $sessions = ExamSession::query()
            ->where('user_id', $student->id)
            ->whereIn('event_id', $events->pluck('id'))
            ->get()
            ->keyBy(fn ($session) => "{$session->event_id}_{$session->test_id}");

        $rows = [];
        $now = now();
        $sessionsToComplete = [];

        foreach ($events as $event) {
            if ($event->start_time->gt($now)) {
                continue;
            }

            foreach ($event->tests as $test) {
                if (!empty($filters['search']) && stripos($test->title, $filters['search']) === false) {
                    continue;
                }

                $session = $sessions->get("{$event->id}_{$test->id}");
                $status = $session ? $session->status->value : 'available';

                if ($session && $session->status === ExamSessionStatus::ONGOING && $session->end_time->lte($now)) {
                    $sessionsToComplete[] = $session->id;
                    $status = ExamSessionStatus::FINISHED->value;
                }

                if (!empty($filters['status']) && $filters['status'] !== $status) {
                    continue;
                }

                $rows[] = (object) [
                    'id'       => "{$event->id}-{$test->id}",
                    'event_id' => $event->id,
                    'test_id'  => $test->id,
                    'event'    => $event,
                    'test'     => $test,
                    'status'   => $status,
                ];
            }
        }

        if (!empty($sessionsToComplete)) {
            $this->bulkCompleteSessions($sessionsToComplete);
        }

        usort($rows, fn ($a, $b) => $a->event->start_time <=> $b->event->start_time);

        $total = count($rows);
        $page = max(1, request()->integer('page', 1));
        $items = array_slice($rows, ($page - 1) * $perPage, $perPage);

        return new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query()
        ]);
    }

    public function getLobby(Event $event, Test $exam, User $student): array
    {
        $this->authorizeEventTest($event, $exam, $student);

        $session = $this->sessionQuery($event, $exam, $student)->with('event:id,title')->first();

        if ($session && $session->status === ExamSessionStatus::ONGOING && now()->greaterThanOrEqualTo($session->end_time)) {
            $this->completeSession($session);
            $session->status = ExamSessionStatus::FINISHED;
        }

        if ($session && $session->status === ExamSessionStatus::FINISHED) {
            throw new HttpResponseException(
                redirect()->route('student.exams.result', [$event, $exam])
                    ->with('info', 'Ujian ini sudah selesai dikerjakan.')
            );
        }

        if (!$session && !$this->isEventValidForNewSession($event)) {
            abort(404, 'Ujian tidak tersedia atau waktu telah habis.');
        }

        return [
            'exam'    => $exam->loadCount('questions'),
            'event'   => $event,
            'session' => $session,
        ];
    }

    public function start(Event $event, Test $exam, User $student): ExamSession
    {
        return DB::transaction(function () use ($event, $exam, $student) {
            $this->authorizeEventTest($event, $exam, $student);

            $existingSession = $this->sessionQuery($event, $exam, $student)->first();
            if ($existingSession) {
                return $existingSession;
            }

            abort_unless($this->isEventValidForNewSession($event), 404, 'Acara ujian belum dimulai atau sudah berakhir.');

            $questionIds = $exam->questions()->orderBy('id')->pluck('id');
            abort_if($questionIds->isEmpty(), 422, 'Ujian belum memiliki soal.');

            $startTime = now();
            $calculatedEndTime = $startTime->copy()->addMinutes($exam->duration_minutes);
            $endTime = $calculatedEndTime->gt($event->end_time) ? $event->end_time : $calculatedEndTime;

            $session = $exam->sessions()->create([
                'user_id'    => $student->id,
                'event_id'   => $event->id,
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'status'     => ExamSessionStatus::ONGOING,
            ]);

            $answersData = $questionIds->map(fn ($qId) => [
                'exam_session_id' => $session->id,
                'question_id'     => $qId,
                'choice_id'       => null,
                'is_doubtful'     => false,
                'created_at'      => $startTime,
                'updated_at'      => $startTime,
            ])->toArray();

            UserAnswer::insert($answersData);

            return $session;
        });
    }

    public function getSession(Event $event, Test $exam, User $student): ExamSession
    {
        $session = $this->sessionQuery($event, $exam, $student)->firstOrFail();
        
        $this->finishIfExpired($session);
        abort_unless($session->status === ExamSessionStatus::ONGOING, 403, 'Sesi Ujian Telah Berakhir.');

        $session->load([
            'event:id,title',
            'test.questions' => fn ($query) => $query->select(['id', 'test_id', 'question_text'])->orderBy('id'),
            'test.questions.choices' => fn ($query) => $query->select(['id', 'question_id', 'choice_text'])->orderBy('id'),
            'answers' => fn ($query) => $query->select(['id', 'exam_session_id', 'question_id', 'choice_id', 'is_doubtful']),
        ]);

        return $session;
    }

    public function saveAnswer(Event $event, Test $exam, Question $question, User $student, array $data): UserAnswer
    {
        abort_unless($question->test_id === $exam->id, 404);

        $answer = DB::transaction(function () use ($event, $exam, $question, $student, $data) {
            $session = $this->sessionQuery($event, $exam, $student)->lockForUpdate()->firstOrFail();

            if ($session->status !== ExamSessionStatus::ONGOING || now()->greaterThanOrEqualTo($session->end_time)) {
                $this->completeSession($session);
                return null;
            }

            if ($data['choice_id'] !== null) {
                $validChoice = DB::table('choices')
                    ->where('id', $data['choice_id'])
                    ->where('question_id', $question->id)
                    ->exists();
                
                abort_unless($validChoice, 422, 'Pilihan jawaban tidak sesuai dengan soal.');
            }

            $answer = $session->answers()->where('question_id', $question->id)->firstOrFail();
            
            $answer->update([
                'choice_id'   => $data['choice_id'],
                'is_doubtful' => $data['is_doubtful'],
            ]);

            return $answer;
        });

        if (!$answer) {
            throw new HttpResponseException(
                redirect()->route('student.exams.result', [$event, $exam])
                    ->with('info', 'Waktu ujian habis. Jawaban tersimpan telah dinilai otomatis.')
            );
        }

        return $answer;
    }

    public function finish(Event $event, Test $exam, User $student): ExamSession
    {
        return DB::transaction(function () use ($event, $exam, $student) {
            $session = $this->sessionQuery($event, $exam, $student)->lockForUpdate()->firstOrFail();
            
            if ($session->status === ExamSessionStatus::ONGOING) {
                $this->completeSession($session);
            }

            return $session->fresh();
        });
    }

    public function getResult(Event $event, Test $exam, User $student): array
    {
        $session = $this->sessionQuery($event, $exam, $student)
            ->where('status', ExamSessionStatus::FINISHED)
            ->with([
                'test:id,title', 
                'event:id,title', 
                'answers.question:id,score_weight', 
                'answers.choice:id,is_correct'
            ])
            ->firstOrFail();

        $answers = $session->answers;
        
        $answeredCount = 0;
        $correctCount = 0;
        
        foreach ($answers as $answer) {
            if ($answer->choice_id !== null) {
                $answeredCount++;
                if ($answer->choice && $answer->choice->is_correct) {
                    $correctCount++;
                }
            }
        }

        $maxScore = (int) DB::table('questions')->where('test_id', $session->test_id)->sum('score_weight');
        $score = (float) $session->total_score;

        return [
            'exam'    => $session->test,
            'event'   => $session->event,
            'session' => [
                'start_time'  => $session->start_time,
                'end_time'    => $session->end_time,
                'updated_at'  => $session->updated_at,
                'total_score' => $score,
                'event_id'    => $session->event_id,
            ],
            'summary' => [
                'score'           => $score,
                'max_score'       => $maxScore,
                'percentage'      => $maxScore > 0 ? round(($score / $maxScore) * 100, 1) : 0,
                'total_questions' => $answers->count(),
                'answered'        => $answeredCount,
                'correct'         => $correctCount,
                'incorrect'       => $answeredCount - $correctCount,
                'unanswered'      => $answers->count() - $answeredCount,
            ],
        ];
    }

    private function sessionQuery(Event $event, Test $exam, User $student)
    {
        return ExamSession::query()
            ->where('test_id', $exam->id)
            ->where('event_id', $event->id)
            ->where('user_id', $student->id);
    }

    private function completeSession(ExamSession $session): void
    {
        $score = UserAnswer::query()
            ->where('exam_session_id', $session->id)
            ->whereNotNull('choice_id')
            ->join('choices', 'user_answers.choice_id', '=', 'choices.id')
            ->where('choices.is_correct', true)
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->sum('questions.score_weight');

        $session->update([
            'status'      => ExamSessionStatus::FINISHED,
            'total_score' => $score,
        ]);
    }

    private function bulkCompleteSessions(array $sessionIds): void
    {
        foreach ($sessionIds as $id) {
            $session = ExamSession::find($id);
            if ($session) $this->completeSession($session);
        }
    }

    private function finishIfExpired(ExamSession $session): void
    {
        if ($session->status === ExamSessionStatus::ONGOING && now()->greaterThanOrEqualTo($session->end_time)) {
            $this->completeSession($session);
            throw new HttpResponseException(
                redirect()->route('student.exams.result', [$session->event_id, $session->test_id])
                    ->with('info', 'Waktu ujian habis. Jawaban yang tersimpan telah dinilai.')
            );
        }
    }

    private function isEventValidForNewSession(Event $event): bool
    {
        return $event->is_active && $event->start_time->lte(now()) && $event->end_time->gte(now());
    }

    private function authorizeEventTest(Event $event, Test $test, User $student): void
    {
        $isAuthorized = DB::table('event_test')->where('event_id', $event->id)->where('test_id', $test->id)->exists()
            && DB::table('event_user')->where('event_id', $event->id)->where('user_id', $student->id)->exists();
        
        abort_unless($isAuthorized, 404, 'Data tidak ditemukan atau Anda tidak memiliki akses.');
    }
}