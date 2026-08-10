<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'user_id', 'exam_id', 'started_at', 'submitted_at',
        'score', 'total_marks', 'percentage', 'passed',
        'attempt_number', 'auto_submitted', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
            'total_marks' => 'decimal:2',
            'percentage' => 'decimal:2',
            'passed' => 'boolean',
            'auto_submitted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'attempt_id');
    }

    // --- Helpers ---

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isExpired(): bool
    {
        if ($this->isSubmitted()) {
            return false;
        }
        return $this->time_remaining_in_seconds <= 0;
    }

    public function getTimeRemainingInSecondsAttribute(): int
    {
        if ($this->isSubmitted() || !$this->started_at) {
            return 0;
        }
        $duration = $this->exam ? (int) $this->exam->duration_minutes : 120;
        $endTime = $this->started_at->copy()->addMinutes($duration)->timestamp;
        $now = now()->timestamp;
        $remaining = $endTime - $now;
        return max(0, $remaining);
    }
}
