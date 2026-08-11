<?php

namespace App\Repositories;

use App\Contracts\Repositories\LessonRepositoryInterface;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Collection;

class LessonRepository implements LessonRepositoryInterface
{
    public function getByModuleOrAll(?int $moduleId = null): Collection
    {
        return Lesson::query()
            ->when($moduleId, fn($q) => $q->where('module_id', $moduleId))
            ->with('module.course')
            ->orderBy('order')
            ->get();
    }

    public function findById(int $id): ?Lesson
    {
        return Lesson::with('resources', 'module.course')->find($id);
    }

    public function create(array $data): Lesson
    {
        return Lesson::create($data);
    }

    public function update(int $id, array $data): Lesson
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->update($data);
        return $lesson->fresh();
    }

    public function delete(int $id): bool
    {
        $lesson = Lesson::findOrFail($id);
        return (bool) $lesson->delete();
    }
}
