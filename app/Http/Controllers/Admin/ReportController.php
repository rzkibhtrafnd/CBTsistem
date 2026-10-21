<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportIndexRequest;
use App\Models\Event;
use App\Models\ExamSession;
use App\Services\ReportService;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function index(ReportIndexRequest $request): Response
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 10);

        return Inertia::render('Admin/Reports/Index', [
            'events' => $this->reportService->getFilteredEvents($filters, $perPage),
            'filters' => $filters,
        ]);
    }

    public function show(Event $event): Response
    {
        return Inertia::render('Admin/Reports/Show', [
            'event' => $this->reportService->getEventReport($event),
        ]);
    }

    public function session(ExamSession $examSession): Response
    {
        return Inertia::render('Admin/Reports/Session', [
            'session' => $this->reportService->getSessionDetails($examSession),
        ]);
    }
}
