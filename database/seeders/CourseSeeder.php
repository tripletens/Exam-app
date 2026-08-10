<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LearningResource;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@lythub.com')->first();
        $interns = User::where('role', 'intern')->get();

        // ─── Course 1: Cybersecurity Fundamentals ────────────────────────────────
        $cyberCourse = Course::updateOrCreate(
            ['slug' => 'cybersecurity-fundamentals'],
            [
                'created_by' => $admin->id,
                'title' => 'Cybersecurity Fundamentals & Threat Protection',
                'description' => 'Comprehensive foundation in cybersecurity principles, threat vectors, network security, Linux CLI hardening, OWASP Top 10 vulnerabilities, and security operations.',
                'category' => 'Cybersecurity',
                'difficulty' => 'beginner',
                'status' => 'published',
                'estimated_duration' => 1200,
            ]
        );

        $cyberModules = [
            [
                'title' => 'Cybersecurity Fundamentals & The CIA Triad',
                'desc' => 'Core security concepts, confidentiality, integrity, availability, risk management, and authentication controls.',
                'video_url' => 'https://www.youtube.com/watch?v=inWWhr5tnEA',
                'pdf_url' => 'https://nvlpubs.nist.gov/nistpubs/SpecialPublications/NIST.SP.800-53r5.pdf',
                'pdf_title' => 'NIST SP 800-53 — Cybersecurity Controls & CIA Triad Guide.pdf',
                'lessons' => [
                    [
                        'title' => 'Introduction to the CIA Triad & Risk Management',
                        'content' => <<<'MD'
# Cybersecurity Fundamentals & CIA Triad

Security architecture relies on three primary pillars known as the **CIA Triad**:

### 1. Confidentiality
Guarantees that sensitive data is kept secret from unauthorized individuals.
- **Controls**: AES-256 Encryption, Access Control Lists (ACLs), Multi-Factor Authentication (MFA).

### 2. Integrity
Guarantees that data has not been altered or tampered with by unauthorized parties.
- **Controls**: Cryptographic Hashing (SHA-256), Digital Signatures, Write-Once Storage.

### 3. Availability
Guarantees that systems and applications remain accessible to authorized users when needed.
- **Controls**: Redundant Load Balancers, Automated Failover Clusters, Daily Offsite Backups.
MD,
                        'quiz' => [
                            [
                                'question' => 'Which pillar of the CIA Triad guarantees data is kept secret from unauthorized individuals?',
                                'options' => ['Control', 'Confidentiality', 'Compliance', 'Centralization'],
                                'correct' => 1,
                                'explanation' => 'Confidentiality ensures that information is accessible only to authorized users and systems.',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Networking Fundamentals for Security Engineers',
                'desc' => 'OSI model 7 layers, TCP/IP protocol suite, IP addressing, DNS resolution, and packet analysis.',
                'video_url' => 'https://www.youtube.com/watch?v=IPvYjXCsTg8',
                'pdf_url' => 'https://www.ietf.org/rfc/rfc793.txt',
                'pdf_title' => 'RFC 793 — Transmission Control Protocol (TCP/IP) Specification.pdf',
                'lessons' => [
                    [
                        'title' => 'OSI 7-Layer Model & TCP 3-Way Handshake',
                        'content' => <<<'MD'
# Networking Fundamentals for Security

Understanding network protocols is crucial for packet inspection and firewall configuration.

### The OSI 7-Layer Reference Model
1. **Layer 7 - Application**: HTTP, HTTPS, SSH, DNS.
2. **Layer 4 - Transport**: TCP (reliable connection), UDP (fast datagrams).
3. **Layer 3 - Network**: IPv4 / IPv6 logical addressing and routing.
4. **Layer 2 - Data Link**: Ethernet MAC physical addresses.

### TCP 3-Way Handshake
1. Client sends `SYN` (Synchronize).
2. Server responds with `SYN-ACK` (Synchronize-Acknowledge).
3. Client sends `ACK` (Acknowledge). Connection established.
MD,
                        'quiz' => [
                            [
                                'question' => 'What is the correct sequence of packets in a standard TCP 3-Way Handshake?',
                                'options' => ['ACK, SYN, SYN-ACK', 'SYN, SYN-ACK, ACK', 'SYN, ACK, FIN', 'CONNECT, ACCEPT, READY'],
                                'correct' => 1,
                                'explanation' => 'TCP connection sequence: SYN -> SYN-ACK -> ACK.',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Linux System Administration & Command Line Security',
                'desc' => 'Linux shell commands, file permissions, SSH hardening, and log analysis.',
                'video_url' => 'https://www.youtube.com/watch?v=wBp0Rb-ZJak',
                'pdf_url' => 'https://uwaterloo.ca/information-systems-technology/sites/ca.information-systems-technology/files/uploads/files/linux_cheat_sheet.pdf',
                'pdf_title' => 'Linux Command Line Security & Administration Cheat Sheet.pdf',
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

            // Video resource
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

            // Downloadable PDF reading resource
            LearningResource::updateOrCreate(
                ['module_id' => $module->id, 'title' => $modData['pdf_title']],
                [
                    'course_id' => $cyberCourse->id,
                    'type' => 'pdf',
                    'url' => $modData['pdf_url'],
                    'description' => "Official PDF study guide and reference document for {$modData['title']}.",
                    'is_required' => false,
                    'duration_minutes' => 45,
                ]
            );
        }

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
                'pdf_url' => 'https://dev.mysql.com/doc/refman/8.0/en/innodb-storage-engine.html',
                'pdf_title' => 'MySQL 8.0 Reference Manual — InnoDB Engine & Schema Design Guide.pdf',
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
            ],
            [
                'title' => 'DML & Advanced SELECT Queries',
                'desc' => 'INSERT, UPDATE, DELETE, TRUNCATE, filtering, sorting, aggregations, and GROUP BY clauses.',
                'video_url' => 'https://www.youtube.com/watch?v=HXV3zeQKqGY',
                'pdf_url' => 'https://downloads.mysql.com/docs/apis-php-en.pdf',
                'pdf_title' => 'SQL DML Queries, Aggregations & Grouping Reference Manual.pdf',
                'lessons' => [
                    [
                        'title' => 'SQL Query Execution & Aggregate Functions',
                        'content' => <<<'MD'
# Data Manipulation Language (DML) & SELECT Queries

### Aggregate Functions & Filtering
- `COUNT()`: Counts total rows.
- `AVG()`: Calculates average value.
- `HAVING`: Filters grouped results post `GROUP BY`.
MD,
                        'quiz' => [
                            [
                                'question' => 'Which SQL clause filters aggregate calculation results AFTER `GROUP BY`?',
                                'options' => ['WHERE', 'HAVING', 'ORDER BY', 'LIMIT'],
                                'correct' => 1,
                                'explanation' => 'HAVING filters aggregate values calculated by GROUP BY.',
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

            // Video resource
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

            // PDF resource
            LearningResource::updateOrCreate(
                ['module_id' => $module->id, 'title' => $modData['pdf_title']],
                [
                    'course_id' => $mysqlCourse->id,
                    'type' => 'pdf',
                    'url' => $modData['pdf_url'],
                    'description' => "Downloadable PDF reading guide for {$modData['title']}.",
                    'is_required' => false,
                    'duration_minutes' => 40,
                ]
            );
        }

        // Enroll all interns
        foreach ($interns as $internUser) {
            CourseEnrollment::firstOrCreate(
                ['user_id' => $internUser->id, 'course_id' => $cyberCourse->id],
                ['enrolled_by' => $admin->id, 'enrolled_at' => now()]
            );
            CourseEnrollment::firstOrCreate(
                ['user_id' => $internUser->id, 'course_id' => $mysqlCourse->id],
                ['enrolled_by' => $admin->id, 'enrolled_at' => now()]
            );
        }
    }
}
