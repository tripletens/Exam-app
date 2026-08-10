<?php

namespace App\Http\Controllers\Api;

use App\Models\LearningResource;
use App\Models\ResourceProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $resources = LearningResource::query()
            ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
            ->when($request->module_id, fn($q) => $q->where('module_id', $request->module_id))
            ->when($request->lesson_id, fn($q) => $q->where('lesson_id', $request->lesson_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->get();
        return $this->success($resources);
    }

    public function store(Request $request): JsonResponse
    {
        $resource = LearningResource::create($request->validate([
            'course_id' => ['nullable', 'exists:courses,id'],
            'module_id' => ['nullable', 'exists:course_modules,id'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'type' => ['required', 'in:youtube,book,article,documentation,pdf,external_website,practical_lab,assignment'],
            'author' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer'],
            'pages' => ['nullable', 'integer'],
            'difficulty' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'string'],
            'is_required' => ['boolean'],
        ]));
        return $this->success($resource, 'Resource created', 201);
    }

    public function show(LearningResource $resource): JsonResponse
    {
        return $this->success($resource);
    }

    public function update(Request $request, LearningResource $resource): JsonResponse
    {
        $resource->update($request->validate([
            'title' => ['sometimes', 'string'],
            'description' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'type' => ['sometimes', 'in:youtube,book,article,documentation,pdf,external_website,practical_lab,assignment'],
            'author' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer'],
            'pages' => ['nullable', 'integer'],
            'difficulty' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'string'],
            'is_required' => ['boolean'],
        ]));
        return $this->success($resource, 'Resource updated');
    }

    public function destroy(LearningResource $resource): JsonResponse
    {
        $resource->delete();
        return $this->success(null, 'Resource deleted');
    }

    public function markComplete(LearningResource $resource): JsonResponse
    {
        ResourceProgress::updateOrCreate(
            ['user_id' => auth()->id(), 'resource_id' => $resource->id],
            ['completed_at' => now()]
        );
        return $this->success(null, 'Resource marked as completed');
    }
}
