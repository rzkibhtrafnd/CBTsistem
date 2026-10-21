<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentExamController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', '/login');

// Route Utama (Redirector Multi-Role)
Route::middleware('auth')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// GROUP ROUTE ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('dashboard');

    Route::resource('exams', ExamController::class);
    Route::resource('events', EventController::class);
    Route::resource('users', UserController::class);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{event}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/sessions/{examSession}', [ReportController::class, 'session'])->name('reports.session');
});

// GROUP ROUTE STUDENT
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
    Route::get('/exams', [StudentExamController::class, 'index'])->name('exams.index');
    Route::get('/events/{event}/tests/{exam}/lobby', [StudentExamController::class, 'lobby'])->name('exams.lobby');
    Route::post('/events/{event}/tests/{exam}/start', [StudentExamController::class, 'start'])->name('exams.start');
    Route::get('/events/{event}/tests/{exam}/session', [StudentExamController::class, 'session'])->name('exams.session');
    Route::patch('/events/{event}/tests/{exam}/questions/{question}/answer', [StudentExamController::class, 'saveAnswer'])->name('exams.answers.update');
    Route::post('/events/{event}/tests/{exam}/finish', [StudentExamController::class, 'finish'])->name('exams.finish');
    Route::get('/events/{event}/tests/{exam}/result', [StudentExamController::class, 'result'])->name('exams.result');
});

// GROUP ROUTE PROFILE
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
