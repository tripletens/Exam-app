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
                    ],
                    [
                        'title' => 'DNS Protocols, Packet Sniffing & Subnetting',
                        'content' => <<<'MD'
# DNS Protocol & Network Traffic Analysis

### Domain Name System (DNS) Security
DNS translates human-readable hostnames (`lythub.com`) into IP addresses (`192.0.2.1`).

- **A Record**: Maps domain to IPv4 address.
- **AAAA Record**: Maps domain to IPv6 address.
- **MX Record**: Mail Exchange server routing.
- **TXT Record**: Used for SPF, DKIM, and domain verification.

### DNS Attack Vectors
- **DNS Cache Poisoning**: Injecting false IP addresses into resolver cache.
- **DNS Tunneling**: Exfiltrating stolen data over DNS TXT queries (bypassing firewalls).

### Wireshark Filter Cheat Sheet
```bash
# Filter by IP address
ip.addr == 192.168.1.50

# Filter HTTP POST requests
http.request.method == "POST"

# Detect potential port scans (SYN flags)
tcp.flags.syn == 1 && tcp.flags.ack == 0
```
MD,
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

Linux powers over 90% of cloud servers and security infrastructure (Kali Linux, Ubuntu Server, Debian).

### File Permissions Breakdown (`chmod`)
Permissions are represented by 3 octal digits corresponding to **User (u)**, **Group (g)**, and **Others (o)**:

- Read (`r`) = 4
- Write (`w`) = 2
- Execute (`x`) = 1

```bash
# Example: -rwxr-xr-- (754)
# User: rwx (4+2+1 = 7)
# Group: r-x (4+0+1 = 5)
# Others: r-- (4+0+0 = 4)

chmod 755 /var/www/html/script.sh
chown www-data:www-data /var/www/html/index.php
```

---

### Critical Security Directories
- `/etc/passwd`: List of local user accounts.
- `/etc/shadow`: Password hashes (accessible by root only).
- `/var/log/auth.log` or `/var/log/secure`: Authentication logs (SSH logins, sudo attempts).
- `/etc/ssh/sshd_config`: SSH daemon security configuration.
MD,
                    ],
                    [
                        'title' => 'SSH Hardening & Linux Log Inspection',
                        'content' => <<<'MD'
# SSH Daemon Hardening (`sshd_config`)

To secure a Linux server against brute-force attacks and unauthorized access:

### Recommended Settings in `/etc/ssh/sshd_config`
```ini
# Disable root password login
PermitRootLogin prohibit-password

# Disable password authentication (enforce Public Key Auth)
PasswordAuthentication no

# Change default port (mitigate automated scanners)
Port 2222

# Limit max auth attempts
MaxAuthTries 3
```

### Inspecting Security Logs
```bash
# View failed SSH login attempts
grep "Failed password" /var/log/auth.log | awk '{print $11}' | sort | uniq -c | sort -nr

# Monitor authentication log live
tail -f /var/log/auth.log
```
MD,
                    ]
                ]
            ],
            [
                'title' => 'Web Application Security & OWASP Top 10',
                'desc' => 'In-depth analysis of the OWASP Top 10 web vulnerabilities and countermeasures.',
                'video_url' => 'https://www.youtube.com/watch?v=Yf1o0K_WfHQ',
                'lessons' => [
                    [
                        'title' => 'OWASP Top 10 Vulnerabilities Overview',
                        'content' => <<<'MD'
# OWASP Top 10 Vulnerabilities Deep Dive

The **OWASP Top 10** represents the most critical web application security risks facing organizations worldwide:

1. **A01: Broken Access Control**: Users executing actions outside their intended permissions (IDOR, horizontal/vertical privilege escalation).
2. **A02: Cryptographic Failures**: Insecure storage/transmission of sensitive data (plaintext passwords, weak ciphers).
3. **A03: Injection**: Untrusted data sent to an interpreter as part of a command or query (SQLi, OS Command Injection, LDAP Injection).
4. **A04: Insecure Design**: Flaws in architectural design and threat modeling.
5. **A05: Security Misconfiguration**: Default passwords, open S3 buckets, verbose debug messages exposed.
6. **A06: Vulnerable and Outdated Components**: Outdated npm/composer dependencies with known CVEs.
7. **A07: Identification and Authentication Failures**: Weak password policies, lack of rate limiting, credential stuffing vulnerability.
8. **A08: Software and Data Integrity Failures**: Insecure deserialization, untrusted CI/CD pipelines.
9. **A09: Security Logging and Monitoring Failures**: Insufficient audit logs leading to undetected breaches.
10. **A10: Server-Side Request Forgery (SSRF)**: Web app fetching remote resources without validating the user-supplied URL.
MD,
                    ],
                    [
                        'title' => 'Preventing Broken Access Control & Misconfigurations',
                        'content' => <<<'MD'
# Preventing Broken Access Control (A01)

Broken Access Control is currently the #1 vulnerability on the OWASP Top 10 list.

### Common Access Control Flaws
- **Insecure Direct Object Reference (IDOR)**:
  `GET /api/invoices/105` — Changing URL parameter `105` to `106` grants access to another user's invoice.

### Remediation Code Example (Laravel Authorization Policy)
```php
public function view(User $user, Invoice $invoice): bool
{
    // Ensure invoice belongs to the requesting authenticated user
    return $user->id === $invoice->user_id || $user->hasRole('super_admin');
}
```

### Security Misconfiguration Defense Checklist
- Disable directory listing (`Options -Indexes` in Apache/Nginx).
- Turn off `APP_DEBUG=false` in production.
- Enforce HTTPS with HTTP Strict Transport Security (`HSTS`).
MD,
                    ]
                ]
            ],
            [
                'title' => 'SQL Injection (SQLi) Exploitation & Defense',
                'desc' => 'Understanding Inband, Blind, and Time-based SQLi, and fixing with Prepared Statements.',
                'video_url' => 'https://www.youtube.com/watch?v=ciNHn38EyRc',
                'lessons' => [
                    [
                        'title' => 'SQL Injection Vulnerability Mechanics',
                        'content' => <<<'MD'
# SQL Injection (SQLi) Fundamentals

SQL Injection occurs when user input is concatenated directly into a database query string without proper sanitization or parameterization.

### Vulnerable Code Example (PHP)
```php
// VULNERABLE TO SQL INJECTION!
$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
```

If attacker inputs: `admin' -- ` for username:
```sql
SELECT * FROM users WHERE username = 'admin' -- ' AND password = '...'
```
The `--` comments out the password validation check, logging the attacker in as `admin`!

---

### Types of SQL Injection
1. **In-Band SQLi (Classic)**: Error-based or UNION-based, results returned in response.
2. **Inferential SQLi (Blind)**: Boolean-based or Time-based delay (`SLEEP(5)`), no data returned directly.
3. **Out-of-Band SQLi**: Triggering DNS or HTTP requests to attacker-controlled server.
MD,
                    ],
                    [
                        'title' => 'Remediating SQL Injection with Prepared Statements',
                        'content' => <<<'MD'
# Securing Queries Against SQL Injection

The only complete defense against SQL Injection is using **Prepared Statements (Parameterized Queries)**.

### Secure Code Example (PDO / Prepared Statements)
```php
// SECURE PREPARED STATEMENT
$stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE email = :email AND is_active = :status');
$stmt->execute([
    'email' => $userEmail,
    'status' => 1
]);
$user = $stmt->fetch();
```

### How Eloquent ORM Protects You in Laravel
Laravel's Eloquent ORM uses PDO parameter bindings automatically:
```php
// SECURE - Eloquent auto-binds parameters
$user = User::where('email', $request->email)->first();

// CAUTION - Raw queries MUST use bindings
// SECURE raw query:
$users = DB::select('SELECT * FROM users WHERE status = ?', [$status]);
```
MD,
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
                    ['content' => $lData['content'], 'order' => $j + 1, 'duration_minutes' => 30]
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
                'max_attempts' => 2,
                'randomize_questions' => true,
                'randomize_answers' => true,
                'show_results_immediately' => true,
                'status' => 'published',
            ]
        );
        $this->seedCyberQuestions($cyberExam);

        CourseEnrollment::firstOrCreate(
            ['user_id' => $intern->id, 'course_id' => $cyberCourse->id],
            ['enrolled_by' => $admin->id, 'enrolled_at' => now()]
        );

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

### Key MySQL 8 Data Types
- **INT / BIGINT**: Whole numbers (4 bytes / 8 bytes).
- **DECIMAL(p, s)**: Exact numeric representation for currency/financials (e.g., `DECIMAL(10, 2)`).
- **VARCHAR(n)**: Variable-length string up to N characters.
- **TEXT / LONGTEXT**: Large string storage for articles/notes.
- **DATETIME / TIMESTAMP**: Date and time tracking (`YYYY-MM-DD HH:MM:SS`).
- **JSON**: Native JSON document support in MySQL 5.7+.

### InnoDB vs MyISAM Storage Engines
- **InnoDB (Default)**: Supports ACID transactions, Foreign Keys, row-level locking, and crash recovery.
- **MyISAM**: Legacy engine, table-level locking, no transaction support. Always use **InnoDB** in modern applications.
MD,
                    ],
                    [
                        'title' => 'Creating Databases & Table Schema DDL',
                        'content' => <<<'MD'
# Data Definition Language (DDL) Commands

### Creating a Database
```sql
CREATE DATABASE IF NOT EXISTS lythub_platform
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

### Table Definition with Constraints
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'instructor', 'intern') DEFAULT 'intern',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
MD,
                    ]
                ]
            ],
            [
                'title' => 'Data Manipulation Language (DML) & SELECT Queries',
                'desc' => 'INSERT, UPDATE, DELETE statements, WHERE filters, ORDER BY, and aggregation.',
                'video_url' => 'https://www.youtube.com/watch?v=HXV3zeQKqGY',
                'lessons' => [
                    [
                        'title' => 'Inserting, Updating & Deleting Records',
                        'content' => <<<'MD'
# Data Manipulation Language (DML)

### INSERT Command
```sql
INSERT INTO users (name, email, password, role)
VALUES ('Kwame Mensah', 'kwame@lythub.com', '$2y$12$...', 'intern');
```

### UPDATE Command (Always specify WHERE clause!)
```sql
UPDATE users
SET is_active = 1, department = 'Cybersecurity'
WHERE email = 'kwame@lythub.com';
```

### DELETE vs TRUNCATE
- `DELETE FROM users WHERE id = 5;`: Removes specified row, logs deletion, triggers CASCADE hooks.
- `TRUNCATE TABLE logs;`: Drops and recreates table, resets AUTO_INCREMENT, much faster.
MD,
                    ],
                    [
                        'title' => 'Advanced SELECT Filtering & Aggregations',
                        'content' => <<<'MD'
# Advanced SELECT Queries & Aggregate Functions

### Filtering with WHERE & LIKE
```sql
SELECT id, name, email, department
FROM users
WHERE department = 'Cybersecurity' AND is_active = 1
ORDER BY name ASC
LIMIT 10 OFFSET 0;
```

### Aggregate Functions (`COUNT`, `AVG`, `SUM`, `GROUP BY`, `HAVING`)
```sql
SELECT department, COUNT(*) AS total_interns, AVG(score) AS avg_score
FROM users
JOIN exam_attempts ON users.id = exam_attempts.user_id
GROUP BY department
HAVING avg_score >= 70.0;
```
> **Note**: `WHERE` filters rows before grouping; `HAVING` filters aggregate groups after `GROUP BY`.
MD,
                    ]
                ]
            ],
            [
                'title' => 'Relational JOINs & Subqueries',
                'desc' => 'INNER JOIN, LEFT JOIN, RIGHT JOIN, FULL JOIN, and correlated subqueries.',
                'video_url' => 'https://www.youtube.com/watch?v=9yeOJ0ZMUmo',
                'lessons' => [
                    [
                        'title' => 'Mastering INNER JOIN & LEFT JOIN',
                        'content' => <<<'MD'
# SQL Table JOINs Explained

JOINs allow you to combine data from multiple related tables in a single query.

### 1. INNER JOIN
Returns records that have matching values in both tables:
```sql
SELECT users.name, courses.title, course_enrollments.enrolled_at
FROM course_enrollments
INNER JOIN users ON course_enrollments.user_id = users.id
INNER JOIN courses ON course_enrollments.course_id = courses.id;
```

---

### 2. LEFT JOIN (OUTER JOIN)
Returns all records from the left table, and matched records from the right table:
```sql
-- Find all users and their certificates (including users with NO certificate)
SELECT users.name, certificates.certificate_number
FROM users
LEFT JOIN certificates ON users.id = certificates.user_id;
```
MD,
                    ],
                    [
                        'title' => 'Subqueries & Derived Tables',
                        'content' => <<<'MD'
# Subqueries & Complex Joins

### Subquery in WHERE Clause
```sql
-- Find interns who scored higher than average
SELECT id, name, email
FROM users
WHERE id IN (
    SELECT user_id
    FROM exam_attempts
    WHERE percentage > (SELECT AVG(percentage) FROM exam_attempts)
);
```

### Derived Table Subquery (in FROM clause)
```sql
SELECT u.name, attempts.max_score
FROM users u
JOIN (
    SELECT user_id, MAX(percentage) AS max_score
    FROM exam_attempts
    GROUP BY user_id
) attempts ON u.id = attempts.user_id;
```
MD,
                    ]
                ]
            ],
            [
                'title' => 'Indexes, EXPLAIN Plans & Query Optimization',
                'desc' => 'B-Tree indexes, composite indexes, query execution plans (`EXPLAIN`), and performance tuning.',
                'video_url' => 'https://www.youtube.com/watch?v=fsG1XaZEa78',
                'lessons' => [
                    [
                        'title' => 'Understanding B-Tree Indexes & Performance',
                        'content' => <<<'MD'
# MySQL B-Tree Indexing

Without indexes, MySQL must perform a full table scan O(N) to find matching rows. B-Tree indexes reduce lookup time to logarithmic complexity O(log N).

### Creating Indexes
```sql
-- Single-column index
CREATE INDEX idx_users_email ON users(email);

-- Composite Index (order matters! Most selective column first)
CREATE INDEX idx_attempts_user_exam ON exam_attempts(user_id, exam_id);
```

### When NOT to Index
- Small tables (under 1,000 rows).
- Frequently updated columns (every write requires re-indexing).
- Low-cardinality columns (e.g., `gender` or boolean flags).
MD,
                    ],
                    [
                        'title' => 'Analyzing Queries with EXPLAIN',
                        'content' => <<<'MD'
# Analyzing Query Execution Plans with EXPLAIN

Prefix any SELECT query with `EXPLAIN` to inspect how MySQL executes it:

```sql
EXPLAIN SELECT * FROM exam_attempts WHERE user_id = 5 AND exam_id = 1;
```

### Key EXPLAIN Columns to Watch
- `type`: `ALL` (bad: full table scan), `ref` or `eq_ref` (good: index lookup), `const` (best).
- `possible_keys`: Indexes MySQL could use.
- `key`: Actual index selected by the optimizer.
- `rows`: Estimated number of rows MySQL must examine. Reduce this number for faster queries!
MD,
                    ]
                ]
            ],
            [
                'title' => 'Transactions & Database Security',
                'desc' => 'ACID properties, isolation levels, database backup (`mysqldump`), and MySQL user privileges.',
                'video_url' => 'https://www.youtube.com/watch?v=gxdjs23Q8eU',
                'lessons' => [
                    [
                        'title' => 'ACID Transactions & Isolation Levels',
                        'content' => <<<'MD'
# MySQL ACID Transactions & Engine Safety

A **transaction** is a sequence of SQL operations performed as a single logical unit of work.

### ACID Properties
- **Atomicity**: All operations succeed or all roll back (All or Nothing).
- **Consistency**: Data moves from one valid state to another.
- **Isolation**: Concurrent transactions execute without interfering.
- **Durability**: Committed changes persist even after system crash.

### SQL Transaction Control
```sql
START TRANSACTION;

UPDATE accounts SET balance = balance - 100 WHERE id = 1;
UPDATE accounts SET balance = balance + 100 WHERE id = 2;

-- If no error occurred:
COMMIT;

-- If error occurred:
-- ROLLBACK;
```
MD,
                    ],
                    [
                        'title' => 'Database Backup, Restoration & User Privileges',
                        'content' => <<<'MD'
# MySQL Database Security & Backups

### Creating Database Backups (`mysqldump`)
```bash
# Dump single database to SQL file
mysqldump -u root -p lythub_platform > lythub_backup.sql

# Restore database from SQL file
mysql -u root -p lythub_platform < lythub_backup.sql
```

### Managing MySQL Users & Privileges
Never run web apps as the MySQL `root` user! Create least-privileged application users:

```sql
-- Create application user
CREATE USER 'lythub_app'@'localhost' IDENTIFIED BY 'SecurePass123!';

-- Grant specific privileges on application database
GRANT SELECT, INSERT, UPDATE, DELETE ON lythub_platform.* TO 'lythub_app'@'localhost';

-- Apply privilege changes
FLUSH PRIVILEGES;
```
MD,
                    ]
                ]
            ],
        ];

        foreach ($mysqlModules as $i => $modData) {
            $module = CourseModule::updateOrCreate(
                ['course_id' => $mysqlCourse->id, 'title' => $modData['title']],
                ['description' => $modData['desc'], 'order' => $i + 1]
            );

            foreach ($modData['lessons'] as $j => $lData) {
                Lesson::updateOrCreate(
                    ['module_id' => $module->id, 'title' => $lData['title']],
                    ['content' => $lData['content'], 'order' => $j + 1, 'duration_minutes' => 30]
                );
            }

            LearningResource::updateOrCreate(
                ['module_id' => $module->id, 'title' => "{$modData['title']} — Video Lecture"],
                [
                    'course_id' => $mysqlCourse->id,
                    'type' => 'youtube',
                    'url' => $modData['video_url'],
                    'description' => "Full video lecture covering {$modData['title']}.",
                    'is_required' => true,
                    'duration_minutes' => 25,
                ]
            );
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
