<?php

namespace App\Providers;

use App\Models\ExamAttempt;
use App\Policies\ExamAttemptPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Register policies
        Gate::policy(ExamAttempt::class, ExamAttemptPolicy::class);
    }
}
