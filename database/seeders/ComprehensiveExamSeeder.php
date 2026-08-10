<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComprehensiveExamSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@lythub.com')->first();
        $interns = User::where('role', 'intern')->get();

        $exam = Exam::updateOrCreate(
            ['title' => 'Lythub Technologies Comprehensive Internship Certification Exam'],
            [
                'created_by' => $admin->id,
                'description' => 'Official 2-Hour Comprehensive Assessment containing 50 questions across Cybersecurity, Networking, Linux, MySQL, Web Security, and Software Engineering. Total Marks: 100.',
                'duration_minutes' => 120, // 2 Hours
                'pass_percentage' => 70, // 70% to pass
                'max_attempts' => 5,
                'randomize_questions' => true,
                'randomize_answers' => true,
                'show_results_immediately' => true,
                'status' => 'published',
            ]
        );

        // Assign to all interns
        foreach ($interns as $intern) {
            ExamAssignment::firstOrCreate(
                ['exam_id' => $exam->id, 'user_id' => $intern->id],
                ['assigned_by' => $admin->id, 'assigned_at' => now()]
            );
        }

        $questionsData = [
            // ─── Cybersecurity Fundamentals (1-10) ──────────────────────────────────
            [
                'q' => 'What does the "C" in the CIA Triad represent?',
                'opts' => ['Control', 'Confidentiality', 'Compliance', 'Centralization'],
                'ans' => 1,
                'exp' => 'Confidentiality ensures sensitive data is accessible only to authorized users.',
            ],
            [
                'q' => 'Which cryptographic function guarantees Data Integrity?',
                'opts' => ['Symmetric Encryption', 'Asymmetric Encryption', 'Cryptographic Hashing (e.g. SHA-256)', 'Steganography'],
                'ans' => 2,
                'exp' => 'Cryptographic hash functions generate unique fixed-length checksums to detect data alteration.',
            ],
            [
                'q' => 'What type of attack involves an adversary inserting themselves between two communicating parties?',
                'opts' => ['SQL Injection', 'Man-in-the-Middle (MitM)', 'Cross-Site Scripting', 'Denial of Service'],
                'ans' => 1,
                'exp' => 'A MitM attack intercepts or alters communication between two unsuspecting endpoints.',
            ],
            [
                'q' => 'Which security control category includes firewalls, Intrusion Prevention Systems (IPS), and WAFs?',
                'opts' => ['Administrative Controls', 'Technical / Engineering Controls', 'Physical Controls', 'Legal Controls'],
                'ans' => 1,
                'exp' => 'Technical controls employ hardware or software technology to protect systems.',
            ],
            [
                'q' => 'What is the primary objective of Defense-in-Depth?',
                'opts' => ['To eliminate all security threats completely', 'To deploy layered security controls so if one fails, others protect the system', 'To reduce hardware costs', 'To replace passwords with PINs'],
                'ans' => 1,
                'exp' => 'Defense-in-Depth uses redundant security measures to protect assets.',
            ],
            [
                'q' => 'Which hashing algorithm is specifically designed for secure password storage?',
                'opts' => ['MD5', 'SHA-1', 'bcrypt', 'CRC32'],
                'ans' => 2,
                'exp' => 'bcrypt includes salt and configurable work factors (cost) to resist brute-force attacks.',
            ],
            [
                'q' => 'What type of social engineering attack targets high-profile executives specifically?',
                'opts' => ['Whaling', 'Vishing', 'Smishing', 'Baiting'],
                'ans' => 0,
                'exp' => 'Whaling is a targeted phishing attack aimed at senior executives.',
            ],
            [
                'q' => 'In risk management, purchasing a cyber insurance policy is an example of:',
                'opts' => ['Risk Avoidance', 'Risk Mitigation', 'Risk Transference', 'Risk Acceptance'],
                'ans' => 2,
                'exp' => 'Cyber insurance transfers potential financial risk to an insurance company.',
            ],
            [
                'q' => 'What does Multi-Factor Authentication (MFA) require?',
                'opts' => ['Two different passwords', 'Two or more independent authentication factors (e.g. password + OTP)', 'Logging in from two separate devices', 'Changing passwords every 30 days'],
                'ans' => 1,
                'exp' => 'MFA combines something you know, something you have, or something you are.',
            ],
            [
                'q' => 'Which NIST Cybersecurity Framework core function focuses on restoring services after an incident?',
                'opts' => ['Identify', 'Protect', 'Respond', 'Recover'],
                'ans' => 3,
                'exp' => 'Recover ensures timely restoration of operational capabilities after a security event.',
            ],

            // ─── Networking & Protocols (11-18) ────────────────────────────────────
            [
                'q' => 'At which OSI layer do IPv4 and IPv6 routing protocols operate?',
                'opts' => ['Layer 2 — Data Link', 'Layer 3 — Network', 'Layer 4 — Transport', 'Layer 7 — Application'],
                'ans' => 1,
                'exp' => 'The Network Layer (Layer 3) handles logical IP addressing and packet routing.',
            ],
            [
                'q' => 'What is the correct sequence of packets in a standard TCP 3-Way Handshake?',
                'opts' => ['ACK, SYN, SYN-ACK', 'SYN, SYN-ACK, ACK', 'SYN, ACK, FIN', 'CONNECT, ACCEPT, READY'],
                'ans' => 1,
                'exp' => 'TCP connection begins with SYN, server responds with SYN-ACK, and client finishes with ACK.',
            ],
            [
                'q' => 'Which DNS record type maps a domain name to an IPv4 address?',
                'opts' => ['AAAA Record', 'MX Record', 'A Record', 'CNAME Record'],
                'ans' => 2,
                'exp' => 'An "A Record" points a hostname directly to an 32-bit IPv4 address.',
            ],
            [
                'q' => 'What protocol provides encrypted communication over port 443?',
                'opts' => ['HTTP', 'HTTPS (TLS/SSL)', 'SSH', 'SFTP'],
                'ans' => 1,
                'exp' => 'HTTPS uses Transport Layer Security (TLS) on TCP port 443.',
            ],
            [
                'q' => 'Which Transport Layer protocol is connectionless and does not guarantee packet delivery?',
                'opts' => ['TCP', 'UDP', 'SCTP', 'BGP'],
                'ans' => 1,
                'exp' => 'User Datagram Protocol (UDP) is lightweight, connectionless, and fast without acknowledgments.',
            ],
            [
                'q' => 'What subnet mask corresponds to a `/24` CIDR prefix?',
                'opts' => ['255.255.0.0', '255.255.255.0', '255.255.255.128', '255.0.0.0'],
                'ans' => 1,
                'exp' => 'A /24 prefix has 24 network bits set to 1, yielding 255.255.255.0.',
            ],
            [
                'q' => 'Which command-line tool is used to trace the hop-by-hop path packets take to a target IP?',
                'opts' => ['ping', 'traceroute / tracert', 'netstat', 'nslookup'],
                'ans' => 1,
                'exp' => 'traceroute displays the series of IP routers through which packets travel.',
            ],
            [
                'q' => 'What attack involves spoofing DNS response packets to redirect users to malicious sites?',
                'opts' => ['DNS Cache Poisoning', 'SYN Flood', 'ARP Spoofing', 'BGP Hijacking'],
                'ans' => 0,
                'exp' => 'DNS Cache Poisoning alters resolver caches to point queries to fake IP addresses.',
            ],

            // ─── Linux System Administration & Security (19-26) ───────────────────
            [
                'q' => 'What octal numeric value corresponds to `rwxr-xr--` permissions in Linux?',
                'opts' => ['777', '754', '644', '755'],
                'ans' => 1,
                'exp' => 'rwx (4+2+1=7), r-x (4+0+1=5), r-- (4+0+0=4) = 754.',
            ],
            [
                'q' => 'Which Linux file contains local user account information, default shell, and UID?',
                'opts' => ['/etc/shadow', '/etc/passwd', '/etc/group', '/var/log/auth.log'],
                'ans' => 1,
                'exp' => '/etc/passwd lists user account details (password hashes reside in /etc/shadow).',
            ],
            [
                'q' => 'Which command changes file ownership in a Linux filesystem?',
                'opts' => ['chmod', 'chown', 'chgrp', 'umask'],
                'ans' => 1,
                'exp' => 'chown (change owner) modifies the user and group ownership of files.',
            ],
            [
                'q' => 'In `/etc/ssh/sshd_config`, which directive disables password-based SSH logins?',
                'opts' => ['PermitRootLogin no', 'PasswordAuthentication no', 'AllowUsers none', 'PubkeyAuthentication no'],
                'ans' => 1,
                'exp' => 'Setting `PasswordAuthentication no` disables password logins, enforcing SSH key auth.',
            ],
            [
                'q' => 'Which Linux command displays live memory usage and running processes interactively?',
                'opts' => ['ps -ef', 'top / htop', 'df -h', 'uptime'],
                'ans' => 1,
                'exp' => 'top (or htop) displays dynamic real-time views of system processes.',
            ],
            [
                'q' => 'Which security directory logs SSH authentication attempts on Debian/Ubuntu systems?',
                'opts' => ['/var/log/syslog', '/var/log/auth.log', '/var/log/nginx/access.log', '/etc/security/logs'],
                'ans' => 1,
                'exp' => '/var/log/auth.log records system authorization events including SSH attempts.',
            ],
            [
                'q' => 'What command sets restrictive default file creation permissions for a user session?',
                'opts' => ['chmod 600', 'umask', 'chown root', 'setfacl'],
                'ans' => 1,
                'exp' => 'umask subtracts permissions from default 666 (files) / 777 (dirs).',
            ],
            [
                'q' => 'Which command searches for text patterns matching regular expressions in Linux files?',
                'opts' => ['find', 'grep', 'awk', 'sed'],
                'ans' => 1,
                'exp' => 'grep (global regular expression print) searches files for pattern matches.',
            ],

            // ─── MySQL Database & SQL (27-36) ──────────────────────────────────────
            [
                'q' => 'Which default MySQL storage engine supports ACID transactions and foreign keys?',
                'opts' => ['MyISAM', 'Memory', 'InnoDB', 'CSV'],
                'ans' => 2,
                'exp' => 'InnoDB is MySQL\'s default transaction-safe (ACID compliant) storage engine.',
            ],
            [
                'q' => 'Which SQL command removes all rows from a table quickly and resets auto-increment IDs?',
                'opts' => ['DELETE FROM table;', 'DROP TABLE table;', 'TRUNCATE TABLE table;', 'REMOVE TABLE table;'],
                'ans' => 2,
                'exp' => 'TRUNCATE drops and recreates the table, resetting auto-increment counters.',
            ],
            [
                'q' => 'Which JOIN type returns all records from the left table and matching records from the right?',
                'opts' => ['INNER JOIN', 'LEFT JOIN (LEFT OUTER JOIN)', 'RIGHT JOIN', 'FULL JOIN'],
                'ans' => 1,
                'exp' => 'LEFT JOIN keeps all rows from the left table, filling unmatched right columns with NULL.',
            ],
            [
                'q' => 'In SQL queries, which clause is used to filter aggregated results AFTER a `GROUP BY`?',
                'opts' => ['WHERE', 'HAVING', 'ORDER BY', 'LIMIT'],
                'ans' => 1,
                'exp' => 'HAVING filters aggregate values calculated by GROUP BY; WHERE filters before grouping.',
            ],
            [
                'q' => 'What constraint uniquely identifies each row in a table and cannot contain NULL values?',
                'opts' => ['FOREIGN KEY', 'UNIQUE', 'PRIMARY KEY', 'CHECK'],
                'ans' => 2,
                'exp' => 'A PRIMARY KEY must be unique and non-null for every row in a table.',
            ],
            [
                'q' => 'How does a B-Tree index improve database SELECT performance?',
                'opts' => ['By compressing disk data', 'By reducing search complexity from full table scan O(N) to O(log N)', 'By caching query results in memory', 'By bypassing foreign keys'],
                'ans' => 1,
                'exp' => 'B-Tree indexes structure indexed columns so queries locate records in logarithmic time.',
            ],
            [
                'q' => 'What does `type: ALL` indicate in a MySQL `EXPLAIN` query execution plan?',
                'opts' => ['An index lookup is used', 'A full table scan is occurring (slow query)', 'A primary key match occurred', 'Subquery execution'],
                'ans' => 1,
                'exp' => '`type: ALL` means MySQL is forced to scan every single row in the table.',
            ],
            [
                'q' => 'Which ACID property guarantees that all SQL statements in a transaction complete or roll back as one unit?',
                'opts' => ['Atomicity', 'Consistency', 'Isolation', 'Durability'],
                'ans' => 0,
                'exp' => 'Atomicity ensures all operations in a transaction succeed completely or none do.',
            ],
            [
                'q' => 'Which command utility is used to export a MySQL database to an SQL dump file?',
                'opts' => ['mysql-export', 'mysqldump', 'mysqladmin', 'db-backup'],
                'ans' => 1,
                'exp' => 'mysqldump is the official command-line utility for dumping MySQL databases.',
            ],
            [
                'q' => 'Which SQL privilege grants read-only access to query database records without altering them?',
                'opts' => ['ALL PRIVILEGES', 'INSERT', 'SELECT', 'UPDATE'],
                'ans' => 2,
                'exp' => 'The SELECT privilege allows reading data from tables without modification permissions.',
            ],

            // ─── Web Development & OWASP Vulnerabilities (37-44) ───────────────────
            [
                'q' => 'Which OWASP Top 10 vulnerability currently holds the #1 ranking for web risks?',
                'opts' => ['SQL Injection', 'Broken Access Control', 'Cryptographic Failures', 'SSRF'],
                'ans' => 1,
                'exp' => 'Broken Access Control (A01) is currently the highest-ranked risk on OWASP Top 10.',
            ],
            [
                'q' => 'What primary mechanism completely neutralizes SQL Injection vulnerabilities?',
                'opts' => ['Input regex validation only', 'Prepared Statements with Parameterized Queries', 'Web Application Firewalls (WAF) only', 'Base64 encoding input'],
                'ans' => 1,
                'exp' => 'Prepared statements separate code from user data, preventing user inputs from altering SQL logic.',
            ],
            [
                'q' => 'Changing a URL from `/api/invoice/10` to `/api/invoice/11` to access another user\'s data is an example of:',
                'opts' => ['Cross-Site Scripting (XSS)', 'Insecure Direct Object Reference (IDOR)', 'CSRF', 'SQL Injection'],
                'ans' => 1,
                'exp' => 'IDOR occurs when an app uses user-supplied input to access objects directly without authorization checks.',
            ],
            [
                'q' => 'Which attack type injects malicious client-side JavaScript into web pages viewed by other users?',
                'opts' => ['SQL Injection', 'Cross-Site Scripting (XSS)', 'CSRF', 'Command Injection'],
                'ans' => 1,
                'exp' => 'XSS allows attackers to execute scripts in victims\' browser sessions.',
            ],
            [
                'q' => 'What security header instructs web browsers to communicate exclusively over HTTPS?',
                'opts' => ['Content-Security-Policy', 'HTTP Strict Transport Security (HSTS)', 'X-Frame-Options', 'X-Content-Type-Options'],
                'ans' => 1,
                'exp' => 'HSTS forces browsers to use HTTPS for all subsequent requests to the domain.',
            ],
            [
                'q' => 'Which attack tricks a victim\'s authenticated browser into submitting unauthorized requests to a web app?',
                'opts' => ['Cross-Site Request Forgery (CSRF)', 'XSS', 'SSRF', 'Directory Traversal'],
                'ans' => 0,
                'exp' => 'CSRF tricks the browser into sending stored session cookies along with an unauthorized request.',
            ],
            [
                'q' => 'Which HTTP response status code represents "401 Unauthorized"?',
                'opts' => ['400 Bad Request', '401 Unauthorized (Unauthenticated)', '403 Forbidden', '404 Not Found'],
                'ans' => 1,
                'exp' => '401 Unauthorized means the request lacks valid authentication credentials.',
            ],
            [
                'q' => 'What does Server-Side Request Forgery (SSRF) allow an attacker to do?',
                'opts' => ['Execute JavaScript in victim browser', 'Force the web server to make requests to internal or external systems', 'Dump database tables directly', 'Overwrite local files'],
                'ans' => 1,
                'exp' => 'SSRF forces the target server to initiate network requests to internal or external resources.',
            ],

            // ─── Software Engineering & Laravel Architecture (45-50) ───────────────
            [
                'q' => 'In Model-View-Controller (MVC) architecture, which component manages business logic and database queries?',
                'opts' => ['View', 'Controller / Model', 'Router', 'Template Engine'],
                'ans' => 1,
                'exp' => 'Models handle data structure and queries; Controllers process requests and apply business logic.',
            ],
            [
                'q' => 'In RESTful API design, which HTTP method is idempotent and updates/replaces an existing resource?',
                'opts' => ['POST', 'PUT', 'GET', 'DELETE'],
                'ans' => 1,
                'exp' => 'PUT replaces or updates a target resource and is designed to be idempotent.',
            ],
            [
                'q' => 'What package authentication library provides lightweight token authentication for Laravel SPAs?',
                'opts' => ['Laravel Passport', 'Laravel Sanctum', 'JWT-Auth', 'Laravel Breeze'],
                'ans' => 1,
                'exp' => 'Laravel Sanctum provides API token and stateful cookie authentication for SPAs.',
            ],
            [
                'q' => 'In Git version control, which command creates and switches to a new feature branch?',
                'opts' => ['git branch new-feature', 'git checkout -b new-feature', 'git commit -m new-feature', 'git merge new-feature'],
                'ans' => 1,
                'exp' => '`git checkout -b <branch>` creates and immediately switches to the new branch.',
            ],
            [
                'q' => 'What is the primary benefit of continuous integration (CI) in software engineering?',
                'opts' => ['Faster internet speeds', 'Automatically testing and building code changes frequently to catch bugs early', 'Replacing manual documentation', 'Eliminating database migrations'],
                'ans' => 1,
                'exp' => 'CI automatically validates code changes with automated builds and tests.',
            ],
            [
                'q' => 'Which design pattern ensures a class has only one instance and provides a global point of access?',
                'opts' => ['Factory Pattern', 'Singleton Pattern', 'Observer Pattern', 'Strategy Pattern'],
                'ans' => 1,
                'exp' => 'The Singleton pattern restricts instantiation of a class to a single object instance.',
            ],
        ];

        foreach ($questionsData as $i => $data) {
            $q = Question::create([
                'exam_id' => $exam->id,
                'question_text' => $data['q'],
                'type' => 'mcq',
                'marks' => 2, // 50 questions x 2 marks = 100 total marks!
                'difficulty' => 'medium',
                'explanation' => $data['exp'],
                'order' => $i + 1,
            ]);

            foreach ($data['opts'] as $j => $optText) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option_text' => $optText,
                    'is_correct' => $j === $data['ans'],
                    'order' => $j,
                ]);
            }
        }
    }
}
