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
            $user = auth()->user();
            $assignedIds = ExamAssignment::where('user_id', $user->id)->pluck('exam_id');
            $enrolledCourseIds = CourseEnrollment::where('user_id', $user->id)->pluck('course_id');

            $query->where(function ($q) use ($assignedIds, $enrolledCourseIds) {
                $q->whereIn('id', $assignedIds)
                  ->orWhereIn('course_id', $enrolledCourseIds)
                  ->orWhereNull('course_id'); // Also include global published standalone exams
            })->where('status', 'published');
        }

        return $this->paginated($query->paginate(15));
    }

    public function show(Exam $exam): JsonResponse
    {
        $exam->load('course', 'module', 'questions.options');
        // Strip is_correct from options for non-admins
        if (auth()->user()->isIntern()) {
            $exam->questions->each(function ($q) {
                $q->options->each(fn($o) => $o->makeHidden('is_correct'));
            });
        }
        return $this->success($exam);
    }

    public function store(Request $request): JsonResponse
    {
        $exam = Exam::create([
            ...$request->validate([
                'course_id' => ['nullable', 'exists:courses,id'],
                'module_id' => ['nullable', 'exists:course_modules,id'],
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'duration_minutes' => ['required', 'integer', 'min:1'],
                'pass_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
                'max_attempts' => ['required', 'integer', 'min:1'],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date', 'after:starts_at'],
                'randomize_questions' => ['boolean'],
                'randomize_answers' => ['boolean'],
                'show_results_immediately' => ['boolean'],
                'allow_retakes' => ['boolean'],
                'status' => ['required', 'in:draft,published,archived'],
            ]),
            'created_by' => auth()->id(),
        ]);
        return $this->success($exam, 'Exam created', 201);
    }

    public function update(Request $request, Exam $exam): JsonResponse
    {
        $exam->update($request->validate([
            'title' => ['sometimes', 'string'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1'],
            'pass_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'max_attempts' => ['sometimes', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'randomize_questions' => ['boolean'],
            'randomize_answers' => ['boolean'],
            'show_results_immediately' => ['boolean'],
            'allow_retakes' => ['boolean'],
            'status' => ['sometimes', 'in:draft,published,archived'],
        ]));
        return $this->success($exam, 'Exam updated');
    }

    public function destroy(Exam $exam): JsonResponse
    {
        $exam->delete();
        return $this->success(null, 'Exam deleted');
    }

    public function publish(Exam $exam): JsonResponse
    {
        $exam->update(['status' => 'published']);
        return $this->success(null, 'Exam published');
    }

    public function unpublish(Exam $exam): JsonResponse
    {
        $exam->update(['status' => 'draft']);
        return $this->success(null, 'Exam unpublished');
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

        return $this->success(null, 'Exam assigned to ' . count($request->user_ids) . ' intern(s)');
    }
}
