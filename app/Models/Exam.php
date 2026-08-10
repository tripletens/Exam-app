<?php

namespace App\Models;

use App\Enums\ExamStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id', 'module_id', 'created_by', 'title', 'description',
        'duration_minutes', 'pass_percentage', 'max_attempts',
        'starts_at', 'ends_at', 'randomize_questions', 'randomize_answers',
        'show_results_immediately', 'allow_retakes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'randomize_questions' => 'boolean',
            'randomize_answers' => 'boolean',
            'show_results_immediately' => 'boolean',
            'allow_retakes' => 'boolean',
            'pass_percentage' => 'decimal:2',
            'status' => ExamStatus::class,
        ];
    }

    // --- Relationships ---

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ExamAssignment::class);
    }

    // --- Helpers ---

    public function isPublished(): bool
    {
        if ($this->status instanceof ExamStatus) {
            return $this->status === ExamStatus::Published;
        }
        return $this->status === 'published' || $this->status?->value === 'published';
    }

    public function isAvailableNow(): bool
    {
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;
        return $this->isPublished();
    }

    public function getTotalMarksAttribute(): int
    {
        return $this->questions->sum('marks');
    }
}
