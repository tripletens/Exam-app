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
                'title' => 'Applied Linear Algebra & Vector Mathematics',
                'desc' => 'Vector spaces, dot products, matrix transformations, linear equations, determinants, and eigenvalues.',
                'video_url' => 'https://www.youtube.com/watch?v=fNk_zzaMoSs',
                'lessons' => [
                    [
                        'title' => 'Vectors, Linear Transformations & Matrix Operations',
                        'content' => <<<'MD'
# Vectors & Linear Algebra Essentials

Linear algebra is the foundational mathematical language behind computer graphics, machine learning, 3D game engines, and scientific computing.

### 1. Vector Fundamentals
A vector $\vec{v}$ represents a magnitude and direction in space:
$$\vec{A} = \begin{bmatrix} a_1 \\ a_2 \end{bmatrix}, \quad \vec{B} = \begin{bmatrix} b_1 \\ b_2 \end{bmatrix}$$

### 2. Vector Dot Product Formula
The dot product multiplies corresponding components and sums the results:
$$\vec{A} \cdot \vec{B} = a_1 b_1 + a_2 b_2 = \|\vec{A}\| \|\vec{B}\| \cos(\theta)$$
- If $\vec{A} \cdot \vec{B} = 0$, the vectors are **orthogonal** (perpendicular, 90° angle).

---

### 3. Matrix Multiplication ($2 \times 2$)
Given matrix $M$ and vector $\vec{v}$:
$$\begin{bmatrix} a & b \\ c & d \end{bmatrix} \begin{bmatrix} x \\ y \end{bmatrix} = \begin{bmatrix} ax + by \\ cx + dy \end{bmatrix}$$

### 4. Determinant of a Matrix
The determinant $\det(M)$ calculates the area scaling factor of a linear transformation:
$$\det\begin{bmatrix} a & b \\ c & d \end{bmatrix} = ad - bc$$
MD,
                        'quiz' => [
                            [
                                'question' => 'What is the dot product of vectors A = [2, 3] and B = [4, 1]?',
                                'options' => ['5', '11', '14', '24'],
                                'correct' => 1,
                                'explanation' => '(2 * 4) + (3 * 1) = 8 + 3 = 11.',
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

            LearningResource::updateOrCreate(
                ['module_id' => $module->id, 'title' => "{$modData['title']} — Video Lecture"],
                [
                    'course_id' => $csCourse->id,
                    'type' => 'youtube',
                    'url' => $modData['video_url'],
                    'description' => "Full video lecture covering {$modData['title']}.",
                    'is_required' => true,
                    'duration_minutes' => 30,
                ]
            );
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
                    'description' => "Official 2-Hour Module Certification Exam for {$module->title}. Features a 200-question pool with 50 randomly sampled questions per attempt to prevent cramming. 100 Marks Total.",
                    'duration_minutes' => 120, // 2 Hours
                    'pass_percentage' => 70, // 70% Pass mark
                    'max_attempts' => 5, // 5 retries allowed
                    'randomize_questions' => true,
                    'randomize_answers' => true,
                    'show_results_immediately' => true,
                    'status' => 'published',
                ]
            );

            // Assign to all interns
            foreach ($interns as $internUser) {
                ExamAssignment::firstOrCreate(
                    ['exam_id' => $exam->id, 'user_id' => $internUser->id],
                    ['assigned_by' => $admin->id, 'assigned_at' => now()]
                );
            }
        }
    }
}
