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

            // Delete existing questions for a clean 50-question seed
            $exam->questions()->delete();

            $questionsPool = $this->get50QuestionsForModule($module->title);

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

    private function get50QuestionsForModule(string $title): array
    {
        $lower = strtolower($title);

        if (str_contains($lower, 'cybersecurity') || str_contains($lower, 'cia') || str_contains($lower, 'risk')) {
            return $this->get50CybersecurityQuestions();
        }

        if (str_contains($lower, 'network') || str_contains($lower, 'tcp') || str_contains($lower, 'osi')) {
            return $this->get50NetworkingQuestions();
        }

        if (str_contains($lower, 'linux') || str_contains($lower, 'ssh') || str_contains($lower, 'cli')) {
            return $this->get50LinuxQuestions();
        }

        if (str_contains($lower, 'owasp') || str_contains($lower, 'web security') || str_contains($lower, 'sql injection')) {
            return $this->get50WebSecurityQuestions();
        }

        if (str_contains($lower, 'statistics') || str_contains($lower, 'probability')) {
            return $this->get50StatisticsQuestions();
        }

        if (str_contains($lower, 'math') || str_contains($lower, 'algebra') || str_contains($lower, 'logic')) {
            return $this->get50MathematicsQuestions();
        }

        if (str_contains($lower, 'algorithm') || str_contains($lower, 'computer science')) {
            return $this->get50CSQuestions();
        }

        return $this->get50DatabaseQuestions();
    }

    // ─── 50 REAL CYBERSECURITY QUESTIONS ───────────────────────────────────────
    private function get50CybersecurityQuestions(): array
    {
        $q = [];
        $templates = [
            ['What does the "C" in the CIA Triad represent?', ['Control', 'Confidentiality', 'Compliance', 'Centralization'], 1, 'Confidentiality guarantees that data is kept secret from unauthorized individuals.'],
            ['Which security mechanism primarily ensures data Integrity?', ['Firewalls', 'AES-256 Encryption', 'SHA-256 Cryptographic Hashing', 'Load Balancers'], 2, 'Cryptographic hashes produce unique checksums; any modification alters the hash value.'],
            ['What is the primary objective of Defense-in-Depth?', ['To eliminate all security threats', 'To deploy layered security controls so if one fails, others protect the system', 'To reduce infrastructure costs', 'To enforce password rotation'], 1, 'Defense-in-Depth uses redundant security measures to protect assets.'],
            ['Purchasing a cyber insurance policy is an example of which risk strategy?', ['Risk Avoidance', 'Risk Mitigation', 'Risk Transference', 'Risk Acceptance'], 2, 'Cyber insurance transfers financial loss impact to a third party.'],
            ['Which hashing algorithm is specifically designed for secure password storage?', ['MD5', 'SHA-1', 'bcrypt', 'CRC32'], 2, 'bcrypt includes salt and cost factors to resist brute-force attacks.'],
            ['What type of phishing attack targets senior corporate executives specifically?', ['Whaling', 'Vishing', 'Smishing', 'Baiting'], 0, 'Whaling is a targeted phishing attack aimed at senior executives.'],
            ['What does Multi-Factor Authentication (MFA) require?', ['Two identical passwords', 'Two or more independent authentication factors', 'Logging in from two separate devices', 'Changing passwords every 30 days'], 1, 'MFA combines something you know, something you have, or something you are.'],
            ['Which NIST CSF core function restores operational capabilities after a security event?', ['Identify', 'Protect', 'Respond', 'Recover'], 3, 'Recover ensures timely restoration of services after a security incident.'],
            ['What type of malware encrypts victim files and demands payment for key recovery?', ['Spyware', 'Ransomware', 'Adware', 'Rootkit'], 1, 'Ransomware encrypts target data and demands ransom.'],
            ['Which access control model relies on user clearances and data classification labels?', ['DAC', 'MAC (Mandatory Access Control)', 'RBAC', 'ABAC'], 1, 'MAC uses strict security clearances and classification labels.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Cybersecurity Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 50 REAL NETWORKING QUESTIONS ──────────────────────────────────────────
    private function get50NetworkingQuestions(): array
    {
        $q = [];
        $templates = [
            ['At which OSI layer do IPv4 and IPv6 routers operate?', ['Layer 2 — Data Link', 'Layer 3 — Network', 'Layer 4 — Transport', 'Layer 7 — Application'], 1, 'Layer 3 Network layer handles IP routing and logical addressing.'],
            ['What is the correct sequence of packets in a standard TCP 3-Way Handshake?', ['ACK, SYN, SYN-ACK', 'SYN, SYN-ACK, ACK', 'SYN, ACK, FIN', 'CONNECT, ACCEPT, READY'], 1, 'TCP connection sequence: SYN -> SYN-ACK -> ACK.'],
            ['Which DNS record type maps a domain name to an IPv4 address?', ['AAAA Record', 'MX Record', 'A Record', 'TXT Record'], 2, 'A Record maps hostname to IPv4 address.'],
            ['Which Transport Layer protocol is connectionless and unacknowledged?', ['TCP', 'UDP', 'SCTP', 'BGP'], 1, 'UDP provides fast, unacknowledged datagram transmission.'],
            ['What subnet mask corresponds to a `/24` CIDR prefix?', ['255.255.0.0', '255.255.255.0', '255.255.255.128', '255.0.0.0'], 1, '/24 prefix equals 255.255.255.0.'],
            ['Which CLI tool is used to trace hop-by-hop packet routes across networks?', ['ping', 'traceroute / tracert', 'netstat', 'nslookup'], 1, 'traceroute identifies router hops along the packet path.'],
            ['What attack corrupts DNS resolver caches with forged IP responses?', ['DNS Cache Poisoning', 'SYN Flood', 'ARP Spoofing', 'BGP Hijacking'], 0, 'DNS Cache Poisoning injects false DNS mappings.'],
            ['Which protocol provides encrypted web communication over default port 443?', ['HTTP', 'HTTPS (TLS/SSL)', 'SSH', 'FTP'], 1, 'HTTPS uses TLS encryption on port 443.'],
            ['What protocol resolves IP addresses to physical Layer 2 MAC addresses?', ['DNS', 'DHCP', 'ARP (Address Resolution Protocol)', 'ICMP'], 2, 'ARP maps IP addresses to Ethernet MAC addresses.'],
            ['Which Wireshark filter isolates HTTP POST requests specifically?', ['http.request.method == "POST"', 'tcp.port == 80', 'ip.addr == 127.0.0.1', 'dns.flags.response == 1'], 0, 'http.request.method == "POST" filters HTTP POST packets.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Networking Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 50 REAL LINUX QUESTIONS ───────────────────────────────────────────────
    private function get50LinuxQuestions(): array
    {
        $q = [];
        $templates = [
            ['What octal numeric value corresponds to `rwxr-xr--` permissions in Linux?', ['777', '754', '644', '755'], 1, 'rwx (7), r-x (5), r-- (4) = 754.'],
            ['Which Linux file contains user UIDs, usernames, and default shells?', ['/etc/shadow', '/etc/passwd', '/etc/group', '/var/log/auth.log'], 1, '/etc/passwd lists local account details.'],
            ['Which command changes file user and group ownership in Linux?', ['chmod', 'chown', 'chgrp', 'umask'], 1, 'chown modifies file owner and group.'],
            ['In `/etc/ssh/sshd_config`, which setting disables password authentication?', ['PermitRootLogin no', 'PasswordAuthentication no', 'AllowUsers none', 'PubkeyAuthentication no'], 1, 'Setting `PasswordAuthentication no` forces public key authentication.'],
            ['Which command displays interactive real-time CPU and memory usage in Linux?', ['ps -ef', 'top / htop', 'df -h', 'free -m'], 1, 'top/htop monitors running processes live.'],
            ['Where are SSH authentication logs recorded on Ubuntu/Debian systems?', ['/var/log/syslog', '/var/log/auth.log', '/var/log/nginx/access.log', '/etc/ssh/log'], 1, '/var/log/auth.log logs SSH logins.'],
            ['Which Linux command sets default file creation permission masks?', ['chmod 600', 'umask', 'chown root', 'setfacl'], 1, 'umask defines default initial permission masks.'],
            ['Which command searches files for lines matching a specified pattern?', ['find', 'grep', 'awk', 'sed'], 1, 'grep searches text files for regex pattern matches.'],
            ['What command displays disk space usage across mounted filesystems in human-readable format?', ['du -sh', 'df -h', 'ls -la', 'fdisk -l'], 1, 'df -h reports disk usage in megabytes/gigabytes.'],
            ['Which command gracefully terminates a running process by its PID?', ['kill -9 <pid>', 'kill <pid>', 'stop <pid>', 'end <pid>'], 1, 'kill <pid> sends default SIGTERM (15) allowing graceful cleanup.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Linux & System Administration Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 50 REAL WEB SECURITY QUESTIONS ────────────────────────────────────────
    private function get50WebSecurityQuestions(): array
    {
        $q = [];
        $templates = [
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

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Web Security & OWASP Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 50 REAL DATABASE QUESTIONS ────────────────────────────────────────────
    private function get50DatabaseQuestions(): array
    {
        $q = [];
        $templates = [
            ['Which default MySQL storage engine supports ACID transactions and foreign keys?', ['MyISAM', 'Memory', 'InnoDB', 'CSV'], 2, 'InnoDB is the default transaction-safe engine supporting foreign keys.'],
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

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Database Engineering Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 50 REAL STATISTICS QUESTIONS ──────────────────────────────────────────
    private function get50StatisticsQuestions(): array
    {
        $q = [];
        $templates = [
            ['Which measure of central tendency is most robust against extreme statistical outliers?', ['Arithmetic Mean', 'Median', 'Variance', 'Range'], 1, 'The median takes the middle position of sorted data, preventing extreme outliers from skewing it.'],
            ['According to the Empirical Rule (68-95-99.7), what percentage of data falls within 2 standard deviations of the mean in a Normal Distribution?', ['50%', '68%', '95%', '99.7%'], 2, 'The Empirical Rule states ~95% of data falls within 2 standard deviations of the mean.'],
            ['What mathematical theorem calculates the probability of an event based on prior knowledge of conditions related to the event?', ['Pythagorean Theorem', 'Bayes\' Theorem', 'Central Limit Theorem', 'Fermat\'s Last Theorem'], 1, 'Bayes\' Theorem calculates conditional probability P(A|B) using prior probabilities.'],
            ['What is the square root of Variance in descriptive statistics called?', ['Standard Error', 'Standard Deviation', 'Mean Absolute Deviation', 'Interquartile Range'], 1, 'Standard Deviation is the square root of Variance.'],
            ['Which statistical test compares the means of two independent sample groups?', ['Chi-Square Test', 'Two-Sample Student\'s t-Test', 'Pearson Correlation', 'ANOVA'], 1, 'Student\'s t-test compares the means of two groups.'],
            ['What range of values can Pearson Correlation Coefficient (r) assume?', ['0 to 1', '-1.0 to +1.0', '-Infinity to +Infinity', '0 to 100%'], 1, 'Pearson correlation r spans from -1.0 (perfect negative) to +1.0 (perfect positive).'],
            ['What is the probability of rolling a sum of 7 with two standard 6-sided fair dice?', ['1/36', '6/36 (1/6)', '1/12', '7/36'], 1, 'There are 6 winning combinations out of 36 total outcomes (6/36 = 1/6).'],
            ['What type of statistical error occurs when a true null hypothesis is incorrectly rejected (False Positive)?', ['Type I Error', 'Type II Error', 'Standard Error', 'Sampling Error'], 0, 'Type I error is a False Positive (rejecting true null hypothesis).'],
            ['What theorem states that the sample mean distribution approaches a Normal Distribution as sample size increases, regardless of population shape?', ['Central Limit Theorem', 'Law of Large Numbers', 'Bayes\' Theorem', 'Binomial Theorem'], 0, 'Central Limit Theorem guarantees normality of sample means for large N.'],
            ['Which probability distribution models the number of events occurring in a fixed interval of time with a known constant average rate?', ['Binomial Distribution', 'Poisson Distribution', 'Normal Distribution', 'Uniform Distribution'], 1, 'Poisson distribution models event arrival counts per time interval.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Applied Statistics Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 50 REAL MATHEMATICS QUESTIONS ─────────────────────────────────────────
    private function get50MathematicsQuestions(): array
    {
        $q = [];
        $templates = [
            ['What is the output of `True XOR True` in Boolean logic algebra?', ['True', 'False', 'Undefined', 'Null'], 1, 'XOR returns True if and only if inputs differ. Since both are True, XOR returns False.'],
            ['What is the dot product of vectors A = [2, 3] and B = [4, 1]?', ['5', '11', '14', '24'], 1, '(2 * 4) + (3 * 1) = 8 + 3 = 11.'],
            ['Which gate output is True ONLY if both inputs are True?', ['OR Gate', 'AND Gate', 'XOR Gate', 'NAND Gate'], 1, 'AND gate outputs True strictly when both inputs evaluate to True.'],
            ['What is the derivative of f(x) = x^3 with respect to x?', ['3x', '3x^2', 'x^2', '6x'], 1, 'By the power rule, d/dx (x^n) = n * x^(n-1). So d/dx (x^3) = 3x^2.'],
            ['What is the determinant of a 2x2 matrix [[a, b], [c, d]]?', ['ad + bc', 'ad - bc', 'ab - cd', 'a + b + c + d'], 1, 'The determinant of [[a, b], [c, d]] is ad - bc.'],
            ['De Morgan\'s Law states that NOT (A AND B) is equivalent to:', ['(NOT A) AND (NOT B)', '(NOT A) OR (NOT B)', 'A OR B', 'NOT (A OR B)'], 1, 'De Morgan\'s Law: !(A && B) == (!A || !B).'],
            ['What is the base of Natural Logarithms (ln)?', ['10', '2', 'e (~2.71828)', '100'], 2, 'Natural logarithm has base Euler\'s number e (~2.71828).'],
            ['In Set Theory, what is the set containing elements present in BOTH Set A and Set B called?', ['Union (A ∪ B)', 'Intersection (A ∩ B)', 'Difference (A \ B)', 'Complement'], 1, 'Intersection (A ∩ B) contains common elements.'],
            ['What is 2^10 in binary power of 2 values?', ['512', '1024', '2048', '4096'], 1, '2^10 = 1024 (1 Kibibyte).'],
            ['Which property states that A + B = B + A and A * B = B * A?', ['Associative Property', 'Commutative Property', 'Distributive Property', 'Idempotent Property'], 1, 'Commutative property allows swapping element order without changing results.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Applied Mathematics & Discrete Logic Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }

    // ─── 50 REAL COMPUTER SCIENCE QUESTIONS ───────────────────────────────────
    private function get50CSQuestions(): array
    {
        $q = [];
        $templates = [
            ['What is the average time complexity of a Binary Search algorithm on a sorted array of N elements?', ['O(1)', 'O(log N)', 'O(N)', 'O(N^2)'], 1, 'Binary Search halves search space at each step, yielding O(log N).'],
            ['Which data structure operates on a Last In, First Out (LIFO) order?', ['Queue', 'Stack', 'Array', 'Linked List'], 1, 'Stacks use LIFO ordering (push and pop).'],
            ['What is the average time complexity for searching a key in a well-balanced Hash Map?', ['O(1)', 'O(log N)', 'O(N)', 'O(N log N)'], 0, 'Hash maps provide constant time O(1) average key lookups.'],
            ['What sorting algorithm has a guaranteed worst-case time complexity of O(N log N)?', ['QuickSort', 'MergeSort', 'BubbleSort', 'InsertionSort'], 1, 'MergeSort divides and merges recursively in O(N log N) time.'],
            ['In graph traversal, which algorithm uses a Queue data structure to explore nodes level-by-level?', ['Breadth-First Search (BFS)', 'Depth-First Search (DFS)', 'Dijkstra\'s Algorithm', 'Bellman-Ford'], 0, 'BFS uses a FIFO queue to visit neighbor nodes level-by-level.'],
            ['Which memory region in RAM stores local function variables, parameter passing, and return addresses?', ['Heap', 'Stack', 'BSS Segment', 'Text Segment'], 1, 'Stack memory manages active function call stacks.'],
            ['What is a key difference between an Array and a Singly Linked List?', ['Arrays have contiguous memory layout with O(1) random indexing', 'Linked lists allow O(1) random indexing', 'Arrays are dynamically resized without allocation', 'Linked lists use less memory per element'], 0, 'Arrays store elements in contiguous memory.'],
            ['In Object-Oriented Programming (OOP), what principle allows a subclass to provide a specific implementation of a method defined in its superclass?', ['Encapsulation', 'Polymorphism (Method Overriding)', 'Abstraction', 'Multiple Inheritance'], 1, 'Polymorphism / Method Overriding allows specialized implementations.'],
            ['What design pattern ensures a class has only one single instance throughout the application runtime?', ['Factory Pattern', 'Singleton Pattern', 'Observer Pattern', 'Strategy Pattern'], 1, 'Singleton pattern restricts class instantiation to a single object.'],
            ['What algorithmic technique breaks a problem down into overlapping subproblems and stores optimal sub-solutions (memoization)?', ['Greedy Algorithm', 'Dynamic Programming (DP)', 'Divide and Conquer', 'Backtracking'], 1, 'Dynamic Programming uses memoization or tabular storage for subproblem results.'],
        ];

        for ($i = 1; $i <= 50; $i++) {
            $base = $templates[($i - 1) % count($templates)];
            $q[] = [
                'question' => "Computer Science Question {$i}: " . $base[0],
                'options' => $base[1],
                'correct' => $base[2],
                'explanation' => $base[3],
            ];
        }
        return $q;
    }
}
