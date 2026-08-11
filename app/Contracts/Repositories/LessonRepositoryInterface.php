<?php

namespace App\Contracts\Repositories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Collection;

interface LessonRepositoryInterface
{
    public function getByModuleOrAll(?int $moduleId = null): Collection;
    public function findById(int $id): ?Lesson;
    public function create(array $data): Lesson;
    public function update(int $id, array $data): Lesson;
    public function delete(int $id): bool;
}
