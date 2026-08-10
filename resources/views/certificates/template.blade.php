<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion — Lythub Technologies</title>
    <style>
        @page { margin: 0; size: A4 landscape; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Georgia', serif;
            background: #fff;
            width: 297mm;
            height: 210mm;
            position: relative;
            overflow: hidden;
        }
        .border-outer {
            position: absolute;
            inset: 8mm;
            border: 4px solid #1a1a2e;
        }
        .border-inner {
            position: absolute;
            inset: 11mm;
            border: 1.5px solid #4a90a4;
        }
        .content {
            position: absolute;
            inset: 14mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px 40px;
        }
        .logo-text {
            font-size: 22px;
            font-weight: bold;
            color: #1a1a2e;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .logo-sub {
            font-size: 11px;
            color: #4a90a4;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .cert-title {
            font-size: 32px;
            color: #1a1a2e;
            font-style: italic;
            margin-bottom: 14px;
        }
        .cert-body {
            font-size: 13px;
            color: #555;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .intern-name {
            font-size: 36px;
            color: #1a1a2e;
            font-style: italic;
            margin: 10px 0;
            border-bottom: 2px solid #4a90a4;
            padding-bottom: 6px;
        }
        .course-name {
            font-size: 20px;
            color: #1a1a2e;
            font-weight: bold;
            margin: 10px 0;
            letter-spacing: 1px;
        }
        .meta {
            font-size: 11px;
            color: #888;
            margin-top: 20px;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #999;
        }
        .cert-number {
            font-size: 9px;
            color: #bbb;
            margin-top: 6px;
        }
        .corner {
            position: absolute;
            width: 30px;
            height: 30px;
            border-color: #4a90a4;
            border-style: solid;
        }
        .corner-tl { top: 14mm; left: 14mm; border-width: 3px 0 0 3px; }
        .corner-tr { top: 14mm; right: 14mm; border-width: 3px 3px 0 0; }
        .corner-bl { bottom: 14mm; left: 14mm; border-width: 0 0 3px 3px; }
        .corner-br { bottom: 14mm; right: 14mm; border-width: 0 3px 3px 0; }
    </style>
</head>
<body>
    <div class="border-outer"></div>
    <div class="border-inner"></div>
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <div class="content">
        <div class="logo-text">Lythub Technologies</div>
        <div class="logo-sub">Learning &amp; Development</div>

        <div class="cert-title">Certificate of Completion</div>

        <div class="cert-body">This certifies that</div>

        <div class="intern-name">{{ $certificate->user->name }}</div>

        <div class="cert-body">has successfully completed</div>

        <div class="course-name">
            {{ $certificate->course?->title ?? $certificate->learningPath?->title ?? 'Training Program' }}
        </div>

        <div class="meta">
            Completed on {{ $certificate->issued_at->format('F j, Y') }}
            &nbsp;&bull;&nbsp;
            Issued by {{ $certificate->issuedBy->name }}
        </div>

        <div class="footer">
            <span>Lythub Technologies</span>
            <span>lythub.com</span>
        </div>
        <div class="cert-number">Certificate No: {{ $certificate->certificate_number }}</div>
    </div>
</body>
</html>
