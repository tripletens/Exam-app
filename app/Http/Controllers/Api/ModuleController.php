<?php

namespace App\Http\Controllers\Api;

use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $modules = CourseModule::with('lessons', 'resources')
            ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
            ->orderBy('order')
            ->get();
        return $this->success($modules);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ]);
        $module = CourseModule::create($validated);
        return $this->success($module, 'Module created', 201);
    }

    public function show(CourseModule $module): JsonResponse
    {
        return $this->success($module->load('lessons.resources', 'resources'));
    }

    public function update(Request $request, CourseModule $module): JsonResponse
    {
        $module->update($request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ]));
        return $this->success($module, 'Module updated');
    }

    public function destroy(CourseModule $module): JsonResponse
    {
        $module->delete();
        return $this->success(null, 'Module deleted');
    }
}

