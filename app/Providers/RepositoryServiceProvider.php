<?php

namespace App\Providers;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Contracts\Services\DashboardServiceInterface;
use App\Repositories\DashboardRepository;
use App\Services\DashboardService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
