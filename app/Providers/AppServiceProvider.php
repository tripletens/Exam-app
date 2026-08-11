<?php

namespace App\Providers;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Contracts\Repositories\LessonRepositoryInterface;
use App\Contracts\Services\DashboardServiceInterface;
use App\Models\ExamAttempt;
use App\Policies\ExamAttemptPolicy;
use App\Repositories\DashboardRepository;
use App\Repositories\LessonRepository;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
        $this->app->bind(
            DashboardServiceInterface::class,
            DashboardService::class
        );
        $this->app->bind(
            LessonRepositoryInterface::class,
            LessonRepository::class
        );
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register policies
        Gate::policy(ExamAttempt::class, ExamAttemptPolicy::class);
    }
}
