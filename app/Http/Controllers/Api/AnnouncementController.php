<?php

namespace App\Http\Controllers\Api;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $announcements = Announcement::with('creator')
            ->where(fn($q) => $q->where('target_role', 'all')->orWhere('target_role', $user->role->value))
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(10);

        return $this->paginated($announcements);
    }

    public function store(Request $request): JsonResponse
    {
        $announcement = Announcement::create([
            ...$request->validate([
                'title' => ['required', 'string', 'max:255'],
                'body' => ['required', 'string'],
                'target_role' => ['required', 'in:all,intern,instructor'],
                'published_at' => ['nullable', 'date'],
            ]),
            'created_by' => auth()->id(),
        ]);
        return $this->success($announcement, 'Announcement created', 201);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $announcement->update($request->validate([
            'title' => ['sometimes', 'string'],
            'body' => ['sometimes', 'string'],
            'target_role' => ['sometimes', 'in:all,intern,instructor'],
            'published_at' => ['nullable', 'date'],
        ]));
        return $this->success($announcement, 'Announcement updated');
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();
        return $this->success(null, 'Announcement deleted');
    }
}
