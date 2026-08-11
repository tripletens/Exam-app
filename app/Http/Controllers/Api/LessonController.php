<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\LessonRepositoryInterface;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends BaseApiController
{
    public function __construct(
        private LessonRepositoryInterface $lessonRepository,
        private ProgressService $progressService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $moduleId = $request->module_id ? (int) $request->module_id : null;
        $lessons = $this->lessonRepository->getByModuleOrAll($moduleId);
        return $this->success($lessons);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module_id' => ['required', 'exists:course_modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'duration_minutes' => ['nullable', 'integer'],
        ]);

        $lesson = $this->lessonRepository->create($validated);
        return $this->success($lesson, 'Lesson created', 201);
    }

    public function show(Lesson $lesson): JsonResponse
    {
        $data = $this->lessonRepository->findById($lesson->id);
        return $this->success($data ?? $lesson);
    }

    public function update(Request $request, Lesson $lesson): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'duration_minutes' => ['nullable', 'integer'],
        ]);

        $updated = $this->lessonRepository->update($lesson->id, $validated);
        return $this->success($updated, 'Lesson updated');
    }

    public function destroy(Lesson $lesson): JsonResponse
    {
        $this->lessonRepository->delete($lesson->id);
        return $this->success(null, 'Lesson deleted');
    }

    public function markComplete(Lesson $lesson): JsonResponse
    {
        $user = auth()->user();
        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            ['completed_at' => now()]
        );

        // Check if whole course is done
        $course = $lesson->module->course;
        $this->progressService->checkAndMarkCourseComplete($user, $course);

        return $this->success(null, 'Lesson marked as completed');
    }
}
