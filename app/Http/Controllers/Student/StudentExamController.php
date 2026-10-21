<?php

namespace App\Http\Controllers\Student;

use App\Enums\ExamSessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\FinishExamRequest;
use App\Http\Requests\Student\SaveExamAnswerRequest;
use App\Http\Requests\Student\StartExamRequest;
use App\Http\Requests\Student\StudentExamIndexRequest;
use App\Models\Event;
use App\Models\Question;
use App\Models\Test;
use App\Services\StudentExamService;
use App\Support\PaginationOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentExamController extends Controller
{
    public function __construct(private readonly StudentExamService $studentExamService) {}

    public function index(StudentExamIndexRequest $request): Response
    {
        $filters = $request->validated();

        return Inertia::render('Student/Exams/Index', [
            'exams' => $this->studentExamService->getStudentExams(
                $request->user(),
                $filters,
                PaginationOptions::perPage($request),
            ),
            'filters' => $filters,
        ]);
    }

    public function lobby(Request $request, Event $event, Test $exam): Response|RedirectResponse
    {
        $lobby = $this->studentExamService->getLobby($event, $exam, $request->user());

        return Inertia::render('Student/Exams/Lobby', $lobby);
    }

    public function start(StartExamRequest $request, Event $event, Test $exam): RedirectResponse
    {
        $session = $this->studentExamService->start($event, $exam, $request->user());

        if ($session->status === ExamSessionStatus::FINISHED) {
            return redirect()->route('student.exams.result', [$event, $exam])->with('info', 'Ujian ini sudah selesai dikerjakan.');
        }

        return redirect()->route('student.exams.session', [$event, $exam])->with('success', 'Sesi ujian berhasil dimulai.');
    }

    public function session(Request $request, Event $event, Test $exam): Response
    {
        $session = $this->studentExamService->getSession($event, $exam, $request->user());

        return Inertia::render('Student/Exams/Session', [
            'exam' => $session->test,
            'session' => $session,
        ]);
    }

    public function saveAnswer(SaveExamAnswerRequest $request, Event $event, Test $exam, Question $question): RedirectResponse
    {
        $this->studentExamService->saveAnswer($event, $exam, $question, $request->user(), $request->validated());

        return back()->with('success', 'Jawaban berhasil disimpan.');
    }

    public function finish(FinishExamRequest $request, Event $event, Test $exam): RedirectResponse
    {
        $this->studentExamService->finish($event, $exam, $request->user());

        return redirect()->route('student.exams.result', [$event, $exam])->with('success', 'Ujian berhasil diselesaikan.');
    }

    public function result(Request $request, Event $event, Test $exam): Response
    {
        return Inertia::render('Student/Exams/Result', [
            ...$this->studentExamService->getResult($event, $exam, $request->user()),
        ]);
    }
}
