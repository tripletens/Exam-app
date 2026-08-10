<?php

namespace App\Console\Commands;

use App\Services\ExamService;
use Illuminate\Console\Command;

class AutoSubmitExpiredExams extends Command
{
    protected $signature = 'exams:auto-submit';
    protected $description = 'Auto-submit all exam attempts where time has expired';

    public function __construct(private ExamService $examService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $count = $this->examService->autoSubmitExpired();
        $this->info("Auto-submitted {$count} expired exam attempt(s).");
        return self::SUCCESS;
    }
}
