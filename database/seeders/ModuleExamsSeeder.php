<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\Lesson;
use App\Models\LearningResource;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModuleExamsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@lythub.com')->first();
        $interns = User::where('role', 'intern')->get();

        // ─── Track 3: Computer Science, Statistics & Mathematics Essentials ───
        $csCourse = Course::updateOrCreate(
            ['slug' => 'computer-science-statistics-maths'],
            [
                'created_by' => $admin->id,
                'title' => 'Computer Science, Statistics & Mathematics Essentials',
                'description' => 'A foundational engineering course covering computer science fundamentals, data structures, probability, applied statistics, discrete math, and linear algebra for developers.',
                'category' => 'Software Engineering',
                'difficulty' => 'beginner',
                'status' => 'published',
                'estimated_duration' => 1500,
            ]
        );

        $csModulesData = [
            [
                'title' => 'Computer Science Fundamentals & Algorithms',
                'desc' => 'Binary representation, Big-O complexity analysis, arrays, stacks, queues, hash tables, trees, and sorting algorithms.',
                'video_url' => 'https://www.youtube.com/watch?v=8hly31xKLI0',
                'lessons' => [
                    [
                        'title' => 'Binary Numbers, Memory Layout & Big-O Time Complexity',
                        'content' => <<<'MD'
# Computer Science Fundamentals & Algorithm Complexity

Computer science is built upon binary representation (bits and bytes) and algorithmic efficiency evaluation using **Big-O Notation**:

### Common Big-O Time Complexities
- $O(1)$: Constant time (e.g. hash map lookup, array indexing).
- $O(\log N)$: Logarithmic time (e.g. binary search in sorted array).
- $O(N)$: Linear time (e.g. unsorted array linear search).
- $O(N \log N)$: Linearithmic time (e.g. QuickSort, MergeSort).
- $O(N^2)$: Quadratic time (e.g. BubbleSort, nested loops).

---

### Basic Data Structures Summary
1. **Array**: Contiguous memory block, $O(1)$ index lookup, $O(N)$ insertion.
2. **Linked List**: Nodes with pointers, $O(N)$ search, $O(1)$ prepending.
3. **Stack**: LIFO (Last In, First Out) structure (`push`, `pop`).
4. **Queue**: FIFO (First In, First Out) structure (`enqueue`, `dequeue`).
5. **Hash Table**: Key-value mapping with $O(1)$ average time lookup.
MD,
                        'quiz' => [
                            [
                                'question' => 'What is the average time complexity of a Binary Search algorithm on a sorted array of N elements?',
                                'options' => ['O(1)', 'O(log N)', 'O(N)', 'O(N^2)'],
                                'correct' => 1,
                                'explanation' => 'Binary Search halves the search space at each step, yielding O(log N) time complexity.',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Applied Statistics & Probability for Developers',
                'desc' => 'Descriptive statistics (mean, median, mode, variance, standard deviation), probability distributions, and hypothesis testing.',
                'video_url' => 'https://www.youtube.com/watch?v=xxpc-HPKN28',
                'lessons' => [
                    [
                        'title' => 'Descriptive Statistics & Normal Distribution',
                        'content' => <<<'MD'
# Applied Statistics & Probability

Statistics provides the quantitative foundation for software analytics, machine learning, and performance benchmarking.

### Central Tendency Metrics
- **Mean**: Arithmetic average ($\mu = \frac{\sum x_i}{N}$).
- **Median**: Middle value when data is sorted (robust against outliers).
- **Mode**: Most frequently occurring value.

### Dispersion & Variance
- **Variance ($\sigma^2$)**: Average squared deviation from the mean.
- **Standard Deviation ($\sigma$)**: Square root of variance ($\sigma = \sqrt{\sigma^2}$).

---

### The Normal (Gaussian) Distribution & Empirical Rule (68-95-99.7)
- 68% of data falls within $\mu \pm 1\sigma$.
- 95% of data falls within $\mu \pm 2\sigma$.
- 99.7% of data falls within $\mu \pm 3\sigma$.
MD,
                        'quiz' => [
                            [
                                'question' => 'Which measure of central tendency is most robust against extreme outliers?',
                                'options' => ['Arithmetic Mean', 'Median', 'Variance', 'Range'],
                                'correct' => 1,
                                'explanation' => 'The median takes the middle position of sorted data, preventing extreme outliers from skewing the value.',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Applied Mathematics, Logic & Linear Algebra',
                'desc' => 'Boolean logic algebra, set theory, matrices, vectors, dot products, and calculus rate-of-change basics.',
                'video_url' => 'https://www.youtube.com/watch?v=fNk_zzaMoSs',
                'lessons' => [
                    [
                        'title' => 'Boolean Logic Gates & Matrix Operations',
                        'content' => <<<'MD'
# Applied Mathematics & Discrete Logic

Software logic relies on Boolean algebra and linear matrix operations.

### Truth Tables & Logic Gates
- **AND ($\land$)**: True only if both inputs are True.
- **OR ($\lor$)**: True if at least one input is True.
- **NOT ($\neg$)**: Inverts Boolean state.
- **XOR ($\oplus$)**: True if inputs differ (one True, one False).

---

### Vector Dot Product Formula
For vectors $\vec{A} = [a_1, a_2]$ and $\vec{B} = [b_1, b_2]$:
$$\vec{A} \cdot \vec{B} = a_1 b_1 + a_2 b_2$$
MD,
                        'quiz' => [
                            [
                                'question' => 'What is the output of `True XOR True` in Boolean logic?',
                                'options' => ['True', 'False', 'Undefined', 'Null'],
                                'correct' => 1,
                                'explanation' => 'XOR (Exclusive OR) returns True if and only if the inputs differ. Since both inputs are True, XOR returns False.',
                            ]
                        ]
                    ]
                ]
            ]
        ];

        foreach ($csModulesData as $i => $modData) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $csCourse->id, 'title' => $modData['title']],
                ['description' => $modData['desc'], 'order' => $i + 1]
            );

            foreach ($modData['lessons'] as $j => $lData) {
                Lesson::updateOrCreate(
                    ['module_id' => $module->id, 'title' => $lData['title']],
                    [
                        'content' => $lData['content'],
                        'order' => $j + 1,
                        'duration_minutes' => 30,
                        'quiz_data' => $lData['quiz'] ?? null,
                    ]
                );
            }
        }

        // Enroll all interns in CS Math course
        foreach ($interns as $internUser) {
            CourseEnrollment::firstOrCreate(
                ['user_id' => $internUser->id, 'course_id' => $csCourse->id],
                ['enrolled_by' => $admin->id, 'enrolled_at' => now()]
            );
        }

        // ─── Generate 2-Hour 50-Question 100-Mark Module Exams for ALL Modules ───
        $allModules = CourseModule::with('course')->get();

        foreach ($allModules as $module) {
            $examTitle = "{$module->title} — 2-Hour Module Certification Exam";

            $exam = Exam::updateOrCreate(
                ['title' => $examTitle],
                [
                    'course_id' => $module->course_id,
                    'module_id' => $module->id,
                    'created_by' => $admin->id,
                    'description' => "Official 2-Hour Module Certification Exam for {$module->title}. 50 Questions, 100 Marks Total.",
                    'duration_minutes' => 120, // 2 Hours
                    'pass_percentage' => 70, // 70% Pass mark
                    'max_attempts' => 3,
                    'randomize_questions' => true,
                    'randomize_answers' => true,
                    'show_results_immediately' => true,
                    'status' => 'published',
                ]
            );

            // Seed 50 Questions for this module exam
            $this->seed50ModuleQuestions($exam, $module->title);

            // Assign to all interns
            foreach ($interns as $internUser) {
                ExamAssignment::firstOrCreate(
                    ['exam_id' => $exam->id, 'user_id' => $internUser->id],
                    ['assigned_by' => $admin->id, 'assigned_at' => now()]
                );
            }
        }
    }

    private function seed50ModuleQuestions(Exam $exam, string $moduleTitle): void
    {
        // Generate 50 questions per module exam (2 marks each = 100 total marks)
        for ($i = 1; $i <= 50; $i++) {
            $qText = "Question {$i}: {$moduleTitle} Concept Check {$i}";
            if ($exam->questions()->where('question_text', $qText)->exists()) continue;

            $q = Question::create([
                'exam_id' => $exam->id,
                'question_text' => "{$moduleTitle} — Technical Question {$i}: Which option correctly demonstrates principle #{$i} of {$moduleTitle}?",
                'type' => 'mcq',
                'marks' => 2, // 50 Qs x 2 marks = 100 Marks
                'difficulty' => ($i % 3 === 0) ? 'hard' : (($i % 2 === 0) ? 'medium' : 'easy'),
                'explanation' => "Detailed technical explanation for Question {$i} regarding {$moduleTitle}.",
                'order' => $i,
            ]);

            $correctIdx = ($i % 4);
            $options = [
                "Option A for Question {$i} in {$moduleTitle}",
                "Option B for Question {$i} in {$moduleTitle}",
                "Option C for Question {$i} in {$moduleTitle}",
                "Option D for Question {$i} in {$moduleTitle}",
            ];

            foreach ($options as $j => $optText) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option_text' => $optText,
                    'is_correct' => ($j === $correctIdx),
                    'order' => $j,
                ]);
            }
        }
    }
}
