<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExamIndexRequest;
use App\Http\Requests\ExamRequest;
use App\Models\Test;
use App\Services\ExamService;
use App\Support\PaginationOptions;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExamController extends Controller
{
    public function __construct(private readonly ExamService $examService) {}

    public function index(ExamIndexRequest $request): Response
    {
        $filters = $request->validated();
        $perPage = PaginationOptions::perPage($request);

        return Inertia::render('Admin/Exams/Index', [
            'tests' => $this->examService->getFilteredTests($filters, $perPage),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Exams/Create');
    }

    public function store(ExamRequest $request): RedirectResponse
    {
        $this->examService->createTest($request->validated());

        return redirect()->route('admin.exams.index')->with('success', 'Test berhasil ditambahkan.');
    }

    public function show(Test $exam): Response
    {
        return Inertia::render('Admin/Exams/Show', [
            'exam' => $this->examService->getTestDetails($exam),
        ]);
    }

    public function edit(Test $exam): Response
    {
        return Inertia::render('Admin/Exams/Edit', [
            'exam' => $this->examService->getTestForEditing($exam),
        ]);
    }

    public function update(ExamRequest $request, Test $exam): RedirectResponse
    {
        $this->examService->updateTest($exam, $request->validated());

        return redirect()->route('admin.exams.index')->with('success', 'Data test berhasil diperbarui.');
    }

    public function destroy(Test $exam): RedirectResponse
    {
        $this->examService->deleteTest($exam);

        return redirect()->route('admin.exams.index')->with('success', 'Test berhasil dihapus.');
    }
}
