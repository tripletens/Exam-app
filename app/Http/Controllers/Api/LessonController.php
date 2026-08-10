<?php

namespace App\Http\Controllers\Api;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends BaseApiController
{
    public function __construct(private ProgressService $progressService) {}

    public function store(Request $request): JsonResponse
    {
        $lesson = Lesson::create($request->validate([
            'module_id' => ['required', 'exists:course_modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'duration_minutes' => ['nullable', 'integer'],
        ]));
        return $this->success($lesson, 'Lesson created', 201);
    }

    public function show(Lesson $lesson): JsonResponse
    {
        return $this->success($lesson->load('resources'));
    }

    public function update(Request $request, Lesson $lesson): JsonResponse
    {
        $lesson->update($request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'duration_minutes' => ['nullable', 'integer'],
        ]));
        return $this->success($lesson, 'Lesson updated');
    }

    public function destroy(Lesson $lesson): JsonResponse
    {
        $lesson->delete();
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
