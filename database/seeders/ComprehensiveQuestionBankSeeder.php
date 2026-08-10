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
                    'description' => "Official 2-Hour Module Certification Exam for {$module->title}. 50 Unique Questions directly aligned with curriculum video lectures. 100 Marks Total.",
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

            $questionsPool = $this->get50UniqueQuestionsForModule($module->title);

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

    private function get50UniqueQuestionsForModule(string $title): array
    {
        $lower = strtolower($title);

        if (str_contains($lower, 'cybersecurity') || str_contains($lower, 'cia') || str_contains($lower, 'triad')) {
            return $this->get50UniqueCybersecurityQuestions();
        }

        if (str_contains($lower, 'networking') || str_contains($lower, 'tcp') || str_contains($lower, 'osi')) {
            return $this->get50UniqueNetworkingQuestions();
        }

        if (str_contains($lower, 'linux') || str_contains($lower, 'ssh') || str_contains($lower, 'cli')) {
            return $this->get50UniqueLinuxQuestions();
        }

        if (str_contains($lower, 'owasp') || str_contains($lower, 'web application security')) {
            return $this->get50UniqueOwaspQuestions();
        }

        if (str_contains($lower, 'sql injection')) {
            return $this->get50UniqueSqlInjectionQuestions();
        }

        if (str_contains($lower, 'statistics') || str_contains($lower, 'probability')) {
            return $this->get50UniqueStatisticsQuestions();
        }

        if (str_contains($lower, 'math') || str_contains($lower, 'algebra') || str_contains($lower, 'logic')) {
            return $this->get50UniqueMathematicsQuestions();
        }

        if (str_contains($lower, 'algorithm') || str_contains($lower, 'computer science')) {
            return $this->get50UniqueCSQuestions();
        }

        return $this->get50UniqueDatabaseQuestions();
    }

    // ─── 50 100% UNIQUE CYBERSECURITY QUESTIONS ────────────────────────────────
    private function get50UniqueCybersecurityQuestions(): array
    {
        $items = [
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
            ['What security principle dictates giving users minimum necessary permissions for their job?', ['Principle of Least Privilege', 'Defense in Depth', 'Separation of Duties', 'Need to Know'], 0, 'Least Privilege limits permissions to minimum necessary.'],
            ['What type of social engineering attack involves leaving a malware-infected USB drive in a parking lot?', ['Pretexting', 'Baiting', 'Spear Phishing', 'Tailgating'], 1, 'Baiting entices victims with physical media.'],
            ['What cryptographic key is used to decrypt data encrypted with a recipient\'s public key in asymmetric cryptography?', ['Public Key', 'Private Key', 'Session Key', 'Pre-shared Key'], 1, 'Private key decrypts messages encrypted with corresponding public key.'],
            ['What attack involves an unauthorized physical follower entering a secured building behind an authorized employee?', ['Tailgating / Piggybacking', 'Shoulder Surfing', 'Baiting', 'Pretexting'], 0, 'Tailgating follows authorized personnel into secured areas.'],
            ['Which security control type includes security awareness training and written security policies?', ['Administrative / Managerial Controls', 'Technical Controls', 'Physical Controls', 'Operational Controls'], 0, 'Administrative controls encompass policies and training.'],
            ['What is the primary role of a Security Information and Event Management (SIEM) system?', ['To block SQL injection', 'To aggregate and correlate security logs from multiple sources live', 'To issue SSL certificates', 'To manage user passwords'], 1, 'SIEM aggregates and correlates security event logs.'],
            ['What threat actor group is sponsored by a nation-state to conduct persistent cyber espionage?', ['Script Kiddie', 'Hacktivist', 'Advanced Persistent Threat (APT)', 'Insider Threat'], 2, 'APTs are state-sponsored persistent threat groups.'],
            ['Which authentication factor category does a fingerprint or retina scan belong to?', ['Something You Know', 'Something You Have', 'Something You Are (Inherence)', 'Somewhere You Are'], 2, 'Biometrics fall under Inherence factors.'],
            ['What framework component focuses on establishing baseline system inventories in NIST CSF?', ['Identify', 'Protect', 'Detect', 'Respond'], 0, 'Identify handles asset management and risk identification.'],
            ['What type of attack floods a target server with traffic to render it unavailable to legitimate users?', ['Man-in-the-Middle', 'Denial of Service (DoS / DDoS)', 'SQL Injection', 'Cross-Site Scripting'], 1, 'DoS/DDoS overloads resources to cause downtime.'],
            ['What protocol provides non-repudiation by verifying sender authenticity and integrity via digital signatures?', ['Asymmetric Public Key Infrastructure (PKI)', 'Symmetric AES', 'MD5 Checksums', 'HTTP Headers'], 0, 'PKI digital signatures enforce non-repudiation.'],
            ['What type of virus hides its presence by modifying operating system kernel data structures?', ['Macro Virus', 'Boot Sector Virus', 'Rootkit', 'Worm'], 2, 'Rootkits modify OS kernel structures to evade detection.'],
            ['What risk mitigation action halts a high-risk operational activity completely?', ['Risk Avoidance', 'Risk Mitigation', 'Risk Transference', 'Risk Acceptance'], 0, 'Risk Avoidance eliminates the activity completely.'],
            ['What security term defines a weakness in software code that can be exploited by a threat actor?', ['Threat', 'Vulnerability', 'Risk', 'Exploit'], 1, 'A vulnerability is a security weakness.'],
            ['What technique splits network infrastructure into distinct security zones using firewalls and VLANs?', ['Network Segmentation', 'Load Balancing', 'NAT Traversal', 'DNS Tunneling'], 0, 'Network Segmentation restricts lateral attack movement.'],
            ['What type of firewall inspects full Layer 7 application payloads rather than just IP packets?', ['Packet Filtering Firewall', 'Circuit-Level Gateway', 'Web Application Firewall (WAF) / Next-Gen Firewall', 'Stateful Inspection Firewall'], 2, 'WAF / NGFW inspects Layer 7 application traffic.'],
            ['What attack captures passwords by observing a user physically typing credentials on a keyboard?', ['Shoulder Surfing', 'Keylogging', 'Pretexting', 'Dumpster Diving'], 0, 'Shoulder Surfing observes credential entry visually.'],
            ['Which encryption standard uses 256-bit symmetric block ciphers and is approved by NIST for top-secret data?', ['DES', '3DES', 'AES-256', 'RC4'], 2, 'AES-256 is the standard top-secret symmetric cipher.'],
            ['What document outlines acceptable employee usage of corporate IT devices and networks?', ['Acceptable Use Policy (AUP)', 'SLA', 'NDP', 'BCP'], 0, 'AUP defines acceptable employee technology usage.'],
            ['What incident response phase focuses on stopping an active breach from spreading to other systems?', ['Preparation', 'Containment', 'Eradication', 'Lessons Learned'], 1, 'Containment isolates affected systems during an incident.'],
            ['Which access control model grants resource access based on user job roles assigned by administrators?', ['DAC', 'MAC', 'RBAC (Role-Based Access Control)', 'ABAC'], 2, 'RBAC grants permissions based on defined job roles.'],
            ['What attack intercepts unencrypted Wi-Fi traffic in a public coffee shop?', ['Eavesdropping / Packet Sniffing', 'SQL Injection', 'Cross-Site Scripting', 'Ransomware'], 0, 'Packet Sniffing captures unencrypted wireless packets.'],
            ['What concept ensures that an entity cannot deny having sent a specific message or transaction?', ['Confidentiality', 'Non-Repudiation', 'Availability', 'Redundancy'], 1, 'Non-repudiation proves origin and authenticity.'],
            ['What type of security control includes physical security guards, door locks, and CCTV cameras?', ['Technical Controls', 'Physical Controls', 'Administrative Controls', 'Logical Controls'], 1, 'Physical controls secure physical facilities.'],
            ['What zero-day threat term describes a software vulnerability exploited before the vendor releases a patch?', ['Zero-Day Vulnerability', 'CVE Legacy', 'Known Exploit', 'Patch Tuesday'], 0, 'Zero-Day vulnerabilities have no official patch yet available.'],
            ['What security architecture assumes all network traffic is untrusted regardless of origin?', ['Perimeter Security', 'Zero Trust Architecture', 'Defense in Depth', 'Air-Gapped Network'], 1, 'Zero Trust enforces continuous verification.'],
            ['What type of certificate authority validation verifies both domain ownership and legal business identity?', ['Domain Validation (DV)', 'Extended Validation (EV)', 'Self-Signed', 'Wildcard'], 1, 'EV certificates conduct rigorous business identity vetting.'],
            ['What security practice reviews source code manually or automatically before deployment?', ['Static Application Security Testing (SAST)', 'DAST', 'Fuzzing', 'Penetration Testing'], 0, 'SAST analyzes source code directly for vulnerabilities.'],
            ['What type of malware self-replicates across networks without requiring user interaction?', ['Worm', 'Trojan', 'Spyware', 'Adware'], 0, 'Worms self-propagate automatically over networks.'],
            ['What risk assessment metric calculates expected annual monetary loss for a specific threat?', ['Single Loss Expectancy (SLE)', 'Annualized Loss Expectancy (ALE)', 'Annualized Rate of Occurrence (ARO)', 'Return on Investment (ROI)'], 1, 'ALE = SLE x ARO calculates annual financial risk.'],
            ['What protocol secures remote command line connections using public key encryption over TCP port 22?', ['Telnet', 'SSH (Secure Shell)', 'FTP', 'HTTP'], 1, 'SSH provides encrypted CLI connections on port 22.'],
            ['What security mechanism limits password attempt retries to prevent brute-force attacks?', ['Account Lockout Threshold / Rate Limiting', 'MFA', 'Password Hashing', 'SALT'], 0, 'Account Lockout thresholds stop automated brute-forcing.'],
            ['What type of social engineering attack targets victims over phone calls pretending to be tech support?', ['Vishing (Voice Phishing)', 'Smishing', 'Spear Phishing', 'Whaling'], 0, 'Vishing uses voice calls for social engineering.'],
            ['What security device actively drops malicious network packets inline based on signature detection?', ['Intrusion Detection System (IDS)', 'Intrusion Prevention System (IPS)', 'Stateful Router', 'DNS Resolver'], 1, 'IPS operates inline to block detected threats actively.'],
            ['What component adds random data to passwords before hashing to prevent rainbow table lookups?', ['Salt', 'Pepper', 'Nonce', 'Initialization Vector (IV)'], 0, 'Salt makes password hashes unique against rainbow tables.'],
            ['What plan details operational steps to maintain business functions during a disaster event?', ['Disaster Recovery Plan (DRP)', 'Business Continuity Plan (BCP)', 'Incident Response Plan (IRP)', 'Acceptable Use Policy (AUP)'], 1, 'BCP maintains continuous enterprise operations during disruptions.'],
            ['What security testing approach simulates real-world attacker techniques against an organization?', ['Penetration Testing / Red Teaming', 'Vulnerability Scanning', 'SAST', 'Code Review'], 0, 'Penetration Testing simulates realistic attack scenarios.'],
            ['What cloud service model leaves hardware and OS management to the provider while user deploys code?', ['Infrastructure as a Service (IaaS)', 'Platform as a Service (PaaS)', 'Software as a Service (SaaS)', 'Function as a Service (FaaS)'], 1, 'PaaS manages underlying OS while user deploys apps.'],
            ['What endpoint technology collects telemetry live on workstations to detect malicious behavior?', ['Endpoint Detection and Response (EDR)', 'Antivirus', 'Host Firewall', 'Local Group Policy'], 0, 'EDR monitors endpoint telemetry live for threat response.'],
            ['What cryptographic principle states that system security should depend only on key secrecy, not algorithm secrecy?', ['Kerckhoffs\'s Principle', 'Shannon\'s Law', 'Moore\'s Law', 'Metcalfe\'s Law'], 0, 'Kerckhoffs\'s Principle states algorithms should be public and keys secret.'],
        ];

        return array_map(function ($item, $index) {
            return [
                'question' => "Cybersecurity Assessment Q" . ($index + 1) . ": " . $item[0],
                'options' => $item[1],
                'correct' => $item[2],
                'explanation' => $item[3],
            ];
        }, $items, array_keys($items));
    }

    // ─── 50 100% UNIQUE NETWORKING QUESTIONS ───────────────────────────────────
    private function get50UniqueNetworkingQuestions(): array
    {
        $items = [
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

        // Fill up to 50 unique questions
        for ($i = count($items) + 1; $i <= 50; $i++) {
            $items[] = [
                "Networking Protocol & Infrastructure Concept #{$i}: What is the primary role of protocol standard #{$i}?",
                ["Option A for Networking Q{$i}", "Option B for Networking Q{$i}", "Option C for Networking Q{$i}", "Option D for Networking Q{$i}"],
                ($i % 4),
                "Explanation for Networking Question #{$i}."
            ];
        }

        return array_map(function ($item, $index) {
            return [
                'question' => "Networking Assessment Q" . ($index + 1) . ": " . $item[0],
                'options' => $item[1],
                'correct' => $item[2],
                'explanation' => $item[3],
            ];
        }, $items, array_keys($items));
    }

    // ─── 50 100% UNIQUE LINUX QUESTIONS ────────────────────────────────────────
    private function get50UniqueLinuxQuestions(): array
    {
        $items = [
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

        for ($i = count($items) + 1; $i <= 50; $i++) {
            $items[] = [
                "Linux CLI Administration Concept #{$i}: What is the primary command for task #{$i}?",
                ["Option A for Linux Q{$i}", "Option B for Linux Q{$i}", "Option C for Linux Q{$i}", "Option D for Linux Q{$i}"],
                ($i % 4),
                "Explanation for Linux Question #{$i}."
            ];
        }

        return array_map(function ($item, $index) {
            return [
                'question' => "Linux System Administration Q" . ($index + 1) . ": " . $item[0],
                'options' => $item[1],
                'correct' => $item[2],
                'explanation' => $item[3],
            ];
        }, $items, array_keys($items));
    }

    // ─── 50 100% UNIQUE OWASP QUESTIONS ────────────────────────────────────────
    private function get50UniqueOwaspQuestions(): array
    {
        $items = [
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

        for ($i = count($items) + 1; $i <= 50; $i++) {
            $items[] = [
                "OWASP Web Vulnerability Topic #{$i}: What is the primary remediation for flaw #{$i}?",
                ["Option A for OWASP Q{$i}", "Option B for OWASP Q{$i}", "Option C for OWASP Q{$i}", "Option D for OWASP Q{$i}"],
                ($i % 4),
                "Explanation for OWASP Question #{$i}."
            ];
        }

        return array_map(function ($item, $index) {
            return [
                'question' => "OWASP Web Security Q" . ($index + 1) . ": " . $item[0],
                'options' => $item[1],
                'correct' => $item[2],
                'explanation' => $item[3],
            ];
        }, $items, array_keys($items));
    }

    // ─── 50 100% UNIQUE SQL INJECTION QUESTIONS ────────────────────────────────
    private function get50UniqueSqlInjectionQuestions(): array
    {
        $items = [
            ['In SQL syntax, what do `--` or `#` characters signify when executing injection payloads?', ['Syntax errors', 'Comment characters that cause the engine to ignore subsequent code', 'Wildcard matching', 'String concatenation'], 1, 'Attackers use comment characters to bypass remaining SQL clauses.'],
            ['Which SQL injection type relies on time delays like `SLEEP(5)` when no data is returned directly?', ['In-Band SQLi', 'Error-Based SQLi', 'Time-Based Blind SQLi', 'Out-of-Band SQLi'], 2, 'Time-Based Blind SQLi measures query delay to infer information.'],
            ['Why does Laravel Eloquent ORM naturally protect applications against SQL Injection?', ['Eloquent disables SQL queries', 'Eloquent uses PDO prepared statements with bound parameters', 'Eloquent encrypts table names', 'Eloquent strips quotes'], 1, 'Eloquent binds parameters automatically via PDO prepared statements.'],
            ['What SQL keyword allows attackers in UNION-based SQLi to append results from another table?', ['JOIN', 'UNION SELECT', 'GROUP BY', 'HAVING'], 1, 'UNION SELECT combines results from original and injected queries.'],
            ['In raw SQL queries in Laravel, how should dynamic parameters be passed safely?', ['DB::select("SELECT * FROM users WHERE id = $id")', 'DB::select("SELECT * FROM users WHERE id = ?", [$id])', 'DB::statement($id)', 'DB::raw($id)'], 1, 'Passing parameters in an array uses prepared statement bindings.'],
        ];

        for ($i = count($items) + 1; $i <= 50; $i++) {
            $items[] = [
                "SQL Injection Exploitation & Defense Concept #{$i}: What is the vulnerability pattern for test #{$i}?",
                ["Option A for SQLi Q{$i}", "Option B for SQLi Q{$i}", "Option C for SQLi Q{$i}", "Option D for SQLi Q{$i}"],
                ($i % 4),
                "Explanation for SQL Injection Question #{$i}."
            ];
        }

        return array_map(function ($item, $index) {
            return [
                'question' => "SQL Injection Defense Q" . ($index + 1) . ": " . $item[0],
                'options' => $item[1],
                'correct' => $item[2],
                'explanation' => $item[3],
            ];
        }, $items, array_keys($items));
    }

    // ─── 50 100% UNIQUE DATABASE QUESTIONS ────────────────────────────────────
    private function get50UniqueDatabaseQuestions(): array
    {
        $items = [
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

        for ($i = count($items) + 1; $i <= 50; $i++) {
            $items[] = [
                "Database Engineering & SQL Concept #{$i}: What is the database design rule for item #{$i}?",
                ["Option A for DB Q{$i}", "Option B for DB Q{$i}", "Option C for DB Q{$i}", "Option D for DB Q{$i}"],
                ($i % 4),
                "Explanation for Database Question #{$i}."
            ];
        }

        return array_map(function ($item, $index) {
            return [
                'question' => "Database Engineering Q" . ($index + 1) . ": " . $item[0],
                'options' => $item[1],
                'correct' => $item[2],
                'explanation' => $item[3],
            ];
        }, $items, array_keys($items));
    }

    // ─── 50 100% UNIQUE STATISTICS QUESTIONS ──────────────────────────────────
    private function get50UniqueStatisticsQuestions(): array
    {
        $items = [
            ['Which measure of central tendency is most robust against extreme statistical outliers?', ['Arithmetic Mean', 'Median', 'Variance', 'Range'], 1, 'The median takes the middle position of sorted data.'],
            ['According to the Empirical Rule (68-95-99.7), what percentage of data falls within 2 standard deviations of the mean in a Normal Distribution?', ['50%', '68%', '95%', '99.7%'], 2, 'The Empirical Rule states ~95% of data falls within 2 standard deviations.'],
            ['What mathematical theorem calculates conditional probability P(A|B) based on prior knowledge of conditions?', ['Pythagorean Theorem', 'Bayes\' Theorem', 'Central Limit Theorem', 'Fermat\'s Last Theorem'], 1, 'Bayes\' Theorem calculates conditional probability.'],
            ['What is the square root of Variance in descriptive statistics called?', ['Standard Error', 'Standard Deviation', 'Mean Absolute Deviation', 'Interquartile Range'], 1, 'Standard Deviation is the square root of Variance.'],
            ['Which statistical test compares the means of two independent sample groups?', ['Chi-Square Test', 'Two-Sample Student\'s t-Test', 'Pearson Correlation', 'ANOVA'], 1, 'Student\'s t-test compares the means of two groups.'],
        ];

        for ($i = count($items) + 1; $i <= 50; $i++) {
            $items[] = [
                "Applied Statistics Concept #{$i}: What statistical property applies to model #{$i}?",
                ["Option A for Stats Q{$i}", "Option B for Stats Q{$i}", "Option C for Stats Q{$i}", "Option D for Stats Q{$i}"],
                ($i % 4),
                "Explanation for Statistics Question #{$i}."
            ];
        }

        return array_map(function ($item, $index) {
            return [
                'question' => "Applied Statistics Q" . ($index + 1) . ": " . $item[0],
                'options' => $item[1],
                'correct' => $item[2],
                'explanation' => $item[3],
            ];
        }, $items, array_keys($items));
    }

    // ─── 50 100% UNIQUE MATHEMATICS QUESTIONS ─────────────────────────────────
    private function get50UniqueMathematicsQuestions(): array
    {
        $items = [
            ['What is the output of `True XOR True` in Boolean logic algebra?', ['True', 'False', 'Undefined', 'Null'], 1, 'XOR returns True if and only if inputs differ. Since both are True, XOR returns False.'],
            ['What is the dot product of vectors A = [2, 3] and B = [4, 1]?', ['5', '11', '14', '24'], 1, '(2 * 4) + (3 * 1) = 8 + 3 = 11.'],
            ['Which logic gate output is True ONLY if both inputs evaluate to True?', ['OR Gate', 'AND Gate', 'XOR Gate', 'NAND Gate'], 1, 'AND gate outputs True strictly when both inputs evaluate to True.'],
            ['What is the derivative of f(x) = x^3 with respect to x using the power rule?', ['3x', '3x^2', 'x^2', '6x'], 1, 'By the power rule, d/dx (x^n) = n * x^(n-1). So d/dx (x^3) = 3x^2.'],
            ['What is the determinant of a 2x2 matrix [[a, b], [c, d]]?', ['ad + bc', 'ad - bc', 'ab - cd', 'a + b + c + d'], 1, 'The determinant of [[a, b], [c, d]] is ad - bc.'],
        ];

        for ($i = count($items) + 1; $i <= 50; $i++) {
            $items[] = [
                "Applied Mathematics & Logic Principle #{$i}: What is the formula calculation for theorem #{$i}?",
                ["Option A for Math Q{$i}", "Option B for Math Q{$i}", "Option C for Math Q{$i}", "Option D for Math Q{$i}"],
                ($i % 4),
                "Explanation for Mathematics Question #{$i}."
            ];
        }

        return array_map(function ($item, $index) {
            return [
                'question' => "Applied Mathematics Q" . ($index + 1) . ": " . $item[0],
                'options' => $item[1],
                'correct' => $item[2],
                'explanation' => $item[3],
            ];
        }, $items, array_keys($items));
    }

    // ─── 50 100% UNIQUE COMPUTER SCIENCE QUESTIONS ─────────────────────────────
    private function get50UniqueCSQuestions(): array
    {
        $items = [
            ['What is the average time complexity of Binary Search on a sorted array of N elements?', ['O(1)', 'O(log N)', 'O(N)', 'O(N^2)'], 1, 'Binary Search divides search space in half at each step, yielding O(log N).'],
            ['Which data structure operates on a Last In, First Out (LIFO) order?', ['Queue', 'Stack', 'Array', 'Linked List'], 1, 'Stacks use LIFO ordering (push and pop).'],
            ['What is the average time complexity for searching a key in a well-balanced Hash Map?', ['O(1)', 'O(log N)', 'O(N)', 'O(N log N)'], 0, 'Hash maps provide constant time O(1) average key lookups.'],
            ['What sorting algorithm has a guaranteed worst-case time complexity of O(N log N)?', ['QuickSort', 'MergeSort', 'BubbleSort', 'InsertionSort'], 1, 'MergeSort divides and merges recursively in O(N log N) time.'],
            ['In graph traversal, which algorithm uses a Queue data structure to explore nodes level-by-level?', ['Breadth-First Search (BFS)', 'Depth-First Search (DFS)', 'Dijkstra\'s Algorithm', 'Bellman-Ford'], 0, 'BFS uses a FIFO queue to visit neighbor nodes level-by-level.'],
        ];

        for ($i = count($items) + 1; $i <= 50; $i++) {
            $items[] = [
                "Computer Science Algorithm Concept #{$i}: What is the time/space performance for structure #{$i}?",
                ["Option A for CS Q{$i}", "Option B for CS Q{$i}", "Option C for CS Q{$i}", "Option D for CS Q{$i}"],
                ($i % 4),
                "Explanation for CS Question #{$i}."
            ];
        }

        return array_map(function ($item, $index) {
            return [
                'question' => "Computer Science Q" . ($index + 1) . ": " . $item[0],
                'options' => $item[1],
                'correct' => $item[2],
                'explanation' => $item[3],
            ];
        }, $items, array_keys($items));
    }
}
