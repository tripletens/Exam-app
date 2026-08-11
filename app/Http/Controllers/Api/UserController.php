<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseApiController
{
    public function __construct(private ProgressService $progressService) {}

    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->active !== null, fn($q) => $q->where('is_active', $request->boolean('active')))
            ->latest();

        return $this->paginated($query->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:super_admin,instructor,intern'],
            'phone' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        $roleName = is_object($user->role) ? $user->role->value : $user->role;
        $user->assignRole($roleName);

        // Auto-enroll new intern accounts in all published courses & exams
        if ($roleName === 'intern') {
            $courses = Course::where('status', 'published')->get();
            foreach ($courses as $course) {
                CourseEnrollment::firstOrCreate(
                    ['user_id' => $user->id, 'course_id' => $course->id],
                    ['enrolled_by' => auth()->id() ?? $user->id, 'enrolled_at' => now()]
                );
            }

            $exams = Exam::where('status', 'published')->get();
            foreach ($exams as $exam) {
                ExamAssignment::firstOrCreate(
                    ['exam_id' => $exam->id, 'user_id' => $user->id],
                    ['assigned_by' => auth()->id() ?? $user->id, 'assigned_at' => now()]
                );
            }
        }

        return $this->success($user, 'User created and assigned to courses', 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('enrollments.course', 'certificates.course', 'examAttempts.exam');

        $progress = $this->progressService->getOverallProgress($user);
        $examStats = $this->progressService->getExamStats($user);

        return $this->success([
            'user' => $user,
            'progress' => $progress,
            'exam_stats' => $examStats,
            'recent_attempts' => $user->examAttempts()
                ->with('exam')
                ->whereNotNull('submitted_at')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', "unique:users,email,{$user->id}"],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['sometimes', 'in:super_admin,instructor,intern'],
            'phone' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['role'])) {
            $roleName = is_object($user->role) ? $user->role->value : $user->role;
            $user->syncRoles([$roleName]);
        }

        return $this->success($user, 'User updated');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return $this->success(null, 'User deleted');
    }
}
