<?php

namespace App\Providers;

use App\Models\ExamAttempt;
use App\Policies\ExamAttemptPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Register policies
        Gate::policy(ExamAttempt::class, ExamAttemptPolicy::class);
    }
}
