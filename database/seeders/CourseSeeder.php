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

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@lythub.com')->first();
        $interns = User::where('role', 'intern')->get();
        $sampleIntern = User::where('email', 'intern@lythub.com')->first();

        // ─── Course 1: Cybersecurity Fundamentals ───────────────────────────────
        $cyberCourse = Course::updateOrCreate(
            ['slug' => 'cybersecurity-fundamentals'],
            [
                'created_by' => $admin->id,
                'title' => 'Cybersecurity Fundamentals',
                'description' => 'A comprehensive, industry-aligned cybersecurity course covering network security, Linux administration, authentication, web vulnerabilities (OWASP Top 10), SQL injection, and incident response.',
                'category' => 'Cybersecurity',
                'difficulty' => 'beginner',
                'status' => 'published',
                'estimated_duration' => 1200,
            ]
        );

        $cyberModules = [
            [
                'title' => 'Cybersecurity Fundamentals & The CIA Triad',
                'desc' => 'Core concepts of information security, threat modeling, and security controls.',
                'video_url' => 'https://www.youtube.com/watch?v=inWWhr5tnEA',
                'lessons' => [
                    [
                        'title' => 'Understanding the CIA Triad & Threat Landscape',
                        'content' => <<<'MD'
# Introduction to Information Security

The **CIA Triad** is the foundational framework for information security across all modern enterprise systems:

1. **Confidentiality**: Ensuring sensitive data is accessible only to authorized users (e.g., AES-256 encryption, access control lists).
2. **Integrity**: Safeguarding the accuracy and completeness of information and processing methods (e.g., SHA-256 hashing, digital signatures).
3. **Availability**: Ensuring authorized users have timely and reliable access to information and resources (e.g., load balancing, DDoS mitigation, redundant backups).

---

### Threat Vectors & Attack Categories
- **Malware**: Ransomware, Trojans, Keyloggers, Spyware.
- **Social Engineering**: Phishing, Spear Phishing, Baiting, Pretexting.
- **Network Attacks**: Man-in-the-Middle (MitM), DNS Spoofing, Packet Sniffing.

### Defense in Depth Strategy
Never rely on a single layer of security. Implement multiple layers: Perimeter Firewall → Network Segmentation → Endpoint Detection (EDR) → Encryption at Rest & In Transit → Identity & Access Management (IAM).
MD,
                        'quiz' => [
                            [
                                'question' => 'What does the "C" in the CIA Triad represent?',
                                'options' => ['Control', 'Confidentiality', 'Compliance', 'Centralization'],
                                'correct' => 1,
                                'explanation' => 'Confidentiality guarantees that data is kept secret from unauthorized individuals.',
                            ],
                            [
                                'question' => 'Which security mechanism primarily ensures data Integrity?',
                                'options' => ['Firewalls', 'AES-256 Encryption', 'SHA-256 Cryptographic Hashing', 'Load Balancers'],
                                'correct' => 2,
                                'explanation' => 'Cryptographic hashes produce unique checksums; any modification alters the hash value, detecting tampering.',
                            ]
                        ]
                    ],
                    [
                        'title' => 'Risk Assessment & Defense-in-Depth',
                        'content' => <<<'MD'
# Risk Assessment & Risk Mitigation Frameworks

Risk is defined as the probability of a threat actor exploiting a vulnerability and the resulting business impact:

Risk = Threat x Vulnerability x Impact

### Key Risk Mitigation Options
- **Risk Avoidance**: Ceasing the risky activity (e.g., disabling legacy protocols like Telnet/FTP).
- **Risk Mitigation**: Implementing security controls (e.g., deploying WAF, MFA).
- **Risk Transference**: Offloading risk to a third party (e.g., cyber insurance).
- **Risk Acceptance**: Acknowledging the risk when mitigation cost exceeds loss impact.

### Security Frameworks Overview
- **NIST CSF**: Identify, Protect, Detect, Respond, Recover.
- **ISO/IEC 27001**: International standard for Information Security Management Systems (ISMS).
MD,
                        'quiz' => [
                            [
                                'question' => 'Purchasing cyber insurance for an enterprise is an example of which risk strategy?',
                                'options' => ['Risk Avoidance', 'Risk Mitigation', 'Risk Transference', 'Risk Acceptance'],
                                'correct' => 2,
                                'explanation' => 'Cyber insurance transfers financial loss impact to a third-party insurer.',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Networking Fundamentals for Security Engineers',
                'desc' => 'TCP/IP protocol suite, OSI 7-layer model, Wireshark packet analysis, and DNS security.',
                'video_url' => 'https://www.youtube.com/watch?v=IPvYjXCsTg8',
                'lessons' => [
                    [
                        'title' => 'OSI 7-Layer Model vs. TCP/IP Architecture',
                        'content' => <<<'MD'
# Network Protocols & Layered Architecture

Understanding network protocols is mandatory for security analysis, intrusion detection, and packet inspection.

### OSI Model Layers
1. **Physical**: Bits over medium (Ethernet cables, Fiber, RF).
2. **Data Link**: MAC addressing, Ethernet frames, Switches.
3. **Network**: IP addressing, ICMP, Routers (IP Packets).
4. **Transport**: TCP (connection-oriented, reliable) & UDP (connectionless, fast).
5. **Session**: Session establishment, TLS handshake management.
6. **Presentation**: Data formatting, SSL/TLS Encryption, Data compression.
7. **Application**: HTTP, HTTPS, DNS, SSH, FTP, SMTP.

---

### The TCP 3-Way Handshake
```
Client                      Server
  | --- SYN (Seq=X) --------> |
  | <--- SYN-ACK (Seq=Y,Ack=X+1) --- |
  | --- ACK (Ack=Y+1) -------> |
```
- **SYN**: Client requests connection initialization.
- **SYN-ACK**: Server acknowledges and sends its own SYN.
- **ACK**: Client acknowledges server's SYN; TCP connection established.
MD,
                        'quiz' => [
                            [
                                'question' => 'What is the correct sequence of packets in the TCP 3-way handshake?',
                                'options' => ['ACK, SYN, SYN-ACK', 'SYN, SYN-ACK, ACK', 'SYN, ACK, FIN', 'CONNECT, ACCEPT, CONFIRM'],
                                'correct' => 1,
                                'explanation' => 'The handshake starts with SYN, server responds SYN-ACK, and client finishes with ACK.',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Linux System Administration & Command Line Security',
                'desc' => 'Linux shell commands, file permissions, SSH hardening, and log analysis.',
                'video_url' => 'https://www.youtube.com/watch?v=wBp0Rb-ZJak',
                'lessons' => [
                    [
                        'title' => 'Essential Linux Commands & File Permission Model',
                        'content' => <<<'MD'
# Linux CLI & File System Permissions

Linux powers over 90% of cloud servers and security infrastructure.

### File Permissions Breakdown (`chmod`)
- Read (`r`) = 4
- Write (`w`) = 2
- Execute (`x`) = 1

```bash
chmod 755 /var/www/html/script.sh
```
MD,
                        'quiz' => [
                            [
                                'question' => 'What octal numeric value represents `rwxr-xr--` permissions in Linux?',
                                'options' => ['777', '754', '644', '755'],
                                'correct' => 1,
                                'explanation' => 'rwx (7), r-x (5), r-- (4) = 754.',
                            ]
                        ]
                    ]
                ]
            ],
        ];

        foreach ($cyberModules as $i => $modData) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $cyberCourse->id, 'title' => $modData['title']],
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
                    'course_id' => $cyberCourse->id,
                    'type' => 'youtube',
                    'url' => $modData['video_url'],
                    'description' => "Full video lecture covering {$modData['title']}.",
                    'is_required' => true,
                    'duration_minutes' => 25,
                ]
            );
        }

        // Cybersecurity Exam
        $cyberExam = Exam::updateOrCreate(
            ['title' => 'Cybersecurity Fundamentals Assessment'],
            [
                'course_id' => $cyberCourse->id,
                'created_by' => $admin->id,
                'description' => 'Test your understanding of core cybersecurity concepts, network security, Linux administration, and OWASP Top 10.',
                'duration_minutes' => 30,
                'pass_percentage' => 70,
                'max_attempts' => 5,
                'randomize_questions' => true,
                'randomize_answers' => true,
                'show_results_immediately' => true,
                'status' => 'published',
            ]
        );
        $this->seedCyberQuestions($cyberExam);

        // ─── Course 2: MySQL Fundamentals ───────────────────────────────────────
        $mysqlCourse = Course::updateOrCreate(
            ['slug' => 'mysql-fundamentals'],
            [
                'created_by' => $admin->id,
                'title' => 'MySQL Fundamentals & Relational Database Design',
                'description' => 'Master MySQL 8 relational database design, complex SQL queries, JOINs, indexing, performance optimization, normalization, and ACID transactions.',
                'category' => 'Database',
                'difficulty' => 'beginner',
                'status' => 'published',
                'estimated_duration' => 900,
            ]
        );

        $mysqlModules = [
            [
                'title' => 'Database Fundamentals & Relational Schema Design',
                'desc' => 'RDBMS architecture, MySQL storage engines (InnoDB vs MyISAM), data types, and primary keys.',
                'video_url' => 'https://www.youtube.com/watch?v=7S_tz1z_5bA',
                'lessons' => [
                    [
                        'title' => 'Relational Database Concepts & Data Types',
                        'content' => <<<'MD'
# Introduction to Relational Databases & MySQL

A **Relational Database Management System (RDBMS)** structures data into tables consisting of rows and columns with explicit relationships between tables.
MD,
                        'quiz' => [
                            [
                                'question' => 'Which MySQL storage engine supports ACID transactions and foreign key constraints?',
                                'options' => ['MyISAM', 'Memory', 'InnoDB', 'CSV'],
                                'correct' => 2,
                                'explanation' => 'InnoDB is the default MySQL engine supporting transactions and referential integrity.',
                            ]
                        ]
                    ]
                ]
            ]
        ];

        foreach ($mysqlModules as $i => $modData) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $mysqlCourse->id, 'title' => $modData['title']],
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

        // MySQL Exam
        $mysqlExam = Exam::updateOrCreate(
            ['title' => 'MySQL Fundamentals Assessment'],
            [
                'course_id' => $mysqlCourse->id,
                'created_by' => $admin->id,
                'description' => 'Assess your knowledge of MySQL from DDL/DML to JOINs, B-Tree indexes, transactions, and security.',
                'duration_minutes' => 30,
                'pass_percentage' => 70,
                'max_attempts' => 5,
                'randomize_questions' => true,
                'randomize_answers' => true,
                'show_results_immediately' => true,
                'status' => 'published',
            ]
        );
        $this->seedMysqlQuestions($mysqlExam);

        // ─── Standalone Exam 1: Linux & Server Security Exam ──────────────────
        $linuxExam = Exam::updateOrCreate(
            ['title' => 'Linux CLI & Server Security Certification Exam'],
            [
                'created_by' => $admin->id,
                'description' => 'Comprehensive exam evaluating Linux CLI skills, SSH hardening, file permissions, and system log analysis.',
                'duration_minutes' => 45,
                'pass_percentage' => 75,
                'max_attempts' => 3,
                'randomize_questions' => true,
                'randomize_answers' => true,
                'show_results_immediately' => true,
                'status' => 'published',
            ]
        );
        $this->seedLinuxQuestions($linuxExam);

        // ─── Standalone Exam 2: OWASP Web Security Challenge ─────────────────
        $owaspExam = Exam::updateOrCreate(
            ['title' => 'OWASP Top 10 Web Application Security Challenge'],
            [
                'created_by' => $admin->id,
                'description' => 'Advanced assessment covering Broken Access Control, SQL Injection, XSS, CSRF, and secure coding practices.',
                'duration_minutes' => 40,
                'pass_percentage' => 70,
                'max_attempts' => 3,
                'randomize_questions' => true,
                'randomize_answers' => true,
                'show_results_immediately' => true,
                'status' => 'published',
            ]
        );
        $this->seedOwaspQuestions($owaspExam);

        // ─── Enroll & Assign to Interns ────────────────────────────────────────
        foreach ($interns as $internUser) {
            CourseEnrollment::firstOrCreate(
                ['user_id' => $internUser->id, 'course_id' => $cyberCourse->id],
                ['enrolled_by' => $admin->id, 'enrolled_at' => now()]
            );

            CourseEnrollment::firstOrCreate(
                ['user_id' => $internUser->id, 'course_id' => $mysqlCourse->id],
                ['enrolled_by' => $admin->id, 'enrolled_at' => now()]
            );

            // Assign ALL exams explicitly to every intern
            foreach ([$cyberExam, $mysqlExam, $linuxExam, $owaspExam] as $ex) {
                ExamAssignment::firstOrCreate(
                    ['exam_id' => $ex->id, 'user_id' => $internUser->id],
                    ['assigned_by' => $admin->id, 'assigned_at' => now()]
                );
            }
        }
    }

    private function seedCyberQuestions(Exam $exam): void
    {
        $questions = [
            ['What does CIA stand for in cybersecurity?', ['Control, Integrity, Access', 'Confidentiality, Integrity, Availability', 'Compliance, Identity, Authorization', 'Central Intelligence Agency'], 1, 'CIA Triad: Confidentiality, Integrity, Availability — the three pillars of information security.'],
            ['Which layer of the OSI model handles IP addressing?', ['Layer 2 — Data Link', 'Layer 3 — Network', 'Layer 4 — Transport', 'Layer 7 — Application'], 1, 'The Network layer (Layer 3) is responsible for logical addressing and routing using IP.'],
            ['What is the purpose of a firewall?', ['To speed up network traffic', 'To monitor and control incoming/outgoing traffic', 'To encrypt data at rest', 'To assign IP addresses'], 1, 'A firewall monitors and controls network traffic based on predefined security rules.'],
            ['Which OWASP risk involves running malicious scripts in a browser?', ['SQL Injection', 'Cross-Site Scripting (XSS)', 'CSRF', 'Broken Authentication'], 1, 'XSS injects client-side scripts into web pages viewed by other users.'],
            ['What hashing algorithm is recommended for passwords?', ['MD5', 'SHA-1', 'bcrypt', 'Base64'], 2, 'bcrypt is a slow hashing algorithm designed for passwords. MD5 and SHA-1 are too fast and vulnerable.'],
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

    private function seedLinuxQuestions(Exam $exam): void
    {
        $questions = [
            ['Which command changes file permissions in Linux?', ['chown', 'chmod', 'chgrp', 'ps'], 1, 'chmod (change mode) modifies file access permissions.'],
            ['Where are Linux user password hashes stored securely?', ['/etc/passwd', '/etc/shadow', '/var/log/auth.log', '/usr/bin/passwd'], 1, '/etc/shadow stores encrypted password hashes readable only by root.'],
            ['Which SSH directive disables password-based authentication?', ['PermitRootLogin no', 'PasswordAuthentication no', 'AllowUsers none', 'PubkeyAuthentication false'], 1, 'PasswordAuthentication no forces SSH key-based authentication.'],
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

    private function seedOwaspQuestions(Exam $exam): void
    {
        $questions = [
            ['Which vulnerability occurs when user input is concatenated directly into SQL queries?', ['Cross-Site Scripting', 'SQL Injection', 'Insecure Deserialization', 'SSRF'], 1, 'SQL Injection occurs when untrusted input alters database query structure.'],
            ['What is the best defense against SQL Injection?', ['Escaping special characters only', 'Prepared Statements with Parameter Bindings', 'Client-side JavaScript validation', 'Web Application Firewall only'], 1, 'Prepared statements separate SQL syntax from parameters, neutralizing injection attacks.'],
            ['What attack involves tricking a server into sending HTTP requests to internal or external systems?', ['CSRF', 'SSRF (Server-Side Request Forgery)', 'XSS', 'IDOR'], 1, 'SSRF forces a web server to make unauthorized requests to internal or external infrastructure.'],
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
