<?php

namespace App\Http\Controllers\Api;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Exam;
use App\Services\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamAttemptController extends BaseApiController
{
    public function __construct(private ExamService $examService) {}

    /**
     * Start a new exam attempt.
     * Records start_time server-side. Returns shuffled questions WITHOUT is_correct.
     */
    public function start(Exam $exam): JsonResponse
    {
        $user = auth()->user();

        // Validation: exam must be available
        if (!$exam->isAvailableNow()) {
            return $this->error('This exam is not currently available.', 403);
        }

        // Check attempt limit
        $attemptCount = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->count();

        if ($attemptCount >= $exam->max_attempts) {
            return $this->error("You have reached the maximum number of attempts ({$exam->max_attempts}).", 403);
        }

        // Check for active (unsubmitted, unexpired) attempt
        $activeAttempt = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->whereNull('submitted_at')
            ->first();

        if ($activeAttempt && !$activeAttempt->isExpired()) {
            // Resume existing attempt
            return $this->buildAttemptResponse($activeAttempt, $exam);
        }

        // Create new attempt
        $attempt = DB::transaction(function () use ($user, $exam, $attemptCount) {
            return ExamAttempt::create([
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'started_at' => now(),
                'attempt_number' => $attemptCount + 1,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return $this->buildAttemptResponse($attempt, $exam);
    }

    private function buildAttemptResponse(ExamAttempt $attempt, Exam $exam): JsonResponse
    {
        $exam->load('questions.options');

        $questions = $exam->randomize_questions
            ? $exam->questions->shuffle()
            : $exam->questions;

        $questionsData = $questions->map(function ($q) use ($exam) {
            $options = $exam->randomize_answers ? $q->options->shuffle() : $q->options;
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'type' => $q->type,
                'marks' => $q->marks,
                'options' => $options->map(fn($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                    // is_correct is intentionally OMITTED
                ]),
            ];
        });

        $savedAnswers = $attempt->answers()->pluck('selected_option_id', 'question_id');

        return $this->success([
            'attempt_id' => $attempt->id,
            'exam_id' => $exam->id,
            'exam_title' => $exam->title,
            'duration_minutes' => $exam->duration_minutes,
            'time_remaining_seconds' => $attempt->time_remaining_in_seconds,
            'started_at' => $attempt->started_at,
            'questions' => $questionsData,
            'saved_answers' => $savedAnswers,
        ]);
    }

    /**
     * Server-authoritative time remaining.
     */
    public function timeRemaining(ExamAttempt $attempt): JsonResponse
    {
        $this->authorize('view', $attempt);
        if ($attempt->isSubmitted()) {
            return $this->success(['seconds_remaining' => 0, 'submitted' => true]);
        }
        return $this->success([
            'seconds_remaining' => $attempt->time_remaining_in_seconds,
            'submitted' => false,
        ]);
    }

    /**
     * Save a single answer (auto-save during exam).
     */
    public function saveAnswer(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $this->authorize('update', $attempt);

        if ($attempt->isSubmitted()) {
            return $this->error('Exam already submitted.', 403);
        }

        if ($attempt->isExpired()) {
            return $this->error('Exam time has expired.', 403);
        }

        $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'selected_option_id' => ['nullable', 'exists:question_options,id'],
            'answer_text' => ['nullable', 'string'],
        ]);

        ExamAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $request->question_id],
            [
                'selected_option_id' => $request->selected_option_id,
                'answer_text' => $request->answer_text,
            ]
        );

        return $this->success(null, 'Answer saved');
    }

    /**
     * Submit the exam — server validates timing before grading.
     */
    public function submit(ExamAttempt $attempt): JsonResponse
    {
        $this->authorize('update', $attempt);

        if ($attempt->isSubmitted()) {
            return $this->error('Exam already submitted.', 403);
        }

        // Server-side time validation — the ONLY authoritative check
        if ($attempt->isExpired()) {
            // Still grade it, but mark as auto-submitted
            $attempt->update(['auto_submitted' => true]);
        }

        $result = $this->examService->gradeAttempt($attempt);

        return $this->success([
            'attempt_id' => $result->id,
            'score' => $result->score,
            'total_marks' => $result->total_marks,
            'percentage' => $result->percentage,
            'passed' => $result->passed,
            'time_taken_minutes' => $result->started_at->diffInMinutes($result->submitted_at),
        ], 'Exam submitted successfully');
    }

    /**
     * Get result for a submitted attempt.
     */
    public function show(ExamAttempt $attempt): JsonResponse
    {
        $this->authorize('view', $attempt);

        if (!$attempt->isSubmitted()) {
            return $this->error('Exam not yet submitted.', 404);
        }

        $attempt->load('exam', 'answers.question.options', 'answers.selectedOption');

        $showAnswers = $attempt->exam->show_results_immediately || auth()->user()->isSuperAdmin();

        $answersData = $attempt->answers->map(function ($a) use ($showAnswers) {
            $data = [
                'question' => $a->question->question_text,
                'type' => $a->question->type,
                'marks_available' => $a->question->marks,
                'marks_awarded' => $a->marks_awarded,
                'is_correct' => $a->is_correct,
            ];

            if ($showAnswers) {
                $data['selected_option'] = $a->selectedOption?->option_text;
                $data['correct_options'] = $a->question->options->where('is_correct', true)->pluck('option_text');
                $data['explanation'] = $a->question->explanation;
            }

            return $data;
        });

        return $this->success([
            'attempt_id' => $attempt->id,
            'exam_title' => $attempt->exam->title,
            'score' => $attempt->score,
            'total_marks' => $attempt->total_marks,
            'percentage' => $attempt->percentage,
            'passed' => $attempt->passed,
            'time_taken_minutes' => $attempt->started_at->diffInMinutes($attempt->submitted_at),
            'submitted_at' => $attempt->submitted_at,
            'answers' => $answersData,
        ]);
    }
}
