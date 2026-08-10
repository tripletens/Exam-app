<?php

namespace App\Models;

use App\Enums\CourseDifficulty;
use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by', 'title', 'slug', 'description', 'category',
        'difficulty', 'thumbnail', 'estimated_duration', 'status',
    ];

    protected function casts(): array
    {
        return [
            'difficulty' => CourseDifficulty::class,
            'status' => CourseStatus::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    // --- Relationships ---

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withPivot('enrolled_at', 'completed_at')
            ->withTimestamps();
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LearningResource::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    // --- Helpers ---

    public function isPublished(): bool
    {
        return $this->status === CourseStatus::Published;
    }

    public function getTotalLessonsAttribute(): int
    {
        return $this->modules->sum(fn($m) => $m->lessons->count());
    }
}
