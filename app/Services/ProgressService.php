<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;

class ProgressService
{
    /**
     * Get course progress percentage for a user.
     */
    public function getCourseProgress(User $user, Course $course): array
    {
        $course->load('modules.lessons.progress', 'modules.lessons.resources.progress');

        $totalLessons = 0;
        $completedLessons = 0;

        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                $totalLessons++;
                if ($lesson->isCompletedBy($user->id)) {
                    $completedLessons++;
                }
            }
        }

        $percentage = $totalLessons > 0
            ? round(($completedLessons / $totalLessons) * 100)
            : 0;

        return [
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'percentage' => $percentage,
        ];
    }

    /**
     * Get overall intern progress across all enrolled courses.
     */
    public function getOverallProgress(User $user): array
    {
        $enrollments = $user->enrollments()->with('course.modules.lessons')->get();

        if ($enrollments->isEmpty()) {
            return ['courses_enrolled' => 0, 'courses_completed' => 0, 'overall_percentage' => 0];
        }

        $total = $enrollments->count();
        $completed = $enrollments->filter(fn($e) => $e->isCompleted())->count();

        $percentages = $enrollments->map(fn($e) => $this->getCourseProgress($user, $e->course)['percentage']);
        $avg = $percentages->avg();

        return [
            'courses_enrolled' => $total,
            'courses_completed' => $completed,
            'overall_percentage' => round($avg),
        ];
    }

    /**
     * Mark a course as completed if all lessons are done.
     */
    public function checkAndMarkCourseComplete(User $user, Course $course): bool
    {
        $progress = $this->getCourseProgress($user, $course);

        if ($progress['percentage'] === 100) {
            CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->whereNull('completed_at')
                ->update(['completed_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Get intern exam performance stats.
     */
    public function getExamStats(User $user): array
    {
        $attempts = $user->examAttempts()->whereNotNull('submitted_at')->get();

        return [
            'total_attempts' => $attempts->count(),
            'passed' => $attempts->where('passed', true)->count(),
            'failed' => $attempts->where('passed', false)->count(),
            'average_score' => round($attempts->avg('percentage') ?? 0, 1),
        ];
    }
}
