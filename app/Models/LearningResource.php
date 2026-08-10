<?php

namespace App\Models;

use App\Enums\ResourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningResource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id', 'module_id', 'lesson_id', 'title', 'description',
        'url', 'type', 'author', 'duration_minutes', 'pages',
        'difficulty', 'thumbnail', 'is_required',
    ];

    protected function casts(): array
    {
        return [
            'type' => ResourceType::class,
            'is_required' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ResourceProgress::class, 'resource_id');
    }

    public function isCompletedBy(int $userId): bool
    {
        return $this->progress()->where('user_id', $userId)->whereNotNull('completed_at')->exists();
    }
}
