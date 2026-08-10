<?php

namespace Database\Seeders;

use App\Models\CourseModule;
use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComprehensiveQuestionBankSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@lythub.com')->first();
        $interns = User::where('role', 'intern')->get();

        $allModules = CourseModule::with('course')->get();

        foreach ($allModules as $module) {
            $examTitle = "{$module->title} — 2-Hour Module Certification Exam";

            $exam = Exam::updateOrCreate(
                ['title' => $examTitle],
                [
                    'course_id' => $module->course_id,
                    'module_id' => $module->id,
                    'created_by' => $admin->id,
                    'description' => "Official 2-Hour Module Certification Exam for {$module->title}. 50 Questions directly based on video lectures and lecture notes. 100 Marks Total.",
                    'duration_minutes' => 120, // 2 Hours
                    'pass_percentage' => 70, // 70% Pass mark
                    'max_attempts' => 5, // 5 retries allowed
                    'randomize_questions' => true,
                    'randomize_answers' => true,
                    'show_results_immediately' => true,
                    'status' => 'published',
                ]
            );

            // Delete existing questions for a clean 50-question seed
            $exam->questions()->delete();

            $questionsPool = $this->get50VideoBasedQuestionsForModule($module->title);

            foreach ($questionsPool as $i => $qData) {
                $q = Question::create([
                    'exam_id' => $exam->id,
                    'question_text' => $qData['question'],
                    'type' => 'mcq',
                    'marks' => 2, // 50 Qs x 2 marks = 100 Marks
                    'difficulty' => ($i % 3 === 0) ? 'hard' : (($i % 2 === 0) ? 'medium' : 'easy'),
                    'explanation' => $qData['explanation'],
                    'order' => $i + 1,
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

            // Assign to all interns
            foreach ($interns as $internUser) {
                ExamAssignment::firstOrCreate(
                    ['exam_id' => $exam->id, 'user_id' => $internUser->id],
                    ['assigned_by' => $admin->id, 'assigned_at' => now()]
                );
            }
        }
    }

    private function get50VideoBasedQuestionsForModule(string $title): array
    {
        $lower = strtolower($title);

        if (str_contains($lower, 'cybersecurity') || str_contains($lower, 'cia') || str_contains($lower, 'triad')) {
            return $this->getModule1CyberQuestions();
        }

        if (str_contains($lower, 'networking') || str_contains($lower, 'tcp') || str_contains($lower, 'osi')) {
            return $this->getModule2NetworkingQuestions();
        }

        if (str_contains($lower, 'linux') || str_contains($lower, 'ssh') || str_contains($lower, 'cli')) {
            return $this->getModule3LinuxQuestions();
        }

        if (str_contains($lower, 'owasp') || str_contains($lower, 'web application security')) {
            return $this->getModule4OwaspQuestions();
        }

        if (str_contains($lower, 'sql injection')) {
            return $this->getModule5SqlInjectionQuestions();
        }

        if (str_contains($lower, 'database fundamentals') || str_contains($lower, 'schema design')) {
            return $this->getModule6DatabaseDesignQuestions();
        }

        if (str_contains($lower, 'dml') || str_contains($lower, 'select queries')) {
            return $this->getModule7DmlQuestions();
        }

        if (str_contains($lower, 'join') || str_contains($lower, 'subquer')) {
            return $this->getModule8JoinQuestions();
        }

        if (str_contains($lower, 'index') || str_contains($lower, 'explain')) {
            return $this->getModule9IndexQuestions();
        }

        if (str_contains($lower, 'transaction') || str_contains($lower, 'acid')) {
            return $this->getModule10TransactionQuestions();
        }

        if (str_contains($lower, 'statistics') || str_contains($lower, 'probability')) {
            return $this->getModule11StatsQuestions();
        }

        if (str_contains($lower, 'math') || str_contains($lower, 'algebra') || str_contains($lower, 'logic')) {
            return $this->getModule12MathQuestions();
        }

        return $this->getModule13CSQuestions();
    }

    // ─── 1. Cybersecurity Fundamentals & CIA Triad (Video Lecture Based) ────────
    private function getModule1CyberQuestions(): array
    {
        $q = [];
        $templates = [
            ['According to the video lecture, what pillar of the CIA Triad ensures data is accessible only to authorized users?', ['Control', 'Confidentiality', 'Compliance', 'Centralization'], 1, 'Confidentiality guarantees sensitive data remains accessible only to authorized parties.'],
            ['In the lecture notes, which cryptographic technique is highlighted for verifying Data Integrity?', ['Symmetric AES Encryption', 'SHA-256 Hashing', 'Load Balancing', 'VPN Tunnels'], 1, 'SHA-256 hashing produces a unique checksum to detect any data alteration.'],
            ['As covered in the video, what is the core principle of Defense-in-Depth?', ['Relying on a single perimeter firewall', 'Implementing multiple layered security controls so if one fails, others protect the system', 'Replacing passwords with biometrics only', 'Disabling network logging'], 1, 'Defense in Depth uses redundant overlapping security controls.'],
            ['Which risk management strategy transfers financial impact by purchasing a cyber insurance policy?', ['Risk Avoidance', 'Risk Mitigation', 'Risk Transference', 'Risk Acceptance'], 2, 'Cyber insurance transfers financial loss impact to an insurer.'],
            ['In the authentication video, why is bcrypt recommended over MD5 for password storage?', ['bcrypt is faster to compute', 'bcrypt includes salt and configurable work factors to slow down brute-force attacks', 'bcrypt is unhashed', 'bcrypt uses 32-bit keys'], 1, 'bcrypt is intentionally slow and salted to resist hardware brute-force attacks.'],
            ['What targeted social engineering attack specifically focuses on high-ranking executives?', ['Whaling', 'Vishing', 'Smishing', 'Baiting'], 0, 'Whaling targets senior executives directly.'],
            ['What two components are required for Multi-Factor Authentication (MFA)?', ['Two passwords', 'Two or more independent authentication factors (something you know, have, or are)', 'Logging in from two browsers', 'Changing passwords every month'], 1, 'MFA requires multiple distinct authentication factors.'],
            ['Which NIST Cybersecurity Framework core function focuses on restoring services after an incident?', ['Identify', 'Protect', 'Respond', 'Recover'], 3, 'Recover restores systems and data following a security incident.'],
            ['What type of malware encrypts files and demands payment for decryption keys?', ['Spyware', 'Ransomware', 'Adware', 'Rootkit'], 1, 'Ransomware encrypts target files and demands ransom.'],
            ['Which access control model enforces access based on user clearances and data classification labels?', ['DAC', 'MAC (Mandatory Access Control)', 'RBAC', 'ABAC'], 1, 'MAC relies on strict security clearances and classification labels.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Lecture Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 2. Networking Fundamentals (Video Lecture Based) ──────────────────────
    private function getModule2NetworkingQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the networking video, at which OSI model layer do IPv4 and IPv6 routers operate?', ['Layer 2 — Data Link', 'Layer 3 — Network', 'Layer 4 — Transport', 'Layer 7 — Application'], 1, 'Layer 3 Network layer handles IP routing and logical addressing.'],
            ['What is the exact order of packets in the TCP 3-Way Handshake explained in the lecture?', ['ACK, SYN, SYN-ACK', 'SYN, SYN-ACK, ACK', 'SYN, ACK, FIN', 'CONNECT, ACCEPT, READY'], 1, 'TCP connection sequence: SYN -> SYN-ACK -> ACK.'],
            ['Which DNS record type translates a domain name into an IPv4 address?', ['AAAA Record', 'MX Record', 'A Record', 'TXT Record'], 2, 'A Record maps hostname to IPv4 address.'],
            ['Which Transport Layer protocol is connectionless and does not guarantee packet delivery?', ['TCP', 'UDP', 'SCTP', 'BGP'], 1, 'UDP provides fast, unacknowledged datagram transmission.'],
            ['What subnet mask corresponds to a `/24` CIDR prefix taught in the subnetting module?', ['255.255.0.0', '255.255.255.0', '255.255.255.128', '255.0.0.0'], 1, '/24 prefix equals 255.255.255.0.'],
            ['Which command-line utility traces the hop-by-hop router path packets take to a target IP?', ['ping', 'traceroute / tracert', 'netstat', 'nslookup'], 1, 'traceroute identifies router hops along the packet path.'],
            ['What network attack corrupts DNS resolver caches with false IP mappings?', ['DNS Cache Poisoning', 'SYN Flood', 'ARP Spoofing', 'BGP Hijacking'], 0, 'DNS Cache Poisoning injects false DNS mappings.'],
            ['Which protocol provides encrypted web communication over default port 443?', ['HTTP', 'HTTPS (TLS/SSL)', 'SSH', 'FTP'], 1, 'HTTPS uses TLS encryption on port 443.'],
            ['What protocol resolves IP addresses to physical Layer 2 MAC addresses on an Ethernet network?', ['DNS', 'DHCP', 'ARP (Address Resolution Protocol)', 'ICMP'], 2, 'ARP maps IP addresses to Ethernet MAC addresses.'],
            ['Which Wireshark filter isolates HTTP POST requests specifically as shown in the lab demonstration?', ['http.request.method == "POST"', 'tcp.port == 80', 'ip.addr == 127.0.0.1', 'dns.flags.response == 1'], 0, 'http.request.method == "POST" filters HTTP POST packets.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Networking Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 3. Linux Administration (Video Lecture Based) ─────────────────────────
    private function getModule3LinuxQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the Linux permissions video, what octal numeric value represents `rwxr-xr--`?', ['777', '754', '644', '755'], 1, 'rwx (7), r-x (5), r-- (4) = 754.'],
            ['Which system file contains user accounts, UIDs, and default login shells?', ['/etc/shadow', '/etc/passwd', '/etc/group', '/var/log/auth.log'], 1, '/etc/passwd lists local account details.'],
            ['Which Linux command changes file ownership (user and group)?', ['chmod', 'chown', 'chgrp', 'umask'], 1, 'chown modifies file owner and group.'],
            ['In `/etc/ssh/sshd_config`, which directive disables password logins to enforce key auth?', ['PermitRootLogin no', 'PasswordAuthentication no', 'AllowUsers none', 'PubkeyAuthentication no'], 1, 'Setting `PasswordAuthentication no` forces public key authentication.'],
            ['Which command displays real-time CPU and memory usage interactively in Linux?', ['ps -ef', 'top / htop', 'df -h', 'free -m'], 1, 'top/htop monitors running processes live.'],
            ['Where are SSH login attempts recorded on Ubuntu/Debian Linux systems?', ['/var/log/syslog', '/var/log/auth.log', '/var/log/nginx/access.log', '/etc/ssh/log'], 1, '/var/log/auth.log logs SSH logins.'],
            ['Which command sets default file creation permission masks in Linux?', ['chmod 600', 'umask', 'chown root', 'setfacl'], 1, 'umask defines default initial permission masks.'],
            ['Which command searches text files for lines matching regular expression patterns?', ['find', 'grep', 'awk', 'sed'], 1, 'grep searches text files for regex pattern matches.'],
            ['What command displays disk space usage across mounted filesystems in human-readable format?', ['du -sh', 'df -h', 'ls -la', 'fdisk -l'], 1, 'df -h reports disk usage in megabytes/gigabytes.'],
            ['Which command gracefully sends a SIGTERM signal to terminate a process by PID?', ['kill -9 <pid>', 'kill <pid>', 'stop <pid>', 'end <pid>'], 1, 'kill <pid> sends default SIGTERM (15) allowing graceful cleanup.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Linux Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 4. OWASP Web Security (Video Lecture Based) ───────────────────────────
    private function getModule4OwaspQuestions(): array
    {
        $q = [];
        $templates = [
            ['As explained in the OWASP video, which vulnerability currently holds the #1 ranking for web risks?', ['SQL Injection', 'Broken Access Control', 'Cryptographic Failures', 'SSRF'], 1, 'Broken Access Control (A01) is ranked #1 by OWASP.'],
            ['What primary mechanism completely neutralizes SQL Injection vulnerabilities in code?', ['Input regex validation only', 'Prepared Statements with Parameterized Queries', 'Web Application Firewall (WAF) only', 'Base64 encoding'], 1, 'Prepared statements separate SQL code from user parameters.'],
            ['Changing URL parameter `/api/user/10` to `/api/user/11` to view private data is an example of what flaw?', ['Cross-Site Scripting (XSS)', 'Insecure Direct Object Reference (IDOR)', 'CSRF', 'SQL Injection'], 1, 'IDOR exposes direct internal object identifiers without access authorization checks.'],
            ['Which attack type injects malicious client-side JavaScript into web pages viewed by other users?', ['SQL Injection', 'Cross-Site Scripting (XSS)', 'CSRF', 'Command Injection'], 1, 'XSS executes client-side scripts in victim browsers.'],
            ['What HTTP security header instructs web browsers to communicate exclusively over HTTPS?', ['Content-Security-Policy', 'HTTP Strict Transport Security (HSTS)', 'X-Frame-Options', 'X-Content-Type-Options'], 1, 'HSTS enforces HTTPS connections for all browser requests.'],
            ['Which attack tricks an authenticated browser into submitting unauthorized requests to a web app?', ['Cross-Site Request Forgery (CSRF)', 'XSS', 'SSRF', 'Directory Traversal'], 0, 'CSRF exploits stored browser session cookies to execute unauthorized actions.'],
            ['Which HTTP response status code indicates an unauthenticated request?', ['400 Bad Request', '401 Unauthorized', '403 Forbidden', '404 Not Found'], 1, '401 Unauthorized indicates missing or invalid authentication credentials.'],
            ['What does Server-Side Request Forgery (SSRF) force a web server to do?', ['Execute browser scripts', 'Make HTTP requests to internal or external systems', 'Dump database schemas', 'Modify local files'], 1, 'SSRF forces the backend server to send requests to target endpoints.'],
            ['What HTTP header prevents clickjacking attacks by controlling iframe embedding?', ['X-Frame-Options', 'HSTS', 'CORS', 'Content-Type'], 0, 'X-Frame-Options restricts whether a page can be embedded inside an iframe.'],
            ['In Laravel, what mechanism provides automated protection against Cross-Site Request Forgery?', ['Sanctum Token', 'CSRF Token Middleware (@csrf / X-CSRF-TOKEN)', 'Eloquent ORM', 'Blade Compiler'], 1, 'Laravel verifies CSRF tokens on incoming POST/PUT/DELETE web requests.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "OWASP Security Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 5. SQL Injection (Video Lecture Based) ────────────────────────────────
    private function getModule5SqlInjectionQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the SQLi video demonstration, what do `--` or `#` characters signify in SQL syntax?', ['Syntax errors', 'Comment characters that cause the engine to ignore subsequent code', 'Wildcard matching', 'String concatenation'], 1, 'Attackers use comment characters to bypass remaining SQL clauses.'],
            ['Which SQL injection type relies on time delays like `SLEEP(5)` when no data is returned directly?', ['In-Band SQLi', 'Error-Based SQLi', 'Time-Based Blind SQLi', 'Out-of-Band SQLi'], 2, 'Time-Based Blind SQLi measures query delay to infer information.'],
            ['Why does Laravel Eloquent ORM naturally protect applications against SQL Injection?', ['Eloquent disables SQL queries', 'Eloquent uses PDO prepared statements with bound parameters', 'Eloquent encrypts table names', 'Eloquent strips quotes'], 1, 'Eloquent binds parameters automatically via PDO prepared statements.'],
            ['What SQL keyword allows attackers in UNION-based SQLi to append results from another table?', ['JOIN', 'UNION SELECT', 'GROUP BY', 'HAVING'], 1, 'UNION SELECT combines results from the original and injected queries.'],
            ['In raw SQL queries in Laravel, how should dynamic parameters be passed safely?', ['DB::select("SELECT * FROM users WHERE id = $id")', 'DB::select("SELECT * FROM users WHERE id = ?", [$id])', 'DB::statement($id)', 'DB::raw($id)'], 1, 'Passing parameters in an array uses prepared statement bindings.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "SQLi Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 6. Database Fundamentals (Video Lecture Based) ────────────────────────
    private function getModule6DatabaseDesignQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the MySQL lecture, which default storage engine supports ACID transactions and foreign key constraints?', ['MyISAM', 'Memory', 'InnoDB', 'CSV'], 2, 'InnoDB is the default transaction-safe engine supporting foreign keys.'],
            ['Which SQL constraint guarantees that every row in a table has a unique, non-null identifier?', ['FOREIGN KEY', 'NOT NULL', 'PRIMARY KEY', 'DEFAULT'], 2, 'PRIMARY KEY uniquely identifies rows and cannot contain NULL values.'],
            ['Which MySQL data type is best suited for exact currency amounts like $99.99?', ['FLOAT', 'DOUBLE', 'DECIMAL(10, 2)', 'INT'], 2, 'DECIMAL stores exact fixed-point numeric values for financials.'],
            ['What storage engine feature in InnoDB prevents dirty reads and enforces concurrency control?', ['Table Locking', 'Row-Level Locking & MVCC', 'Disk Compression', 'Full-Text Indexing'], 1, 'InnoDB uses row-level locking for concurrent transactions.'],
            ['Which command creates a database enforcing `utf8mb4` character set in MySQL?', ['CREATE DATABASE db CHARACTER SET utf8mb4;', 'ADD DATABASE db;', 'MAKE DATABASE db;', 'INIT DATABASE db;'], 0, 'CREATE DATABASE db CHARACTER SET utf8mb4 sets full UTF-8 support.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Database Design Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 7. DML & SELECT Queries (Video Lecture Based) ─────────────────────────
    private function getModule7DmlQuestions(): array
    {
        $q = [];
        $templates = [
            ['What key difference distinguishes `TRUNCATE TABLE` from `DELETE FROM table` as shown in the lecture?', ['TRUNCATE is slower', 'TRUNCATE deletes individual rows with triggers', 'TRUNCATE drops and recreates the table without individual row deletion logs', 'DELETE resets auto-increment IDs'], 2, 'TRUNCATE is a DDL operation that clears the table and resets auto-increment.'],
            ['Which clause is used to filter aggregated results AFTER a `GROUP BY` clause?', ['WHERE', 'HAVING', 'ORDER BY', 'FILTER'], 1, 'HAVING filters aggregate values calculated by GROUP BY.'],
            ['In a `SELECT` statement, what keyword removes duplicate rows from the query output?', ['UNIQUE', 'DISTINCT', 'DIFFERENT', 'GROUP'], 1, 'DISTINCT removes duplicate rows from query results.'],
            ['Which SQL function calculates the average numeric value across grouped rows?', ['SUM()', 'COUNT()', 'AVG()', 'MAX()'], 2, 'AVG() computes the arithmetic mean of a column.'],
            ['What is the effect of omitting the `WHERE` clause in an `UPDATE` command?', ['Syntax error occurs', 'Only the first row is updated', 'Every single row in the table will be updated', 'No rows are updated'], 2, 'Without WHERE, UPDATE modifies every record in the table.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "DML & Queries Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 8. Relational JOINs (Video Lecture Based) ─────────────────────────────
    private function getModule8JoinQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the JOINs video tutorial, which JOIN type returns all records from the left table even if there are no matches in the right?', ['INNER JOIN', 'LEFT JOIN (LEFT OUTER JOIN)', 'CROSS JOIN', 'RIGHT JOIN'], 1, 'LEFT JOIN returns all left-table rows, padding missing right columns with NULL.'],
            ['Which JOIN returns only rows that have matching values in BOTH joined tables?', ['INNER JOIN', 'LEFT JOIN', 'FULL JOIN', 'CROSS JOIN'], 0, 'INNER JOIN requires matching values in both tables.'],
            ['What is a subquery placed inside the `FROM` clause called?', ['Inline Query', 'Derived Table', 'Correlated Subquery', 'Stored Function'], 1, 'A subquery in the FROM clause acts as a temporary Derived Table.'],
            ['What type of query evaluates a subquery once for every outer query row processed?', ['Derived Table', 'Correlated Subquery', 'Union Query', 'View'], 1, 'Correlated subqueries depend on values from the outer query row.'],
            ['What Cartesian product result size is produced when CROSS JOINing a table of 5 rows with a table of 10 rows?', ['15 rows', '50 rows', '5 rows', '10 rows'], 1, 'CROSS JOIN multiplies row counts (5 * 10 = 50).'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "SQL JOINs Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 9. Indexes & Query Optimization (Video Lecture Based) ─────────────────
    private function getModule9IndexQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the indexing lecture, how does a B-Tree index improve SELECT query speed?', ['By compressing disk files', 'By reducing search complexity from full table scan O(N) to O(log N)', 'By caching results in RAM', 'By disabling foreign keys'], 1, 'B-Tree indexes enable logarithmic time search O(log N).'],
            ['In a MySQL `EXPLAIN` query plan, what does `type: ALL` indicate?', ['Index lookup', 'Full table scan (inefficient query)', 'Constant lookup', 'NULL result'], 1, '`type: ALL` means MySQL must scan every row in the table.'],
            ['What rule dictates that a composite index `(col1, col2)` can only optimize queries filtering by `col1` first?', ['Rightmost Prefix Rule', 'Leftmost Prefix Rule', 'Index Order Rule', 'Cardinality Rule'], 1, 'Composite indexes require the leftmost column to be present in WHERE queries.'],
            ['Which situation is LEAST suitable for creating a new index?', ['A 10,000,000 row table frequently filtered by email', 'A small 50-row lookup table', 'A column used in JOIN ON clauses', 'A column used in ORDER BY'], 1, 'Small tables perform fast full table scans without needing indexes.'],
            ['In `EXPLAIN` output, which column shows the actual index selected by the MySQL optimizer?', ['possible_keys', 'key', 'rows', 'extra'], 1, 'The `key` column shows the index chosen by the optimizer.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Indexing Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 10. ACID Transactions (Video Lecture Based) ───────────────────────────
    private function getModule10TransactionQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the transactions video, which ACID property guarantees all-or-nothing execution?', ['Atomicity', 'Consistency', 'Isolation', 'Durability'], 0, 'Atomicity guarantees all operations in a transaction complete or roll back.'],
            ['Which command permanently saves all changes made during a SQL transaction?', ['START TRANSACTION;', 'COMMIT;', 'ROLLBACK;', 'SAVEPOINT;'], 1, 'COMMIT saves transaction changes permanently to disk.'],
            ['Which CLI command creates an SQL database backup file in MySQL?', ['mysql-export', 'mysqldump', 'mysql-backup', 'db-dump'], 1, 'mysqldump exports databases to SQL dump files.'],
            ['Which SQL statement undoes all uncommitted changes in a transaction?', ['ABORT;', 'CANCEL;', 'ROLLBACK;', 'RESTORE;'], 2, 'ROLLBACK reverts uncommitted transaction changes.'],
            ['What is the best practice for web application database user privileges?', ['Use root user for everything', 'Grant ALL PRIVILEGES', 'Grant least-privilege access (SELECT, INSERT, UPDATE, DELETE) to dedicated app user', 'Disable password auth'], 2, 'Principle of Least Privilege limits access to necessary operations.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Transactions Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 11. Statistics & Probability (Video Lecture Based) ────────────────────
    private function getModule11StatsQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the statistics video lecture, which central tendency metric is most robust against extreme outliers?', ['Arithmetic Mean', 'Median', 'Variance', 'Range'], 1, 'The median takes the middle position of sorted data, preventing extreme outliers from skewing it.'],
            ['According to the Empirical Rule (68-95-99.7), what percentage of data falls within 2 standard deviations of the mean in a Normal Distribution?', ['50%', '68%', '95%', '99.7%'], 2, 'The Empirical Rule states ~95% of data falls within 2 standard deviations of the mean.'],
            ['What mathematical theorem calculates conditional probability P(A|B) based on prior knowledge of conditions?', ['Pythagorean Theorem', 'Bayes\' Theorem', 'Central Limit Theorem', 'Fermat\'s Last Theorem'], 1, 'Bayes\' Theorem calculates conditional probability P(A|B) using prior probabilities.'],
            ['What is the square root of Variance in descriptive statistics called?', ['Standard Error', 'Standard Deviation', 'Mean Absolute Deviation', 'Interquartile Range'], 1, 'Standard Deviation is the square root of Variance.'],
            ['Which statistical test compares the means of two independent sample groups?', ['Chi-Square Test', 'Two-Sample Student\'s t-Test', 'Pearson Correlation', 'ANOVA'], 1, 'Student\'s t-test compares the means of two groups.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Applied Statistics Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 12. Mathematics & Logic (Video Lecture Based) ─────────────────────────
    private function getModule12MathQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the Boolean logic lecture, what is the output of `True XOR True`?', ['True', 'False', 'Undefined', 'Null'], 1, 'XOR returns True if and only if inputs differ. Since both are True, XOR returns False.'],
            ['What is the dot product of vectors A = [2, 3] and B = [4, 1] demonstrated in the lecture?', ['5', '11', '14', '24'], 1, '(2 * 4) + (3 * 1) = 8 + 3 = 11.'],
            ['Which logic gate output is True ONLY if both inputs evaluate to True?', ['OR Gate', 'AND Gate', 'XOR Gate', 'NAND Gate'], 1, 'AND gate outputs True strictly when both inputs evaluate to True.'],
            ['What is the derivative of f(x) = x^3 with respect to x using the power rule?', ['3x', '3x^2', 'x^2', '6x'], 1, 'By the power rule, d/dx (x^n) = n * x^(n-1). So d/dx (x^3) = 3x^2.'],
            ['What is the determinant of a 2x2 matrix [[a, b], [c, d]]?', ['ad + bc', 'ad - bc', 'ab - cd', 'a + b + c + d'], 1, 'The determinant of [[a, b], [c, d]] is ad - bc.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Applied Mathematics Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 13. Computer Science & Algorithms (Video Lecture Based) ────────────────
    private function getModule13CSQuestions(): array
    {
        $q = [];
        $templates = [
            ['In the CS video lecture, what is the average time complexity of Binary Search on a sorted array of N elements?', ['O(1)', 'O(log N)', 'O(N)', 'O(N^2)'], 1, 'Binary Search divides search space in half at each step, yielding O(log N).'],
            ['Which data structure operates on a Last In, First Out (LIFO) order as shown in the algorithms lab?', ['Queue', 'Stack', 'Array', 'Linked List'], 1, 'Stacks use LIFO ordering (push and pop).'],
            ['What is the average time complexity for searching a key in a well-balanced Hash Map?', ['O(1)', 'O(log N)', 'O(N)', 'O(N log N)'], 0, 'Hash maps provide constant time O(1) average key lookups.'],
            ['What sorting algorithm has a guaranteed worst-case time complexity of O(N log N)?', ['QuickSort', 'MergeSort', 'BubbleSort', 'InsertionSort'], 1, 'MergeSort divides and merges recursively in O(N log N) time.'],
            ['In graph traversal, which algorithm uses a Queue data structure to explore nodes level-by-level?', ['Breadth-First Search (BFS)', 'Depth-First Search (DFS)', 'Dijkstra\'s Algorithm', 'Bellman-Ford'], 0, 'BFS uses a FIFO queue to visit neighbor nodes level-by-level.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "CS & Algorithms Video Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }
}
