<?php

namespace App\Http\Controllers\Api;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $questions = Question::with('options')
            ->when($request->exam_id, fn($q) => $q->where('exam_id', $request->exam_id))
            ->orderBy('order')
            ->get();
        return $this->success($questions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'question_text' => ['required', 'string'],
            'type' => ['required', 'in:mcq,multi_answer,true_false,short_answer'],
            'marks' => ['required', 'integer', 'min:1'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'explanation' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'options' => ['required_unless:type,short_answer', 'array', 'min:2'],
            'options.*.option_text' => ['required', 'string'],
            'options.*.is_correct' => ['required', 'boolean'],
        ]);

        $question = Question::create([
            'exam_id' => $validated['exam_id'],
            'question_text' => $validated['question_text'],
            'type' => $validated['type'],
            'marks' => $validated['marks'],
            'difficulty' => $validated['difficulty'],
            'explanation' => $validated['explanation'] ?? null,
            'order' => $validated['order'] ?? 0,
        ]);

        if (!empty($validated['options'])) {
            foreach ($validated['options'] as $i => $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'],
                    'order' => $i,
                ]);
            }
        }

        return $this->success($question->load('options'), 'Question created', 201);
    }

    public function update(Request $request, Question $question): JsonResponse
    {
        $question->update($request->validate([
            'question_text' => ['sometimes', 'string'],
            'type' => ['sometimes', 'in:mcq,multi_answer,true_false,short_answer'],
            'marks' => ['sometimes', 'integer', 'min:1'],
            'difficulty' => ['sometimes', 'in:easy,medium,hard'],
            'explanation' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ]));

        if ($request->has('options')) {
            $question->options()->delete();
            foreach ($request->options as $i => $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $option['option_text'],
                    'is_correct' => $option['is_correct'],
                    'order' => $i,
                ]);
            }
        }

        return $this->success($question->fresh('options'), 'Question updated');
    }

    public function destroy(Question $question): JsonResponse
    {
        $question->delete();
        return $this->success(null, 'Question deleted');
    }
}
