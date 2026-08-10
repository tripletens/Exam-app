<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class ExamService
{
    /**
     * Grade a submitted attempt and persist results.
     */
    public function gradeAttempt(ExamAttempt $attempt): ExamAttempt
    {
        $exam = $attempt->exam()->with('questions.options')->first();
        $answers = $attempt->answers()->with('question.options')->get();

        $totalMarks = $exam->questions->sum('marks');
        $marksObtained = 0;

        foreach ($answers as $answer) {
            $question = $answer->question;
            $marks = $this->gradeAnswer($question, $answer);
            $answer->update([
                'is_correct' => $marks > 0,
                'marks_awarded' => $marks,
            ]);
            $marksObtained += $marks;
        }

        $percentage = $totalMarks > 0 ? round(($marksObtained / $totalMarks) * 100, 2) : 0;
        $passed = $percentage >= $exam->pass_percentage;

        $attempt->update([
            'score' => $marksObtained,
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);

        return $attempt->fresh();
    }

    /**
     * Grade a single answer — returns marks awarded.
     */
    private function gradeAnswer(Question $question, ExamAnswer $answer): float
    {
        return match ($question->type) {
            QuestionType::MultipleChoice, QuestionType::TrueFalse => $this->gradeMultipleChoice($question, $answer),
            QuestionType::MultipleAnswer => $this->gradeMultipleAnswer($question, $answer),
            QuestionType::ShortAnswer => 0, // Manual grading required
        };
    }

    private function gradeMultipleChoice(Question $question, ExamAnswer $answer): float
    {
        if (!$answer->selected_option_id) return 0;
        $correctOption = $question->options->firstWhere('is_correct', true);
        return ($correctOption && $correctOption->id === $answer->selected_option_id)
            ? $question->marks
            : 0;
    }

    private function gradeMultipleAnswer(Question $question, ExamAnswer $answer): float
    {
        // Partial credit not implemented — full marks only if ALL correct options selected
        if (!$answer->selected_option_id) return 0;
        $correctIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values();
        $selectedIds = collect(json_decode($answer->answer_text ?? '[]'))->sort()->values();
        return $correctIds->toArray() === $selectedIds->toArray() ? $question->marks : 0;
    }

    /**
     * Auto-submit all expired active attempts.
     */
    public function autoSubmitExpired(): int
    {
        $expired = ExamAttempt::query()
            ->whereNull('submitted_at')
            ->with('exam')
            ->get()
            ->filter(fn($a) => $a->isExpired());

        foreach ($expired as $attempt) {
            DB::transaction(function () use ($attempt) {
                $attempt->update(['auto_submitted' => true]);
                $this->gradeAttempt($attempt);
            });
        }

        return $expired->count();
    }
}
