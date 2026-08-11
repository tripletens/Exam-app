<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\DashboardServiceInterface;
use Illuminate\Http\JsonResponse;

class DashboardController extends BaseApiController
{
    public function __construct(private DashboardServiceInterface $dashboardService) {}

    public function admin(): JsonResponse
    {
        return $this->success($this->dashboardService->getAdminDashboardData());
    }

    public function intern(): JsonResponse
    {
        return $this->success($this->dashboardService->getInternDashboardData(auth()->user()));
    }

    public function instructor(): JsonResponse
    {
        return $this->success($this->dashboardService->getInstructorDashboardData(auth()->user()));
    }
}
