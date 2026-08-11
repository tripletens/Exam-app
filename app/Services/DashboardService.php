<?php

namespace App\Services;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Contracts\Services\DashboardServiceInterface;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\ExamAssignment;
use App\Models\ExamAttempt;
use App\Services\ProgressService;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        protected DashboardRepositoryInterface $dashboardRepository,
        protected ProgressService $progressService
    ) {}

    public function getAdminDashboardData(): array
    {
        $totalInterns = $this->dashboardRepository->getTotalInternsCount();
        $activeInterns = $this->dashboardRepository->getActiveInternsCount();
        $totalCourses = $this->dashboardRepository->getTotalCoursesCount();
        $completedExams = $this->dashboardRepository->getCompletedExamsCount();
        $avgScore = $this->dashboardRepository->getAverageScore();
        $passCount = $this->dashboardRepository->getPassedExamsCount();
        $failedExams = $this->dashboardRepository->getFailedExamsCount();

        $passRate = $completedExams > 0 ? round(($passCount / $completedExams) * 100, 1) : 0;
        $scoreTrend = $this->dashboardRepository->getScoreTrend(6);
        $topInterns = $this->dashboardRepository->getTopInterns(5);

        return [
            'stats' => [
                'total_interns' => $totalInterns,
                'active_interns' => $activeInterns,
                'total_courses' => $totalCourses,
                'exams_completed' => $completedExams,
                'average_score' => round($avgScore, 1),
                'pass_rate' => $passRate,
                'failed_exams' => $failedExams,
            ],
            'score_trend' => $scoreTrend,
            'top_interns' => $topInterns,
        ];
    }

    public function getInternDashboardData(object $user): array
    {
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
                ExamAssignment::where('user_id', $user->id)
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

        return [
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
        ];
    }

    public function getInstructorDashboardData(object $user): array
    {
        $myCourses = Course::where('created_by', $user->id)->withCount('enrollments', 'modules')->get();
        $myExams = \App\Models\Exam::where('created_by', $user->id)->withCount('attempts', 'questions')->get();

        return [
            'my_courses' => $myCourses,
            'my_exams' => $myExams,
            'total_students' => CourseEnrollment::whereIn('course_id', $myCourses->pluck('id'))->distinct('user_id')->count(),
        ];
    }
}
