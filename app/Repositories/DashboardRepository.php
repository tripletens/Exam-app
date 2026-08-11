<?php

namespace App\Repositories;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Models\Course;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getTotalInternsCount(): int
    {
        return User::where('role', 'intern')->count();
    }

    public function getActiveInternsCount(): int
    {
        return User::where('role', 'intern')->where('is_active', true)->count();
    }

    public function getTotalCoursesCount(): int
    {
        return Course::count();
    }

    public function getCompletedExamsCount(): int
    {
        return ExamAttempt::whereNotNull('submitted_at')->count();
    }

    public function getAverageScore(): float
    {
        return (float) (ExamAttempt::whereNotNull('submitted_at')->avg('percentage') ?? 0);
    }

    public function getPassedExamsCount(): int
    {
        return ExamAttempt::whereNotNull('submitted_at')->where('passed', true)->count();
    }

    public function getFailedExamsCount(): int
    {
        return ExamAttempt::whereNotNull('submitted_at')->where('passed', false)->count();
    }

    public function getScoreTrend(int $months = 6): Collection
    {
        $driver = config('database.default');
        $dateFormatRaw = $driver === 'pgsql'
            ? "TO_CHAR(submitted_at, 'YYYY-MM') as month, AVG(percentage) as avg_score, COUNT(*) as count"
            : 'DATE_FORMAT(submitted_at, "%Y-%m") as month, AVG(percentage) as avg_score, COUNT(*) as count';

        return ExamAttempt::whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subMonths($months))
            ->selectRaw($dateFormatRaw)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function getTopInterns(int $limit = 5): Collection
    {
        return User::where('role', 'intern')
            ->withAvg(['examAttempts as avg_score' => fn($q) => $q->whereNotNull('submitted_at')], 'percentage')
            ->orderByDesc('avg_score')
            ->take($limit)
            ->get(['id', 'name', 'email', 'avatar']);
    }
}
