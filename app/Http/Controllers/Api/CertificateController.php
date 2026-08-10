<?php

namespace App\Http\Controllers\Api;

use App\Models\Certificate;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends BaseApiController
{
    public function __construct(private CertificateService $certificateService) {}

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $query = Certificate::with('user', 'course', 'learningPath', 'issuedBy');

        if ($user->isIntern()) {
            $query->where('user_id', $user->id);
        } elseif ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        return $this->success($query->latest('issued_at')->get());
    }

    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'learning_path_id' => ['nullable', 'exists:learning_paths,id'],
        ]);

        $certificate = Certificate::create([
            ...$validated,
            'issued_by' => auth()->id(),
            'issued_at' => now(),
        ]);

        return $this->success($certificate->load('user', 'course'), 'Certificate issued', 201);
    }

    public function download(Certificate $certificate)
    {
        // Interns can only download their own
        if (auth()->user()->isIntern() && $certificate->user_id !== auth()->id()) {
            abort(403);
        }

        return $this->certificateService->download($certificate);
    }

    public function destroy(Certificate $certificate): JsonResponse
    {
        $certificate->delete();
        return $this->success(null, 'Certificate revoked');
    }
}
