<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\LearningResource;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@lythub.com')->first();
        $intern = User::where('email', 'intern@lythub.com')->first();

        // ─── Course 1: Cybersecurity Fundamentals ───────────────────────────────
        $cyberCourse = Course::updateOrCreate(
            ['slug' => 'cybersecurity-fundamentals'],
            [
                'created_by' => $admin->id,
                'title' => 'Cybersecurity Fundamentals',
                'description' => 'A comprehensive introduction to cybersecurity concepts, tools, and practices used in the industry.',
                'category' => 'Cybersecurity',
                'difficulty' => 'beginner',
                'status' => 'published',
                'estimated_duration' => 1200,
            ]
        );

        $cyberModules = [
            ['Cybersecurity Fundamentals', 'Introduction to the cybersecurity landscape, CIA triad, and key concepts.'],
            ['Networking Fundamentals', 'TCP/IP, OSI model, DNS, HTTP/S, and network security basics.'],
            ['Linux Fundamentals', 'Linux command line, file system, users, permissions, and shell scripting.'],
            ['Web Technologies', 'How the web works, HTTP methods, cookies, sessions, and web architecture.'],
            ['Authentication & Authorization', 'Password hashing, MFA, OAuth 2.0, JWT tokens, and access control.'],
            ['OWASP Top 10', 'The ten most critical web application security risks with examples.'],
            ['SQL Injection', 'Understanding, exploiting, and preventing SQL injection vulnerabilities.'],
            ['XSS & CSRF', 'Cross-site scripting and cross-site request forgery — theory and defense.'],
            ['API Security', 'REST API security, authentication, rate limiting, and OWASP API risks.'],
            ['Incident Response', 'Detection, containment, eradication, and recovery from security incidents.'],
        ];

        foreach ($cyberModules as $i => [$title, $desc]) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $cyberCourse->id, 'title' => $title],
                ['description' => $desc, 'order' => $i + 1]
            );

            // Add 2 lessons per module
            Lesson::updateOrCreate(
                ['module_id' => $module->id, 'title' => "Introduction to {$title}"],
                ['content' => "This lesson covers the foundational concepts of {$title}. By the end, you should understand the core principles and be able to apply them in practice.", 'order' => 1, 'duration_minutes' => 30]
            );
            Lesson::updateOrCreate(
                ['module_id' => $module->id, 'title' => "{$title} — Practical Application"],
                ['content' => "In this lesson, we apply {$title} concepts through guided exercises and real-world scenarios.", 'order' => 2, 'duration_minutes' => 45]
            );

            // Add YouTube resource per module
            LearningResource::updateOrCreate(
                ['module_id' => $module->id, 'title' => "{$title} — Video Introduction"],
                [
                    'course_id' => $cyberCourse->id,
                    'type' => 'youtube',
                    'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'description' => "Watch this introductory video on {$title}.",
                    'is_required' => true,
                    'duration_minutes' => 20,
                ]
            );
        }

        // Cybersecurity Exam
        $cyberExam = Exam::updateOrCreate(
            ['title' => 'Cybersecurity Fundamentals Assessment'],
            [
                'course_id' => $cyberCourse->id,
                'created_by' => $admin->id,
                'description' => 'Test your understanding of core cybersecurity concepts covered in this course.',
                'duration_minutes' => 30,
                'pass_percentage' => 70,
                'max_attempts' => 2,
                'randomize_questions' => true,
                'randomize_answers' => true,
                'show_results_immediately' => true,
                'status' => 'published',
            ]
        );
        $this->seedCyberQuestions($cyberExam);

        // Enroll sample intern
        CourseEnrollment::firstOrCreate(
            ['user_id' => $intern->id, 'course_id' => $cyberCourse->id],
            ['enrolled_by' => $admin->id, 'enrolled_at' => now()]
        );

        // ─── Course 2: MySQL Fundamentals ───────────────────────────────────────
        $mysqlCourse = Course::updateOrCreate(
            ['slug' => 'mysql-fundamentals'],
            [
                'created_by' => $admin->id,
                'title' => 'MySQL Fundamentals',
                'description' => 'From database design to advanced queries — a complete MySQL training track for developers.',
                'category' => 'Database',
                'difficulty' => 'beginner',
                'status' => 'published',
                'estimated_duration' => 900,
            ]
        );

        $mysqlModules = [
            ['Database Fundamentals', 'What is a database? RDBMS concepts, data types, and schema design.'],
            ['SQL Basics', 'SQL syntax, DDL vs DML, creating tables, and basic queries.'],
            ['SELECT Queries', 'Retrieving data, aliases, DISTINCT, LIMIT, and OFFSET.'],
            ['INSERT, UPDATE & DELETE', 'Manipulating data in MySQL tables with safe practices.'],
            ['WHERE, ORDER BY & GROUP BY', 'Filtering, sorting, and aggregating query results.'],
            ['JOINs', 'INNER, LEFT, RIGHT, and FULL JOINs with real examples.'],
            ['Indexes & Performance', 'Creating indexes, query optimization, and EXPLAIN statements.'],
            ['Constraints & Relationships', 'Primary keys, foreign keys, unique constraints, and normalization.'],
            ['Transactions', 'ACID properties, BEGIN, COMMIT, ROLLBACK, and isolation levels.'],
            ['Database Security', 'MySQL users, privileges, GRANT/REVOKE, and securing your DB.'],
        ];

        foreach ($mysqlModules as $i => [$title, $desc]) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $mysqlCourse->id, 'title' => $title],
                ['description' => $desc, 'order' => $i + 1]
            );
            Lesson::updateOrCreate(
                ['module_id' => $module->id, 'title' => "Introduction to {$title}"],
                ['content' => "This lesson introduces {$title} in the context of MySQL database management.", 'order' => 1, 'duration_minutes' => 25]
            );
            Lesson::updateOrCreate(
                ['module_id' => $module->id, 'title' => "{$title} — Hands-on Practice"],
                ['content' => "Practice {$title} with guided SQL exercises in a real MySQL environment.", 'order' => 2, 'duration_minutes' => 40]
            );
            LearningResource::updateOrCreate(
                ['module_id' => $module->id, 'title' => "{$title} — MySQL Documentation"],
                [
                    'course_id' => $mysqlCourse->id,
                    'type' => 'documentation',
                    'url' => 'https://dev.mysql.com/doc/',
                    'description' => "Official MySQL documentation on {$title}.",
                    'is_required' => false,
                    'author' => 'Oracle MySQL Team',
                ]
            );
        }

        // MySQL Exam
        $mysqlExam = Exam::updateOrCreate(
            ['title' => 'MySQL Fundamentals Assessment'],
            [
                'course_id' => $mysqlCourse->id,
                'created_by' => $admin->id,
                'description' => 'Assess your knowledge of MySQL from basic queries to security.',
                'duration_minutes' => 30,
                'pass_percentage' => 70,
                'max_attempts' => 2,
                'randomize_questions' => true,
                'randomize_answers' => true,
                'show_results_immediately' => true,
                'status' => 'published',
            ]
        );
        $this->seedMysqlQuestions($mysqlExam);

        CourseEnrollment::firstOrCreate(
            ['user_id' => $intern->id, 'course_id' => $mysqlCourse->id],
            ['enrolled_by' => $admin->id, 'enrolled_at' => now()]
        );
    }

    private function seedCyberQuestions(Exam $exam): void
    {
        $questions = [
            ['What does CIA stand for in cybersecurity?', ['Control, Integrity, Access', 'Confidentiality, Integrity, Availability', 'Compliance, Identity, Authorization', 'Central Intelligence Agency'], 1, 'CIA Triad: Confidentiality, Integrity, Availability — the three pillars of information security.'],
            ['Which layer of the OSI model handles IP addressing?', ['Layer 2 — Data Link', 'Layer 3 — Network', 'Layer 4 — Transport', 'Layer 7 — Application'], 1, 'The Network layer (Layer 3) is responsible for logical addressing and routing using IP.'],
            ['What is the purpose of a firewall?', ['To speed up network traffic', 'To monitor and control incoming/outgoing traffic', 'To encrypt data at rest', 'To assign IP addresses'], 1, 'A firewall monitors and controls network traffic based on predefined security rules.'],
            ['Which OWASP risk involves running malicious scripts in a browser?', ['SQL Injection', 'Cross-Site Scripting (XSS)', 'CSRF', 'Broken Authentication'], 1, 'XSS injects client-side scripts into web pages viewed by other users.'],
            ['What hashing algorithm is recommended for passwords?', ['MD5', 'SHA-1', 'bcrypt', 'Base64'], 2, 'bcrypt is a slow hashing algorithm designed for passwords. MD5 and SHA-1 are too fast and vulnerable.'],
            ['What does SQL injection target?', ['The web server', 'The database layer via unsanitized input', 'The DNS server', 'The user\'s browser'], 1, 'SQL injection exploits unsanitized user input to manipulate database queries.'],
            ['Which HTTP method is typically used to submit a login form?', ['GET', 'POST', 'PUT', 'DELETE'], 1, 'POST is used for login because it sends credentials in the request body, not the URL.'],
            ['What is a VPN used for?', ['Speed up internet browsing', 'Create an encrypted tunnel for secure communication', 'Assign public IP addresses', 'Block malware'], 1, 'A VPN encrypts traffic between a client and server, securing communication over public networks.'],
            ['What is two-factor authentication (2FA)?', ['Using two passwords', 'Using a password plus a second verification factor', 'Logging in from two devices', 'Using biometrics only'], 1, '2FA requires something you know (password) and something you have or are (token/biometrics).'],
            ['What does HTTPS use to encrypt traffic?', ['MD5', 'TLS/SSL', 'Base64', 'AES-256 standalone'], 1, 'HTTPS uses TLS (Transport Layer Security) to encrypt HTTP traffic between client and server.'],
        ];

        foreach ($questions as $i => [$qText, $options, $correctIdx, $explanation]) {
            if ($exam->questions()->where('question_text', $qText)->exists()) continue;
            $q = Question::create([
                'exam_id' => $exam->id,
                'question_text' => $qText,
                'type' => 'mcq',
                'marks' => 10,
                'difficulty' => 'medium',
                'explanation' => $explanation,
                'order' => $i + 1,
            ]);
            foreach ($options as $j => $optText) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option_text' => $optText,
                    'is_correct' => $j === $correctIdx,
                    'order' => $j,
                ]);
            }
        }
    }

    private function seedMysqlQuestions(Exam $exam): void
    {
        $questions = [
            ['What does SQL stand for?', ['Structured Question Language', 'Structured Query Language', 'Simple Query Logic', 'Standard Query Lookup'], 1, 'SQL stands for Structured Query Language — used to communicate with databases.'],
            ['Which SQL statement retrieves data from a table?', ['INSERT', 'UPDATE', 'SELECT', 'DELETE'], 2, 'SELECT is used to query and retrieve data from one or more tables.'],
            ['What does a PRIMARY KEY constraint do?', ['Allows NULL values', 'Uniquely identifies each row, cannot be NULL', 'Creates an index automatically only', 'Links two tables'], 1, 'A PRIMARY KEY uniquely identifies each row in a table and cannot contain NULL values.'],
            ['Which JOIN returns only matching rows from both tables?', ['LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN', 'FULL OUTER JOIN'], 2, 'INNER JOIN returns only rows where there is a match in both tables.'],
            ['What does GROUP BY do?', ['Sorts results', 'Groups rows sharing a value for use with aggregate functions', 'Filters results', 'Joins tables'], 1, 'GROUP BY groups rows with the same values in specified columns, enabling aggregate functions like COUNT, SUM, AVG.'],
            ['Which command removes all rows from a table without logging individual row deletions?', ['DROP TABLE', 'DELETE FROM table', 'TRUNCATE TABLE', 'REMOVE TABLE'], 2, 'TRUNCATE removes all rows fast without logging individual deletions; DELETE is logged row-by-row.'],
            ['What is a FOREIGN KEY?', ['A duplicate primary key', 'A key that links a column to a primary key in another table', 'An index on a non-unique column', 'A composite key'], 1, 'A FOREIGN KEY references the PRIMARY KEY of another table to enforce referential integrity.'],
            ['Which clause filters aggregated results?', ['WHERE', 'HAVING', 'ORDER BY', 'LIMIT'], 1, 'HAVING filters results after GROUP BY; WHERE filters before aggregation.'],
            ['What does an INDEX do in MySQL?', ['Stores data in memory', 'Speeds up data retrieval at the cost of storage', 'Encrypts table data', 'Adds constraints'], 1, 'An INDEX creates a data structure that speeds up SELECT queries at the cost of extra storage and slower writes.'],
            ['Which privilege allows a MySQL user to read data only?', ['ALL PRIVILEGES', 'INSERT', 'SELECT', 'EXECUTE'], 2, 'The SELECT privilege grants read-only access to query table data without modifying it.'],
        ];

        foreach ($questions as $i => [$qText, $options, $correctIdx, $explanation]) {
            if ($exam->questions()->where('question_text', $qText)->exists()) continue;
            $q = Question::create([
                'exam_id' => $exam->id,
                'question_text' => $qText,
                'type' => 'mcq',
                'marks' => 10,
                'difficulty' => 'medium',
                'explanation' => $explanation,
                'order' => $i + 1,
            ]);
            foreach ($options as $j => $optText) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option_text' => $optText,
                    'is_correct' => $j === $correctIdx,
                    'order' => $j,
                ]);
            }
        }
    }
}
