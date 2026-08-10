<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExamAttemptTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $intern;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'intern', 'guard_name' => 'web']);

        $this->admin = User::factory()->create(['role' => 'super_admin']);
        $this->admin->assignRole('super_admin');

        $this->intern = User::factory()->create(['role' => 'intern']);
        $this->intern->assignRole('intern');

        $course = Course::create([
            'created_by' => $this->admin->id,
            'title' => 'Security 101',
            'slug' => 'security-101',
            'status' => 'published',
        ]);

        $this->exam = Exam::create([
            'course_id' => $course->id,
            'created_by' => $this->admin->id,
            'title' => 'Security Quiz',
            'duration_minutes' => 30,
            'pass_percentage' => 70,
            'max_attempts' => 1,
            'status' => 'published',
        ]);

        ExamAssignment::create([
            'exam_id' => $this->exam->id,
            'user_id' => $this->intern->id,
            'assigned_by' => $this->admin->id,
        ]);

        $q = Question::create([
            'exam_id' => $this->exam->id,
            'question_text' => 'What is CIA?',
            'type' => 'mcq',
            'marks' => 10,
        ]);

        QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Confidentiality, Integrity, Availability', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q->id, 'option_text' => 'Control, Identity, Access', 'is_correct' => false]);
    }

    public function test_intern_can_start_assigned_exam(): void
    {
        $response = $this->actingAs($this->intern)
            ->postJson("/api/exams/{$this->exam->id}/start");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['attempt_id', 'exam_id', 'questions', 'time_remaining_seconds'],
            ]);

        $questions = $response->json('data.questions');
        $this->assertNotEmpty($questions);
        
        // Ensure is_correct is NOT returned in question options during exam start
        $firstOption = $questions[0]['options'][0];
        $this->assertArrayNotHasKey('is_correct', $firstOption);
    }

    public function test_intern_can_save_answer_and_submit(): void
    {
        $startRes = $this->actingAs($this->intern)
            ->postJson("/api/exams/{$this->exam->id}/start");

        $attemptId = $startRes->json('data.attempt_id');
        $questionId = $startRes->json('data.questions.0.id');
        $correctOptionId = QuestionOption::where('question_id', $questionId)
            ->where('is_correct', true)
            ->first()->id;

        // Save answer
        $saveRes = $this->actingAs($this->intern)
            ->postJson("/api/exam-attempts/{$attemptId}/save-answer", [
                'question_id' => $questionId,
                'selected_option_id' => $correctOptionId,
            ]);
        $saveRes->assertStatus(200);

        // Submit
        $submitRes = $this->actingAs($this->intern)
            ->postJson("/api/exam-attempts/{$attemptId}/submit");

        $submitRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'score' => '10.00',
                    'percentage' => '100.00',
                    'passed' => true,
                ],
            ]);
    }
}
