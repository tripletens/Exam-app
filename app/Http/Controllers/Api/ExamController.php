<?php

namespace App\Http\Controllers\Api;

use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\ExamAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Exam::with('course', 'module', 'creator')
            ->withCount('questions', 'attempts')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
            ->latest();

        if (auth()->user()->isIntern()) {
            $query->where('status', 'published');
        }

        return $this->paginated($query->paginate(15));
    }

    public function show(Exam $exam): JsonResponse
    {
        $exam->load('course', 'module', 'questions.options');

        // Auto-assign intern on view if not assigned
        if (auth()->user()->isIntern()) {
            ExamAssignment::firstOrCreate(
                ['exam_id' => $exam->id, 'user_id' => auth()->id()],
                ['assigned_by' => auth()->id(), 'assigned_at' => now()]
            );

            // Strip is_correct from options for interns
            $exam->questions->each(function ($q) {
                $q->options->each(fn($o) => $o->makeHidden('is_correct'));
            });
        }
        return $this->success($exam);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'module_id' => ['nullable', 'exists:course_modules,id'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'pass_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'max_attempts' => ['required', 'integer', 'min:1'],
            'randomize_questions' => ['boolean'],
            'randomize_answers' => ['boolean'],
            'show_results_immediately' => ['boolean'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        $exam = Exam::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return $this->success($exam, 'Exam created successfully', 201);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'module_id' => ['nullable', 'exists:course_modules,id'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1'],
            'pass_percentage' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'max_attempts' => ['sometimes', 'integer', 'min:1'],
            'randomize_questions' => ['boolean'],
            'randomize_answers' => ['boolean'],
            'show_results_immediately' => ['boolean'],
            'status' => ['sometimes', 'in:draft,published,archived'],
        ]);

        $exam->update($validated);
        return $this->success($exam, 'Exam updated successfully');
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $exam->delete();
        return $this->success(null, 'Exam deleted successfully');
    }

    public function assign(Request $request, Exam $exam): JsonResponse
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        foreach ($request->user_ids as $userId) {
            ExamAssignment::firstOrCreate(
                ['exam_id' => $exam->id, 'user_id' => $userId],
                ['assigned_by' => auth()->id(), 'assigned_at' => now()]
            );
        }

        return $this->success(null, 'Exam assigned to interns successfully');
    }
}
