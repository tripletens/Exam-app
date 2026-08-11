<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function getTotalInternsCount(): int;

    public function getActiveInternsCount(): int;

    public function getTotalCoursesCount(): int;

    public function getCompletedExamsCount(): int;

    public function getAverageScore(): float;

    public function getPassedExamsCount(): int;

    public function getFailedExamsCount(): int;

    public function getScoreTrend(int $months = 6): Collection;

    public function getTopInterns(int $limit = 5): Collection;
}
