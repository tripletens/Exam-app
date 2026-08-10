<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExamAttemptController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ─── Public ─────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// ─── Authenticated ───────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Courses
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);
    Route::get('/courses/{course}/progress', [CourseController::class, 'myProgress']);

    // Modules
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/modules/{module}', [ModuleController::class, 'show']);

    // Lessons
    Route::get('/lessons/{lesson}', [LessonController::class, 'show']);
    Route::post('/lessons/{lesson}/complete', [LessonController::class, 'markComplete']);

    // Resources
    Route::get('/resources', [ResourceController::class, 'index']);
    Route::get('/resources/{resource}', [ResourceController::class, 'show']);
    Route::post('/resources/{resource}/complete', [ResourceController::class, 'markComplete']);

    // Exams
    Route::get('/exams', [ExamController::class, 'index']);
    Route::get('/exams/{exam}', [ExamController::class, 'show']);

    // Exam Attempts (interns)
    Route::post('/exams/{exam}/start', [ExamAttemptController::class, 'start']);
    Route::get('/exam-attempts/{attempt}/time-remaining', [ExamAttemptController::class, 'timeRemaining']);
    Route::post('/exam-attempts/{attempt}/save-answer', [ExamAttemptController::class, 'saveAnswer']);
    Route::post('/exam-attempts/{attempt}/submit', [ExamAttemptController::class, 'submit']);
    Route::get('/exam-attempts/{attempt}', [ExamAttemptController::class, 'show']);

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index']);

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download']);

    // Dashboards
    Route::get('/dashboard/intern', [DashboardController::class, 'intern']);
    Route::get('/dashboard/instructor', [DashboardController::class, 'instructor']);

    // ─── Admin + Instructor ───────────────────────────────────────────────────
    Route::middleware('role:super_admin|instructor')->group(function () {
        // Courses
        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{course}', [CourseController::class, 'update']);

        // Modules
        Route::post('/modules', [ModuleController::class, 'store']);
        Route::put('/modules/{module}', [ModuleController::class, 'update']);
        Route::delete('/modules/{module}', [ModuleController::class, 'destroy']);

        // Lessons
        Route::post('/lessons', [LessonController::class, 'store']);
        Route::put('/lessons/{lesson}', [LessonController::class, 'update']);
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy']);

        // Resources
        Route::post('/resources', [ResourceController::class, 'store']);
        Route::put('/resources/{resource}', [ResourceController::class, 'update']);
        Route::delete('/resources/{resource}', [ResourceController::class, 'destroy']);

        // Exams
        Route::post('/exams', [ExamController::class, 'store']);
        Route::put('/exams/{exam}', [ExamController::class, 'update']);
        Route::post('/exams/{exam}/publish', [ExamController::class, 'publish']);
        Route::post('/exams/{exam}/unpublish', [ExamController::class, 'unpublish']);

        // Questions
        Route::apiResource('/questions', QuestionController::class)->except(['index', 'show']);
        Route::get('/questions', [QuestionController::class, 'index']);
    });

    // ─── Super Admin only ─────────────────────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        // Dashboard
        Route::get('/dashboard/admin', [DashboardController::class, 'admin']);

        // Courses
        Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
        Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll']);

        // Exams
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy']);
        Route::post('/exams/{exam}/assign', [ExamController::class, 'assign']);

        // Users (intern management)
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);

        // Announcements
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy']);

        // Certificates
        Route::post('/certificates', [CertificateController::class, 'issue']);
        Route::delete('/certificates/{certificate}', [CertificateController::class, 'destroy']);

        // Reports
        Route::get('/reports/intern-performance', [ReportController::class, 'internPerformance']);
        Route::get('/reports/course-completion', [ReportController::class, 'courseCompletion']);
        Route::get('/reports/exam-performance', [ReportController::class, 'examPerformance']);
        Route::get('/reports/export', [ReportController::class, 'exportCsv']);
    });
});
