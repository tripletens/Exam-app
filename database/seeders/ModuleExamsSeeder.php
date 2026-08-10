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
                    'description' => "Official 2-Hour Module Certification Exam for {$module->title}. 50 Questions, 100 Marks Total.",
                    'duration_minutes' => 120, // 2 Hours
                    'pass_percentage' => 70, // 70% Pass mark
                    'max_attempts' => 5, // 5 retries allowed
                    'randomize_questions' => true,
                    'randomize_answers' => true,
                    'show_results_immediately' => true,
                    'status' => 'published',
                ]
            );

            // Clear old dummy questions if re-seeding
            $exam->questions()->delete();

            // Seed 50 realistic questions for this module exam
            $this->seed50RealisticQuestions($exam, $module->title);

            // Assign to all interns
            foreach ($interns as $internUser) {
                ExamAssignment::firstOrCreate(
                    ['exam_id' => $exam->id, 'user_id' => $internUser->id],
                    ['assigned_by' => $admin->id, 'assigned_at' => now()]
                );
            }
        }
    }

    private function seed50RealisticQuestions(Exam $exam, string $moduleTitle): void
    {
        $pool = $this->getQuestionPoolForModule($moduleTitle);

        for ($i = 1; $i <= 50; $i++) {
            $qData = $pool[($i - 1) % count($pool)];

            $questionText = ($i > count($pool))
                ? "[Part " . ceil($i / count($pool)) . "] {$qData['question']}"
                : $qData['question'];

            $q = Question::create([
                'exam_id' => $exam->id,
                'question_text' => $questionText,
                'type' => 'mcq',
                'marks' => 2, // 50 Qs x 2 marks = 100 Marks
                'difficulty' => ($i % 3 === 0) ? 'hard' : (($i % 2 === 0) ? 'medium' : 'easy'),
                'explanation' => $qData['explanation'],
                'order' => $i,
            ]);

            foreach ($qData['options'] as $j => $optText) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option_text' => $optText,
                    'is_correct' => ($j === $qData['correct']),
                    'order' => $j,
                ]);
            }
        }
    }

    private function getQuestionPoolForModule(string $title): array
    {
        $lower = strtolower($title);

        if (str_contains($lower, 'cybersecurity') || str_contains($lower, 'cia')) {
            return [
                ['question' => 'What does the "C" in the CIA Triad represent?', 'options' => ['Control', 'Confidentiality', 'Compliance', 'Centralization'], 'correct' => 1, 'explanation' => 'Confidentiality ensures sensitive data is accessible only to authorized users.'],
                ['question' => 'Which security mechanism guarantees data Integrity?', 'options' => ['Firewalls', 'AES-256 Encryption', 'SHA-256 Cryptographic Hashing', 'Load Balancers'], 'correct' => 2, 'explanation' => 'SHA-256 generates a unique checksum; any alteration changes the hash value.'],
                ['question' => 'What is the primary objective of Defense-in-Depth?', 'options' => ['To eliminate all threats', 'To deploy layered security controls so if one fails, others protect the system', 'To reduce infrastructure costs', 'To enforce 30-day password rotations'], 'correct' => 1, 'explanation' => 'Defense-in-Depth uses overlapping security layers to protect critical assets.'],
                ['question' => 'Which risk strategy involves purchasing a cyber insurance policy?', 'options' => ['Risk Avoidance', 'Risk Mitigation', 'Risk Transference', 'Risk Acceptance'], 'correct' => 2, 'explanation' => 'Cyber insurance transfers potential financial risk to an insurer.'],
                ['question' => 'Which hashing algorithm is specifically designed for password hashing?', 'options' => ['MD5', 'SHA-1', 'bcrypt', 'CRC32'], 'correct' => 2, 'explanation' => 'bcrypt includes salt and cost factors to resist hardware brute-force attacks.'],
                ['question' => 'Which attack category tricks high-level executives specifically?', 'options' => ['Whaling', 'Vishing', 'Smishing', 'Baiting'], 'correct' => 0, 'explanation' => 'Whaling targets senior executives directly.'],
                ['question' => 'What does Multi-Factor Authentication (MFA) require?', 'options' => ['Two identical passwords', 'Two or more independent authentication factors', 'Logging in from two browsers', 'Changing passwords monthly'], 'correct' => 1, 'explanation' => 'MFA combines something you know, have, or are.'],
                ['question' => 'Which NIST CSF core function restores operations after a security incident?', 'options' => ['Identify', 'Protect', 'Respond', 'Recover'], 'correct' => 3, 'explanation' => 'Recover restores services and systems following an incident.'],
                ['question' => 'What type of malware encrypts victim files and demands payment for key recovery?', 'options' => ['Spyware', 'Ransomware', 'Adware', 'Rootkit'], 'correct' => 1, 'explanation' => 'Ransomware encrypts target data and demands ransom.'],
                ['question' => 'Which access control model enforces access based on user security clearances and data classification labels?', 'options' => ['DAC (Discretionary Access Control)', 'MAC (Mandatory Access Control)', 'RBAC (Role-Based Access Control)', 'ABAC'], 'correct' => 1, 'explanation' => 'MAC uses strict security clearances and classification labels.'],
            ];
        }

        if (str_contains($lower, 'network') || str_contains($lower, 'tcp')) {
            return [
                ['question' => 'At which OSI layer do IPv4 and IPv6 routers operate?', 'options' => ['Layer 2 — Data Link', 'Layer 3 — Network', 'Layer 4 — Transport', 'Layer 7 — Application'], 'correct' => 1, 'explanation' => 'Layer 3 Network layer handles IP routing and logical addressing.'],
                ['question' => 'What is the correct sequence of packets in a TCP 3-Way Handshake?', 'options' => ['ACK, SYN, SYN-ACK', 'SYN, SYN-ACK, ACK', 'SYN, ACK, FIN', 'CONNECT, ACCEPT, READY'], 'correct' => 1, 'explanation' => 'TCP connection sequence: SYN -> SYN-ACK -> ACK.'],
                ['question' => 'Which DNS record maps a domain name to an IPv4 address?', 'options' => ['AAAA Record', 'MX Record', 'A Record', 'TXT Record'], 'correct' => 2, 'explanation' => 'A Record maps hostname to IPv4 address.'],
                ['question' => 'Which Transport Layer protocol is connectionless and unacknowledged?', 'options' => ['TCP', 'UDP', 'SCTP', 'BGP'], 'correct' => 1, 'explanation' => 'UDP provides fast, unacknowledged datagram transmission.'],
                ['question' => 'What subnet mask corresponds to a `/24` CIDR prefix?', 'options' => ['255.255.0.0', '255.255.255.0', '255.255.255.128', '255.0.0.0'], 'correct' => 1, 'explanation' => '/24 prefix equals 255.255.255.0.'],
                ['question' => 'Which command traces hop-by-hop packet routes across networks?', 'options' => ['ping', 'traceroute / tracert', 'netstat', 'nslookup'], 'correct' => 1, 'explanation' => 'traceroute identifies router hops along the packet path.'],
                ['question' => 'What attack corrupts DNS resolver caches with forged IP responses?', 'options' => ['DNS Cache Poisoning', 'SYN Flood', 'ARP Spoofing', 'BGP Hijacking'], 'correct' => 0, 'explanation' => 'DNS Cache Poisoning injects false DNS mappings.'],
                ['question' => 'Which protocol encrypts web traffic over default port 443?', 'options' => ['HTTP', 'HTTPS (TLS/SSL)', 'SSH', 'FTP'], 'correct' => 1, 'explanation' => 'HTTPS uses TLS encryption on port 443.'],
                ['question' => 'What protocol translates IP addresses into hardware MAC addresses on a local Ethernet subnet?', 'options' => ['DNS', 'DHCP', 'ARP (Address Resolution Protocol)', 'ICMP'], 'correct' => 2, 'explanation' => 'ARP resolves IP addresses to Layer 2 MAC addresses.'],
                ['question' => 'Which Wireshark display filter captures HTTP POST requests specifically?', 'options' => ['http.request.method == "POST"', 'tcp.port == 80', 'ip.addr == 127.0.0.1', 'dns.flags.response == 1'], 'correct' => 0, 'explanation' => 'http.request.method == "POST" filters HTTP POST packets.'],
            ];
        }

        if (str_contains($lower, 'linux') || str_contains($lower, 'ssh') || str_contains($lower, 'cli')) {
            return [
                ['question' => 'What octal numeric value corresponds to `rwxr-xr--` file permissions in Linux?', 'options' => ['777', '754', '644', '755'], 'correct' => 1, 'explanation' => 'rwx (7), r-x (5), r-- (4) = 754.'],
                ['question' => 'Which Linux file contains user UIDs, usernames, and default shells?', 'options' => ['/etc/shadow', '/etc/passwd', '/etc/group', '/var/log/auth.log'], 'correct' => 1, 'explanation' => '/etc/passwd lists local account details.'],
                ['question' => 'Which command changes file user and group ownership in Linux?', 'options' => ['chmod', 'chown', 'chgrp', 'umask'], 'correct' => 1, 'explanation' => 'chown modifies file owner and group.'],
                ['question' => 'In `/etc/ssh/sshd_config`, which setting disables password authentication?', 'options' => ['PermitRootLogin no', 'PasswordAuthentication no', 'AllowUsers none', 'PubkeyAuthentication no'], 'correct' => 1, 'explanation' => 'Setting `PasswordAuthentication no` forces public key authentication.'],
                ['question' => 'Which command displays interactive real-time CPU and memory usage in Linux?', 'options' => ['ps -ef', 'top / htop', 'df -h', 'free -m'], 'correct' => 1, 'explanation' => 'top/htop monitors running processes and system resources live.'],
                ['question' => 'Where are SSH authentication logs recorded on Ubuntu/Debian systems?', 'options' => ['/var/log/syslog', '/var/log/auth.log', '/var/log/nginx/access.log', '/etc/ssh/log'], 'correct' => 1, 'explanation' => '/var/log/auth.log logs SSH logins and authorization events.'],
                ['question' => 'Which Linux command sets default file creation permission masks?', 'options' => ['chmod 600', 'umask', 'chown root', 'setfacl'], 'correct' => 1, 'explanation' => 'umask defines default initial permission masks.'],
                ['question' => 'Which command searches files for lines matching a specified pattern?', 'options' => ['find', 'grep', 'awk', 'sed'], 'correct' => 1, 'explanation' => 'grep searches text files for regex pattern matches.'],
                ['question' => 'What command displays disk space usage across mounted filesystems in human-readable format?', 'options' => ['du -sh', 'df -h', 'ls -la', 'fdisk -l'], 'correct' => 1, 'explanation' => 'df -h reports disk usage in megabytes/gigabytes.'],
                ['question' => 'Which command gracefully terminates a running process by its PID?', 'options' => ['kill -9 <pid>', 'kill <pid>', 'stop <pid>', 'end <pid>'], 'correct' => 1, 'explanation' => 'kill <pid> sends default SIGTERM (15) allowing graceful cleanup.'],
            ];
        }

        if (str_contains($lower, 'owasp') || str_contains($lower, 'web security') || str_contains($lower, 'sql injection')) {
            return [
                ['question' => 'Which OWASP Top 10 vulnerability currently holds the #1 ranking for web application risks?', 'options' => ['SQL Injection', 'Broken Access Control', 'Cryptographic Failures', 'SSRF'], 'correct' => 1, 'explanation' => 'Broken Access Control (A01) is ranked #1 by OWASP.'],
                ['question' => 'What mechanism completely neutralizes SQL Injection vulnerabilities?', 'options' => ['Input sanitization regex only', 'Prepared Statements with Parameterized Queries', 'Web Application Firewall (WAF) only', 'Base64 encoding'], 'correct' => 1, 'explanation' => 'Prepared statements separate SQL code from user parameters.'],
                ['question' => 'Changing URL parameter `/api/user/10` to `/api/user/11` to view private data is an example of:', 'options' => ['Cross-Site Scripting (XSS)', 'Insecure Direct Object Reference (IDOR)', 'CSRF', 'SQL Injection'], 'correct' => 1, 'explanation' => 'IDOR exposes direct internal object identifiers without access authorization checks.'],
                ['question' => 'Which attack type injects malicious JavaScript into web pages viewed by other users?', 'options' => ['SQL Injection', 'Cross-Site Scripting (XSS)', 'CSRF', 'Command Injection'], 'correct' => 1, 'explanation' => 'XSS executes client-side scripts in victim browsers.'],
                ['question' => 'What HTTP security header instructs browsers to communicate exclusively over HTTPS?', 'options' => ['Content-Security-Policy', 'HTTP Strict Transport Security (HSTS)', 'X-Frame-Options', 'X-Content-Type-Options'], 'correct' => 1, 'explanation' => 'HSTS enforces HTTPS connections for all browser requests.'],
                ['question' => 'Which attack tricks an authenticated browser into submitting unauthorized requests to a web app?', 'options' => ['Cross-Site Request Forgery (CSRF)', 'XSS', 'SSRF', 'Directory Traversal'], 'correct' => 0, 'explanation' => 'CSRF exploits stored browser session cookies to execute unauthorized actions.'],
                ['question' => 'Which HTTP response code indicates an unauthenticated request?', 'options' => ['400 Bad Request', '401 Unauthorized', '403 Forbidden', '404 Not Found'], 'correct' => 1, 'explanation' => '401 Unauthorized indicates missing or invalid authentication credentials.'],
                ['question' => 'What does Server-Side Request Forgery (SSRF) allow an attacker to do?', 'options' => ['Execute browser scripts', 'Force the web server to make requests to internal or external systems', 'Dump database schemas', 'Modify local files'], 'correct' => 1, 'explanation' => 'SSRF forces the backend server to send requests to target endpoints.'],
                ['question' => 'What HTTP header prevents clickjacking attacks by controlling iframe embedding?', 'options' => ['X-Frame-Options', 'HSTS', 'CORS', 'Content-Type'], 'correct' => 0, 'explanation' => 'X-Frame-Options restricts whether a page can be embedded inside an iframe.'],
                ['question' => 'In Laravel, what mechanism provides automated protection against Cross-Site Request Forgery?', 'options' => ['Sanctum Token', 'CSRF Token Middleware (@csrf / X-CSRF-TOKEN)', 'Eloquent ORM', 'Blade Compiler'], 'correct' => 1, 'explanation' => 'Laravel verifies CSRF tokens on incoming POST/PUT/DELETE web requests.'],
            ];
        }

        if (str_contains($lower, 'database') || str_contains($lower, 'mysql') || str_contains($lower, 'dml') || str_contains($lower, 'join') || str_contains($lower, 'index') || str_contains($lower, 'transaction')) {
            return [
                ['question' => 'Which default MySQL storage engine supports ACID transactions and foreign keys?', 'options' => ['MyISAM', 'Memory', 'InnoDB', 'CSV'], 'correct' => 2, 'explanation' => 'InnoDB is the default transaction-safe engine supporting foreign keys.'],
                ['question' => 'Which SQL statement clears all rows from a table quickly and resets auto-increment counters?', 'options' => ['DELETE FROM table;', 'DROP TABLE table;', 'TRUNCATE TABLE table;', 'REMOVE TABLE table;'], 'correct' => 2, 'explanation' => 'TRUNCATE drops and recreates table structure, resetting auto-increment IDs.'],
                ['question' => 'Which JOIN type returns all records from the left table and matching records from the right?', 'options' => ['INNER JOIN', 'LEFT JOIN (LEFT OUTER JOIN)', 'RIGHT JOIN', 'FULL JOIN'], 'correct' => 1, 'explanation' => 'LEFT JOIN returns all left-table rows, padding unmatched right columns with NULL.'],
                ['question' => 'Which SQL clause filters aggregate calculation results AFTER `GROUP BY`?', 'options' => ['WHERE', 'HAVING', 'ORDER BY', 'LIMIT'], 'correct' => 1, 'explanation' => 'HAVING filters aggregate values calculated by GROUP BY.'],
                ['question' => 'What constraint uniquely identifies each row in a table and cannot contain NULL values?', 'options' => ['FOREIGN KEY', 'UNIQUE', 'PRIMARY KEY', 'CHECK'], 'correct' => 2, 'explanation' => 'PRIMARY KEY uniquely identifies rows and forbids NULL values.'],
                ['question' => 'How does a B-Tree index improve database query performance?', 'options' => ['By compressing disk data', 'By reducing lookup time complexity from O(N) to O(log N)', 'By caching results in memory', 'By bypassing foreign keys'], 'correct' => 1, 'explanation' => 'B-Tree indexes structure search keys in logarithmic time complexity O(log N).'],
                ['question' => 'What does `type: ALL` indicate in a MySQL `EXPLAIN` query execution plan?', 'options' => ['An index lookup is used', 'A full table scan is occurring (inefficient query)', 'A primary key match occurred', 'Subquery execution'], 'correct' => 1, 'explanation' => '`type: ALL` means MySQL is forced to scan every row in the table.'],
                ['question' => 'Which ACID property guarantees that all statements in a transaction complete or roll back as one unit?', 'options' => ['Atomicity', 'Consistency', 'Isolation', 'Durability'], 'correct' => 0, 'explanation' => 'Atomicity guarantees all-or-nothing execution.'],
                ['question' => 'Which command utility exports a MySQL database to an SQL dump file?', 'options' => ['mysql-export', 'mysqldump', 'mysqladmin', 'db-backup'], 'correct' => 1, 'explanation' => 'mysqldump is the official command-line backup utility for MySQL.'],
                ['question' => 'Which SQL privilege grants read-only access to query database records without altering them?', 'options' => ['ALL PRIVILEGES', 'INSERT', 'SELECT', 'UPDATE'], 'correct' => 2, 'explanation' => 'SELECT grants read-only permission to query records.'],
            ];
        }

        // Default: Computer Science, Statistics & Mathematics
        return [
            ['question' => 'What is the average time complexity of a Binary Search algorithm on a sorted array of N elements?', 'options' => ['O(1)', 'O(log N)', 'O(N)', 'O(N^2)'], 'correct' => 1, 'explanation' => 'Binary Search divides the search space in half at each step, yielding O(log N).'],
            ['question' => 'Which measure of central tendency is most robust against extreme statistical outliers?', 'options' => ['Arithmetic Mean', 'Median', 'Variance', 'Range'], 'correct' => 1, 'explanation' => 'The median takes the middle position of sorted data, preventing extreme outliers from skewing it.'],
            ['question' => 'What is the output of `True XOR True` in Boolean logic algebra?', 'options' => ['True', 'False', 'Undefined', 'Null'], 'correct' => 1, 'explanation' => 'XOR returns True if and only if inputs differ. Since both are True, XOR returns False.'],
            ['question' => 'According to the Empirical Rule (68-95-99.7), what percentage of data falls within 2 standard deviations of the mean in a Normal Distribution?', 'options' => ['50%', '68%', '95%', '99.7%'], 'correct' => 2, 'explanation' => 'The Empirical Rule states ~95% of data falls within 2 standard deviations of the mean.'],
            ['question' => 'Which data structure operates on a Last In, First Out (LIFO) order?', 'options' => ['Queue', 'Stack', 'Array', 'Linked List'], 'correct' => 1, 'explanation' => 'Stacks use LIFO ordering (push and pop elements).'],
            ['question' => 'What is the dot product of vectors A = [2, 3] and B = [4, 1]?', 'options' => ['5', '11', '14', '24'], 'correct' => 1, 'explanation' => '(2 * 4) + (3 * 1) = 8 + 3 = 11.'],
            ['question' => 'What is the average time complexity for searching a key in a well-balanced Hash Map?', 'options' => ['O(1)', 'O(log N)', 'O(N)', 'O(N log N)'], 'correct' => 0, 'explanation' => 'Hash maps provide constant time O(1) average key lookups.'],
            ['question' => 'What mathematical theorem calculates the probability of an event based on prior knowledge of conditions related to the event?', 'options' => ['Pythagorean Theorem', 'Bayes\' Theorem', 'Central Limit Theorem', 'Fermat\'s Last Theorem'], 'correct' => 1, 'explanation' => 'Bayes\' Theorem calculates conditional probability P(A|B) using prior probabilities.'],
            ['question' => 'What sorting algorithm has a guaranteed worst-case time complexity of O(N log N)?', 'options' => ['QuickSort', 'MergeSort', 'BubbleSort', 'InsertionSort'], 'correct' => 1, 'explanation' => 'MergeSort divides arrays recursively and merges in O(N log N) time in all cases.'],
            ['question' => 'Which gate output is True ONLY if both inputs are True?', 'options' => ['OR Gate', 'AND Gate', 'XOR Gate', 'NAND Gate'], 'correct' => 1, 'explanation' => 'AND gate outputs True strictly when both inputs evaluate to True.'],
        ];
    }
}
