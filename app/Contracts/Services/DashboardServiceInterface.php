<?php

namespace App\Contracts\Services;

interface DashboardServiceInterface
{
    public function getAdminDashboardData(): array;

    public function getInternDashboardData(object $user): array;

    public function getInstructorDashboardData(object $user): array;
}
