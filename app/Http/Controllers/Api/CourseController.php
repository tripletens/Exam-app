<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Course::with('creator', 'modules')
            ->withCount('enrollments')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->latest();

        // Interns only see enrolled courses that are published
        if (auth()->user()->isIntern()) {
            $enrolledIds = auth()->user()->enrolledCourses()->pluck('courses.id');
            $query->whereIn('id', $enrolledIds)->where('status', 'published');
        }

        return $this->paginated($query->paginate(12));
    }

    public function show(Course $course): JsonResponse
    {
        $course->load('creator', 'modules.lessons', 'modules.resources');
        return $this->success($course);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'estimated_duration' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,archived'],
            'thumbnail' => ['nullable', 'string'],
        ]);

        $course = Course::create([
            ...$validated,
            'created_by' => auth()->id(),
            'slug' => Str::slug($validated['title']),
        ]);

        return $this->success($course, 'Course created successfully', 201);
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'difficulty' => ['sometimes', 'in:beginner,intermediate,advanced'],
            'estimated_duration' => ['nullable', 'integer'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'thumbnail' => ['nullable', 'string'],
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $course->update($validated);
        return $this->success($course, 'Course updated successfully');
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();
        return $this->success(null, 'Course deleted successfully');
    }

    public function enroll(Request $request, Course $course): JsonResponse
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $enrolled = [];
        foreach ($request->user_ids as $userId) {
            CourseEnrollment::firstOrCreate(
                ['user_id' => $userId, 'course_id' => $course->id],
                ['enrolled_by' => auth()->id(), 'enrolled_at' => now()]
            );
            $enrolled[] = $userId;
        }

        return $this->success(['enrolled_count' => count($enrolled)], 'Interns enrolled successfully');
    }

    public function myProgress(Course $course): JsonResponse
    {
        $user = auth()->user();
        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return $this->error('You are not enrolled in this course', 404);
        }

        $course->load('modules.lessons.progress');
        $totalLessons = 0;
        $completedLessons = 0;

        foreach ($course->modules as $module) {
            foreach ($module->lessons as $lesson) {
                $totalLessons++;
                if ($lesson->isCompletedBy($user->id)) $completedLessons++;
            }
        }

        return $this->success([
            'enrolled_at' => $enrollment->enrolled_at,
            'completed_at' => $enrollment->completed_at,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0,
        ]);
    }
}
