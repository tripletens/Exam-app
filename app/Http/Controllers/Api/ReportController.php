<?php

namespace App\Http\Controllers\Api;

use App\Models\ExamAttempt;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Csv\Writer;

class ReportController extends BaseApiController
{
    public function internPerformance(Request $request): JsonResponse
    {
        $interns = User::where('role', 'intern')
            ->withCount(['examAttempts as total_exams' => fn($q) => $q->whereNotNull('submitted_at')])
            ->withAvg(['examAttempts as avg_score' => fn($q) => $q->whereNotNull('submitted_at')], 'percentage')
            ->withCount(['examAttempts as passed_exams' => fn($q) => $q->whereNotNull('submitted_at')->where('passed', true)])
            ->orderByDesc('avg_score')
            ->paginate(20);

        return $this->paginated($interns);
    }

    public function courseCompletion(): JsonResponse
    {
        $courses = Course::withCount('enrollments')
            ->withCount(['enrollments as completions' => fn($q) => $q->whereNotNull('completed_at')])
            ->get()
            ->map(fn($c) => [
                'course' => $c->title,
                'enrolled' => $c->enrollments_count,
                'completed' => $c->completions,
                'rate' => $c->enrollments_count > 0
                    ? round(($c->completions / $c->enrollments_count) * 100, 1)
                    : 0,
            ]);

        return $this->success($courses);
    }

    public function examPerformance(): JsonResponse
    {
        $data = ExamAttempt::whereNotNull('submitted_at')
            ->with('exam:id,title')
            ->selectRaw('exam_id, COUNT(*) as attempts, AVG(percentage) as avg_score, SUM(passed) as passes')
            ->groupBy('exam_id')
            ->get()
            ->map(fn($r) => [
                'exam' => $r->exam?->title,
                'attempts' => $r->attempts,
                'avg_score' => round($r->avg_score, 1),
                'pass_rate' => $r->attempts > 0 ? round(($r->passes / $r->attempts) * 100, 1) : 0,
            ]);

        return $this->success($data);
    }

    public function exportCsv(Request $request)
    {
        $request->validate(['type' => ['required', 'in:interns,exams,courses']]);

        $csv = Writer::createFromString();

        match ($request->type) {
            'interns' => $this->exportInterns($csv),
            'exams' => $this->exportExams($csv),
            'courses' => $this->exportCourses($csv),
        };

        return response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"lythub-{$request->type}-report.csv\"",
        ]);
    }

    private function exportInterns(Writer $csv): void
    {
        $csv->insertOne(['Name', 'Email', 'Department', 'Total Exams', 'Avg Score', 'Passed']);
        User::where('role', 'intern')
            ->withCount(['examAttempts as total_exams' => fn($q) => $q->whereNotNull('submitted_at')])
            ->withAvg(['examAttempts as avg_score' => fn($q) => $q->whereNotNull('submitted_at')], 'percentage')
            ->withCount(['examAttempts as passed_exams' => fn($q) => $q->where('passed', true)])
            ->get()
            ->each(fn($u) => $csv->insertOne([
                $u->name, $u->email, $u->department ?? '-',
                $u->total_exams, round($u->avg_score ?? 0, 1), $u->passed_exams,
            ]));
    }

    private function exportExams(Writer $csv): void
    {
        $csv->insertOne(['Exam', 'Course', 'Total Attempts', 'Avg Score', 'Pass Rate']);
        ExamAttempt::whereNotNull('submitted_at')
            ->with('exam.course')
            ->selectRaw('exam_id, COUNT(*) as attempts, AVG(percentage) as avg_score, SUM(passed) as passes')
            ->groupBy('exam_id')
            ->get()
            ->each(fn($r) => $csv->insertOne([
                $r->exam?->title, $r->exam?->course?->title, $r->attempts,
                round($r->avg_score, 1),
                $r->attempts > 0 ? round(($r->passes / $r->attempts) * 100, 1) . '%' : '0%',
            ]));
    }

    private function exportCourses(Writer $csv): void
    {
        $csv->insertOne(['Course', 'Category', 'Enrolled', 'Completed', 'Completion Rate']);
        Course::withCount('enrollments')
            ->withCount(['enrollments as completions' => fn($q) => $q->whereNotNull('completed_at')])
            ->get()
            ->each(fn($c) => $csv->insertOne([
                $c->title, $c->category ?? '-', $c->enrollments_count, $c->completions,
                $c->enrollments_count > 0
                    ? round(($c->completions / $c->enrollments_count) * 100, 1) . '%'
                    : '0%',
            ]));
    }
}
