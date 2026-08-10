<?php

use App\Console\Commands\AutoSubmitExpiredExams;
use Illuminate\Support\Facades\Schedule;

// Auto-submit expired exams every minute
Schedule::command(AutoSubmitExpiredExams::class)->everyMinute()->withoutOverlapping();
