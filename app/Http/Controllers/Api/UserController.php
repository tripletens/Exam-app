<?php

namespace App\Http\Controllers\Api;

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
        $user = User::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:super_admin,instructor,intern'],
            'phone' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]) + ['password' => Hash::make($request->password)]);

        // Assign Spatie role
        $user->assignRole($user->role->value);

        return $this->success($user, 'User created', 201);
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
        $user->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'role' => ['sometimes', 'in:super_admin,instructor,intern'],
            'phone' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]));

        if ($request->has('role')) {
            $user->syncRoles([$user->role->value]);
        }

        return $this->success($user, 'User updated');
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return $this->error('Cannot delete your own account.', 403);
        }
        $user->delete();
        return $this->success(null, 'User deleted');
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $request->validate(['password' => ['required', 'string', 'min:8']]);
        $user->update(['password' => Hash::make($request->password)]);
        return $this->success(null, 'Password reset successfully');
    }
}
