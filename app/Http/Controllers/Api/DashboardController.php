<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    public function __construct(private ProgressService $progressService) {}

    public function admin(): JsonResponse
    {
        $totalInterns = User::where('role', 'intern')->count();
        $activeInterns = User::where('role', 'intern')->where('is_active', true)->count();
        $totalCourses = Course::count();
        $completedExams = ExamAttempt::whereNotNull('submitted_at')->count();
        $avgScore = ExamAttempt::whereNotNull('submitted_at')->avg('percentage') ?? 0;
        $passRate = ExamAttempt::whereNotNull('submitted_at')->where('passed', true)->count();
        $failedExams = ExamAttempt::whereNotNull('submitted_at')->where('passed', false)->count();

        // Monthly score trend (last 6 months)
        $scoreTrend = ExamAttempt::whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subMonths(6))
            ->selectRaw('DATE_FORMAT(submitted_at, "%Y-%m") as month, AVG(percentage) as avg_score, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top performing interns
        $topInterns = User::where('role', 'intern')
            ->withAvg(['examAttempts as avg_score' => fn($q) => $q->whereNotNull('submitted_at')], 'percentage')
            ->orderByDesc('avg_score')
            ->take(5)
            ->get(['id', 'name', 'email', 'avatar']);

        return $this->success([
            'stats' => [
                'total_interns' => $totalInterns,
                'active_interns' => $activeInterns,
                'total_courses' => $totalCourses,
                'exams_completed' => $completedExams,
                'average_score' => round($avgScore, 1),
                'pass_rate' => $completedExams > 0 ? round(($passRate / $completedExams) * 100, 1) : 0,
                'failed_exams' => $failedExams,
            ],
            'score_trend' => $scoreTrend,
            'top_interns' => $topInterns,
        ]);
    }

    public function intern(): JsonResponse
    {
        $user = auth()->user();
        $progress = $this->progressService->getOverallProgress($user);
        $examStats = $this->progressService->getExamStats($user);

        $inProgressCourses = CourseEnrollment::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->with('course.modules.lessons')
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($e) use ($user) {
                $p = $this->progressService->getCourseProgress($user, $e->course);
                return [
                    'id' => $e->course->id,
                    'title' => $e->course->title,
                    'description' => $e->course->description,
                    'thumbnail' => $e->course->thumbnail,
                    'percentage' => $p['percentage'],
                    'completed_lessons' => $p['completed_lessons'],
                    'total_lessons' => $p['total_lessons'],
                ];
            });

        $upcomingExams = $user->examAttempts()
            ->whereNull('submitted_at')
            ->with('exam.course')
            ->get()
            ->merge(
                \App\Models\ExamAssignment::where('user_id', $user->id)
                    ->with('exam.course')
                    ->get()
                    ->filter(fn($a) => $a->exam->isAvailableNow())
            )
            ->take(5);

        $recentResults = ExamAttempt::where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->with('exam')
            ->latest('submitted_at')
            ->take(5)
            ->get();

        return $this->success([
            'user' => ['name' => $user->name, 'avatar' => $user->avatar],
            'stats' => [
                'courses_assigned' => $progress['courses_enrolled'],
                'courses_completed' => $progress['courses_completed'],
                'exams_completed' => $examStats['total_attempts'],
                'average_score' => $examStats['average_score'],
                'overall_progress' => $progress['overall_percentage'],
            ],
            'in_progress_courses' => $inProgressCourses,
            'upcoming_exams' => $upcomingExams,
            'recent_results' => $recentResults,
        ]);
    }

    public function instructor(): JsonResponse
    {
        $user = auth()->user();
        $myCourses = Course::where('created_by', $user->id)->withCount('enrollments', 'modules')->get();
        $myExams = \App\Models\Exam::where('created_by', $user->id)->withCount('attempts', 'questions')->get();

        return $this->success([
            'my_courses' => $myCourses,
            'my_exams' => $myExams,
            'total_students' => CourseEnrollment::whereIn('course_id', $myCourses->pluck('id'))->distinct('user_id')->count(),
        ]);
    }
}
