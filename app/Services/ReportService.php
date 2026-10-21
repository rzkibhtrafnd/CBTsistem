<?php

namespace App\Services;

use App\Models\Event;
use App\Models\ExamSession;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportService
{
    public function getFilteredEvents(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        return Event::query()
            ->withCount([
                'participants', 
                'tests', 
                'sessions',
                'sessions as finished_sessions_count' => fn ($query) => $query->finished()
            ])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->whereHas('sessions', fn ($sessions) => $sessions->where('status', $status));
            })
            ->latest('start_time')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getEventReport(Event $event): Event
    {
        return $event
            ->loadCount(['participants', 'tests', 'sessions'])
            ->load([
                'tests' => fn ($query) => $query->select(['tests.id', 'title', 'duration_minutes', 'is_active'])
                    ->withCount('questions')
                    ->orderBy('title'),
                
                'participants' => fn ($query) => $query->select(['users.id', 'name', 'email'])
                    ->orderBy('name'),
                
                'sessions' => fn ($query) => $query->select([
                    'id', 'user_id', 'event_id', 'test_id', 'start_time', 'end_time', 'status', 'total_score', 'updated_at'
                ])
                ->with([
                    'user:id,name,email',
                    'test' => fn ($test) => $test->select(['id', 'title'])->withCount('questions'),
                ])
                ->withCount([
                    'answers as answered_count' => fn ($answers) => $answers->answered(),
                    'answers as correct_count' => fn ($answers) => $answers->answered()
                        ->whereHas('choice', fn ($choices) => $choices->correct()),
                ])
                ->latest('updated_at'),
            ]);
    }

    public function getSessionDetails(ExamSession $session): ExamSession
    {
        return $session->load([
            'user:id,name,email',
            'event:id,title',
            'test' => fn ($query) => $query->select(['id', 'title', 'duration_minutes'])->withCount('questions'),
            'test.questions' => fn ($query) => $query->select(['id', 'test_id', 'question_text', 'score_weight'])->orderBy('id'),
            'test.questions.choices' => fn ($query) => $query->select(['id', 'question_id', 'choice_text', 'is_correct'])->orderBy('id'),
            'answers' => fn ($query) => $query->select(['id', 'exam_session_id', 'question_id', 'choice_id', 'is_doubtful']),
            'answers.choice:id,question_id,choice_text,is_correct',
        ]);
    }
}