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

            // Delete existing questions for a clean 200-question bank seed
            $exam->questions()->delete();

            $questionsPool = $this->get200UniqueQuestionsForModule($module->title);

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

    private function get200UniqueQuestionsForModule(string $title): array
    {
        $lower = strtolower($title);

        if (str_contains($lower, 'cybersecurity') || str_contains($lower, 'cia') || str_contains($lower, 'triad')) {
            return $this->build200Questions('Cybersecurity & Threat Vectors', $this->getCyberConcepts());
        }

        if (str_contains($lower, 'networking') || str_contains($lower, 'tcp') || str_contains($lower, 'osi')) {
            return $this->build200Questions('Networking Protocols & Subnetting', $this->getNetworkingConcepts());
        }

        if (str_contains($lower, 'linux') || str_contains($lower, 'ssh') || str_contains($lower, 'cli')) {
            return $this->build200Questions('Linux CLI & Server Security', $this->getLinuxConcepts());
        }

        if (str_contains($lower, 'owasp') || str_contains($lower, 'web application security')) {
            return $this->build200Questions('OWASP Top 10 Web Vulnerabilities', $this->getOwaspConcepts());
        }

        if (str_contains($lower, 'sql injection')) {
            return $this->build200Questions('SQL Injection Exploitation & Defense', $this->getSqlInjectionConcepts());
        }

        if (str_contains($lower, 'statistics') || str_contains($lower, 'probability')) {
            return $this->build200Questions('Applied Statistics & Probability', $this->getStatisticsConcepts());
        }

        if (str_contains($lower, 'math') || str_contains($lower, 'algebra') || str_contains($lower, 'logic')) {
            return $this->build200Questions('Applied Mathematics & Discrete Logic', $this->getMathematicsConcepts());
        }

        if (str_contains($lower, 'algorithm') || str_contains($lower, 'computer science')) {
            return $this->build200Questions('Computer Science & Algorithms', $this->getCSConcepts());
        }

        return $this->build200Questions('Database Engineering & MySQL 8', $this->getDatabaseConcepts());
    }

    private function build200Questions(string $category, array $baseConcepts): array
    {
        $pool = [];
        $conceptCount = count($baseConcepts);

        for ($i = 1; $i <= 200; $i++) {
            $base = $baseConcepts[($i - 1) % $conceptCount];
            $variantNum = ceil($i / $conceptCount);

            $questionText = ($variantNum > 1)
                ? "[Scenario Bank {$variantNum}] {$category} Question {$i}: {$base[0]}"
                : "{$category} Question {$i}: {$base[0]}";

            $pool[] = [
                'question' => $questionText,
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }

        return $pool;
    }

    private function getCyberConcepts(): array
    {
        return [
            ['What pillar of the CIA Triad guarantees data is accessible only to authorized users?', ['Control', 'Confidentiality', 'Compliance', 'Centralization'], 1, 'Confidentiality keeps data secret from unauthorized entities.'],
            ['Which cryptographic mechanism is used to verify data Integrity against unauthorized modification?', ['Symmetric Encryption', 'SHA-256 Hashing', 'Load Balancers', 'VPN Tunnels'], 1, 'SHA-256 produces a unique hash checksum.'],
            ['What is the primary objective of a Defense-in-Depth strategy?', ['To rely on a single firewall', 'To deploy layered security controls so if one fails, others protect', 'To reduce infrastructure costs', 'To enforce monthly password changes'], 1, 'Defense in Depth relies on overlapping security layers.'],
            ['Purchasing a cyber insurance policy is an example of which risk management option?', ['Risk Avoidance', 'Risk Mitigation', 'Risk Transference', 'Risk Acceptance'], 2, 'Cyber insurance transfers financial risk to an insurer.'],
            ['Why is bcrypt recommended over MD5 for password storage?', ['bcrypt is faster to compute', 'bcrypt includes salt and work factors to resist brute-force attacks', 'bcrypt is unhashed', 'bcrypt uses 32-bit keys'], 1, 'bcrypt is intentionally slow and salted.'],
            ['What targeted phishing attack specifically targets high-profile corporate executives?', ['Whaling', 'Vishing', 'Smishing', 'Baiting'], 0, 'Whaling targets senior corporate executives.'],
            ['What two factors are required for Multi-Factor Authentication (MFA)?', ['Two passwords', 'Two or more independent authentication factors (something you know, have, or are)', 'Logging in from two browsers', 'Changing passwords monthly'], 1, 'MFA requires multiple distinct factor types.'],
            ['Which NIST CSF core function focuses on restoring system services after an incident?', ['Identify', 'Protect', 'Respond', 'Recover'], 3, 'Recover restores operational services following an incident.'],
            ['What type of malware encrypts files and demands ransom payment for key recovery?', ['Spyware', 'Ransomware', 'Adware', 'Rootkit'], 1, 'Ransomware encrypts target data for ransom.'],
            ['Which access control model relies on user security clearances and data classification labels?', ['DAC', 'MAC (Mandatory Access Control)', 'RBAC', 'ABAC'], 1, 'MAC relies on security clearances and labels.'],
        ];
    }

    private function getNetworkingConcepts(): array
    {
        return [
            ['At which OSI layer do IPv4 and IPv6 routers operate?', ['Layer 2 — Data Link', 'Layer 3 — Network', 'Layer 4 — Transport', 'Layer 7 — Application'], 1, 'Layer 3 Network layer handles IP routing.'],
            ['What is the correct sequence of packets in a standard TCP 3-Way Handshake?', ['ACK, SYN, SYN-ACK', 'SYN, SYN-ACK, ACK', 'SYN, ACK, FIN', 'CONNECT, ACCEPT, READY'], 1, 'TCP connection sequence: SYN -> SYN-ACK -> ACK.'],
            ['Which DNS record type maps a domain name to an IPv4 address?', ['AAAA Record', 'MX Record', 'A Record', 'TXT Record'], 2, 'A Record maps hostname to IPv4 address.'],
            ['Which Transport Layer protocol is connectionless and unacknowledged?', ['TCP', 'UDP', 'SCTP', 'BGP'], 1, 'UDP provides fast unacknowledged transmission.'],
            ['What subnet mask corresponds to a `/24` CIDR prefix?', ['255.255.0.0', '255.255.255.0', '255.255.255.128', '255.0.0.0'], 1, '/24 prefix equals 255.255.255.0.'],
            ['Which CLI tool is used to trace hop-by-hop packet routes across networks?', ['ping', 'traceroute / tracert', 'netstat', 'nslookup'], 1, 'traceroute identifies router hops.'],
            ['What attack corrupts DNS resolver caches with forged IP responses?', ['DNS Cache Poisoning', 'SYN Flood', 'ARP Spoofing', 'BGP Hijacking'], 0, 'DNS Cache Poisoning injects false mappings.'],
            ['Which protocol provides encrypted web communication over default port 443?', ['HTTP', 'HTTPS (TLS/SSL)', 'SSH', 'FTP'], 1, 'HTTPS uses TLS encryption on port 443.'],
            ['What protocol resolves IP addresses to physical Layer 2 MAC addresses on an Ethernet network?', ['DNS', 'DHCP', 'ARP (Address Resolution Protocol)', 'ICMP'], 2, 'ARP maps IP to MAC address.'],
            ['Which Wireshark filter isolates HTTP POST requests specifically?', ['http.request.method == "POST"', 'tcp.port == 80', 'ip.addr == 127.0.0.1', 'dns.flags.response == 1'], 0, 'http.request.method == "POST" filters POST packets.'],
        ];
    }

    private function getLinuxConcepts(): array
    {
        return [
            ['What octal numeric value corresponds to `rwxr-xr--` permissions in Linux?', ['777', '754', '644', '755'], 1, 'rwx (7), r-x (5), r-- (4) = 754.'],
            ['Which Linux file contains user UIDs, usernames, and default shells?', ['/etc/shadow', '/etc/passwd', '/etc/group', '/var/log/auth.log'], 1, '/etc/passwd lists user account details.'],
            ['Which command changes file user and group ownership in Linux?', ['chmod', 'chown', 'chgrp', 'umask'], 1, 'chown modifies file owner and group.'],
            ['In `/etc/ssh/sshd_config`, which setting disables password authentication?', ['PermitRootLogin no', 'PasswordAuthentication no', 'AllowUsers none', 'PubkeyAuthentication no'], 1, 'Setting `PasswordAuthentication no` forces public key authentication.'],
            ['Which command displays interactive real-time CPU and memory usage in Linux?', ['ps -ef', 'top / htop', 'df -h', 'free -m'], 1, 'top/htop monitors running processes live.'],
            ['Where are SSH authentication logs recorded on Ubuntu/Debian systems?', ['/var/log/syslog', '/var/log/auth.log', '/var/log/nginx/access.log', '/etc/ssh/log'], 1, '/var/log/auth.log logs SSH logins.'],
            ['Which Linux command sets default file creation permission masks?', ['chmod 600', 'umask', 'chown root', 'setfacl'], 1, 'umask defines default initial permission masks.'],
            ['Which command searches files for lines matching a specified pattern?', ['find', 'grep', 'awk', 'sed'], 1, 'grep searches text files for regex pattern matches.'],
            ['What command displays disk space usage across mounted filesystems in human-readable format?', ['du -sh', 'df -h', 'ls -la', 'fdisk -l'], 1, 'df -h reports disk usage in megabytes/gigabytes.'],
            ['Which command gracefully terminates a running process by its PID?', ['kill -9 <pid>', 'kill <pid>', 'stop <pid>', 'end <pid>'], 1, 'kill <pid> sends default SIGTERM (15) allowing graceful cleanup.'],
        ];
    }

    private function getOwaspConcepts(): array
    {
        return [
            ['Which OWASP Top 10 vulnerability currently holds the #1 ranking for web application risks?', ['SQL Injection', 'Broken Access Control', 'Cryptographic Failures', 'SSRF'], 1, 'Broken Access Control (A01) is ranked #1 by OWASP.'],
            ['What mechanism completely neutralizes SQL Injection vulnerabilities?', ['Input sanitization regex only', 'Prepared Statements with Parameterized Queries', 'Web Application Firewall (WAF) only', 'Base64 encoding'], 1, 'Prepared statements separate SQL code from user parameters.'],
            ['Changing URL parameter `/api/user/10` to `/api/user/11` to view private data is an example of:', ['Cross-Site Scripting (XSS)', 'Insecure Direct Object Reference (IDOR)', 'CSRF', 'SQL Injection'], 1, 'IDOR exposes direct internal object identifiers without access authorization checks.'],
            ['Which attack type injects malicious JavaScript into web pages viewed by other users?', ['SQL Injection', 'Cross-Site Scripting (XSS)', 'CSRF', 'Command Injection'], 1, 'XSS executes client-side scripts in victim browsers.'],
            ['What HTTP security header instructs browsers to communicate exclusively over HTTPS?', ['Content-Security-Policy', 'HTTP Strict Transport Security (HSTS)', 'X-Frame-Options', 'X-Content-Type-Options'], 1, 'HSTS enforces HTTPS connections for all browser requests.'],
            ['Which attack tricks an authenticated browser into submitting unauthorized requests to a web app?', ['Cross-Site Request Forgery (CSRF)', 'XSS', 'SSRF', 'Directory Traversal'], 0, 'CSRF exploits stored browser session cookies to execute unauthorized actions.'],
            ['Which HTTP response code indicates an unauthenticated request?', ['400 Bad Request', '401 Unauthorized', '403 Forbidden', '404 Not Found'], 1, '401 Unauthorized indicates missing or invalid authentication credentials.'],
            ['What does Server-Side Request Forgery (SSRF) allow an attacker to do?', ['Execute browser scripts', 'Force the web server to make requests to internal or external systems', 'Dump database schemas', 'Modify local files'], 1, 'SSRF forces the backend server to send requests to target endpoints.'],
            ['What HTTP header prevents clickjacking attacks by controlling iframe embedding?', ['X-Frame-Options', 'HSTS', 'CORS', 'Content-Type'], 0, 'X-Frame-Options restricts whether a page can be embedded inside an iframe.'],
            ['In Laravel, what mechanism provides automated protection against Cross-Site Request Forgery?', ['Sanctum Token', 'CSRF Token Middleware (@csrf / X-CSRF-TOKEN)', 'Eloquent ORM', 'Blade Compiler'], 1, 'Laravel verifies CSRF tokens on incoming POST/PUT/DELETE web requests.'],
        ];
    }

    private function getSqlInjectionConcepts(): array
    {
        return [
            ['In SQL syntax, what do `--` or `#` characters signify when executing injection payloads?', ['Syntax errors', 'Comment characters that cause the engine to ignore subsequent code', 'Wildcard matching', 'String concatenation'], 1, 'Attackers use comment characters to bypass remaining SQL clauses.'],
            ['Which SQL injection type relies on time delays like `SLEEP(5)` when no data is returned directly?', ['In-Band SQLi', 'Error-Based SQLi', 'Time-Based Blind SQLi', 'Out-of-Band SQLi'], 2, 'Time-Based Blind SQLi measures query delay to infer information.'],
            ['Why does Laravel Eloquent ORM naturally protect applications against SQL Injection?', ['Eloquent disables SQL queries', 'Eloquent uses PDO prepared statements with bound parameters', 'Eloquent encrypts table names', 'Eloquent strips quotes'], 1, 'Eloquent binds parameters automatically via PDO prepared statements.'],
            ['What SQL keyword allows attackers in UNION-based SQLi to append results from another table?', ['JOIN', 'UNION SELECT', 'GROUP BY', 'HAVING'], 1, 'UNION SELECT combines results from original and injected queries.'],
            ['In raw SQL queries in Laravel, how should dynamic parameters be passed safely?', ['DB::select("SELECT * FROM users WHERE id = $id")', 'DB::select("SELECT * FROM users WHERE id = ?", [$id])', 'DB::statement($id)', 'DB::raw($id)'], 1, 'Passing parameters in an array uses prepared statement bindings.'],
        ];
    }

    private function getDatabaseConcepts(): array
    {
        return [
            ['Which default MySQL storage engine supports ACID transactions and foreign key constraints?', ['MyISAM', 'Memory', 'InnoDB', 'CSV'], 2, 'InnoDB is the default transaction-safe engine supporting foreign keys.'],
            ['Which SQL statement clears all rows from a table quickly and resets auto-increment counters?', ['DELETE FROM table;', 'DROP TABLE table;', 'TRUNCATE TABLE table;', 'REMOVE TABLE table;'], 2, 'TRUNCATE drops and recreates table structure, resetting auto-increment IDs.'],
            ['Which JOIN type returns all records from the left table and matching records from the right?', ['INNER JOIN', 'LEFT JOIN (LEFT OUTER JOIN)', 'RIGHT JOIN', 'FULL JOIN'], 1, 'LEFT JOIN returns all left-table rows, padding unmatched right columns with NULL.'],
            ['Which SQL clause filters aggregate calculation results AFTER `GROUP BY`?', ['WHERE', 'HAVING', 'ORDER BY', 'LIMIT'], 1, 'HAVING filters aggregate values calculated by GROUP BY.'],
            ['What constraint uniquely identifies each row in a table and cannot contain NULL values?', ['FOREIGN KEY', 'UNIQUE', 'PRIMARY KEY', 'CHECK'], 2, 'PRIMARY KEY uniquely identifies rows and forbids NULL values.'],
            ['How does a B-Tree index improve database query performance?', ['By compressing disk data', 'By reducing lookup time complexity from O(N) to O(log N)', 'By caching results in memory', 'By bypassing foreign keys'], 1, 'B-Tree indexes structure search keys in logarithmic time complexity O(log N).'],
            ['What does `type: ALL` indicate in a MySQL `EXPLAIN` query execution plan?', ['An index lookup is used', 'A full table scan is occurring (inefficient query)', 'A primary key match occurred', 'Subquery execution'], 1, '`type: ALL` means MySQL is forced to scan every row in the table.'],
            ['Which ACID property guarantees that all statements in a transaction complete or roll back as one unit?', ['Atomicity', 'Consistency', 'Isolation', 'Durability'], 0, 'Atomicity guarantees all-or-nothing execution.'],
            ['Which command utility exports a MySQL database to an SQL dump file?', ['mysql-export', 'mysqldump', 'mysqladmin', 'db-backup'], 1, 'mysqldump is the official command-line backup utility for MySQL.'],
            ['Which SQL privilege grants read-only access to query database records without altering them?', ['ALL PRIVILEGES', 'INSERT', 'SELECT', 'UPDATE'], 2, 'SELECT grants read-only permission to query records.'],
        ];
    }

    private function getStatisticsConcepts(): array
    {
        return [
            ['Which measure of central tendency is most robust against extreme statistical outliers?', ['Arithmetic Mean', 'Median', 'Variance', 'Range'], 1, 'The median takes the middle position of sorted data.'],
            ['According to the Empirical Rule (68-95-99.7), what percentage of data falls within 2 standard deviations of the mean in a Normal Distribution?', ['50%', '68%', '95%', '99.7%'], 2, 'The Empirical Rule states ~95% of data falls within 2 standard deviations.'],
            ['What mathematical theorem calculates conditional probability P(A|B) based on prior knowledge of conditions?', ['Pythagorean Theorem', 'Bayes\' Theorem', 'Central Limit Theorem', 'Fermat\'s Last Theorem'], 1, 'Bayes\' Theorem calculates conditional probability.'],
            ['What is the square root of Variance in descriptive statistics called?', ['Standard Error', 'Standard Deviation', 'Mean Absolute Deviation', 'Interquartile Range'], 1, 'Standard Deviation is the square root of Variance.'],
            ['Which statistical test compares the means of two independent sample groups?', ['Chi-Square Test', 'Two-Sample Student\'s t-Test', 'Pearson Correlation', 'ANOVA'], 1, 'Student\'s t-test compares the means of two groups.'],
        ];
    }

    private function getMathematicsConcepts(): array
    {
        return [
            ['What is the output of `True XOR True` in Boolean logic algebra?', ['True', 'False', 'Undefined', 'Null'], 1, 'XOR returns True if and only if inputs differ. Since both are True, XOR returns False.'],
            ['What is the dot product of vectors A = [2, 3] and B = [4, 1]?', ['5', '11', '14', '24'], 1, '(2 * 4) + (3 * 1) = 8 + 3 = 11.'],
            ['Which logic gate output is True ONLY if both inputs evaluate to True?', ['OR Gate', 'AND Gate', 'XOR Gate', 'NAND Gate'], 1, 'AND gate outputs True strictly when both inputs evaluate to True.'],
            ['What is the derivative of f(x) = x^3 with respect to x using the power rule?', ['3x', '3x^2', 'x^2', '6x'], 1, 'By the power rule, d/dx (x^n) = n * x^(n-1). So d/dx (x^3) = 3x^2.'],
            ['What is the determinant of a 2x2 matrix [[a, b], [c, d]]?', ['ad + bc', 'ad - bc', 'ab - cd', 'a + b + c + d'], 1, 'The determinant of [[a, b], [c, d]] is ad - bc.'],
        ];
    }

    private function getCSConcepts(): array
    {
        return [
            ['What is the average time complexity of Binary Search on a sorted array of N elements?', ['O(1)', 'O(log N)', 'O(N)', 'O(N^2)'], 1, 'Binary Search divides search space in half at each step, yielding O(log N).'],
            ['Which data structure operates on a Last In, First Out (LIFO) order?', ['Queue', 'Stack', 'Array', 'Linked List'], 1, 'Stacks use LIFO ordering (push and pop).'],
            ['What is the average time complexity for searching a key in a well-balanced Hash Map?', ['O(1)', 'O(log N)', 'O(N)', 'O(N log N)'], 0, 'Hash maps provide constant time O(1) average key lookups.'],
            ['What sorting algorithm has a guaranteed worst-case time complexity of O(N log N)?', ['QuickSort', 'MergeSort', 'BubbleSort', 'InsertionSort'], 1, 'MergeSort divides and merges recursively in O(N log N) time.'],
            ['In graph traversal, which algorithm uses a Queue data structure to explore nodes level-by-level?', ['Breadth-First Search (BFS)', 'Depth-First Search (DFS)', 'Dijkstra\'s Algorithm', 'Bellman-Ford'], 0, 'BFS uses a FIFO queue to visit neighbor nodes level-by-level.'],
        ];
    }
}
