<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion — Lythub Technologies</title>
    <style>
        @page {
            margin: 0;
            size: A4 landscape;
        }
        body {
            font-family: 'DejaVu Serif', 'Georgia', serif;
            background-color: #0F172A;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            color: #F8FAFC;
        }
        .cert-container {
            padding: 25px;
            height: 100%;
            box-sizing: border-box;
        }
        .cert-border {
            border: 4px solid #D4AF37;
            padding: 20px;
            height: 100%;
            box-sizing: border-box;
            background-color: #0B132B;
            position: relative;
        }
        .inner-border {
            border: 1px solid #334155;
            padding: 35px 50px;
            text-align: center;
            height: 100%;
            box-sizing: border-box;
        }
        .brand-header {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #D4AF37;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .brand-sub {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            letter-spacing: 3px;
            color: #94A3B8;
            text-transform: uppercase;
            margin-bottom: 25px;
        }
        .cert-title {
            font-size: 34px;
            color: #FFFFFF;
            font-weight: normal;
            letter-spacing: 2px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .cert-subtitle {
            font-size: 13px;
            color: #94A3B8;
            font-style: italic;
            margin-bottom: 15px;
        }
        .recipient-name {
            font-size: 38px;
            color: #F59E0B;
            font-weight: bold;
            font-style: italic;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #D4AF37;
            display: inline-block;
            min-width: 400px;
        }
        .course-title {
            font-size: 22px;
            color: #FFFFFF;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }
        .footer-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .footer-cell {
            vertical-align: bottom;
            text-align: center;
            width: 33%;
        }
        .signature-line {
            border-top: 1px solid #475569;
            width: 80%;
            margin: 0 auto 5px auto;
            padding-top: 5px;
            font-size: 12px;
            color: #E2E8F0;
            font-weight: bold;
        }
        .signature-sub {
            font-size: 10px;
            color: #94A3B8;
        }
        .badge-gold {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid #D4AF37;
            background-color: #1E293B;
            margin: 0 auto;
            line-height: 70px;
            font-size: 9px;
            color: #D4AF37;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .cert-meta {
            font-size: 10px;
            color: #64748B;
            margin-top: 25px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="cert-container">
        <div class="cert-border">
            <div class="inner-border">

                <div class="brand-header">Lythub Technologies</div>
                <div class="brand-sub">Enterprise Learning & Development Institute</div>

                <div class="cert-title">Certificate of Completion</div>

                <div class="cert-subtitle">This official certificate is proudly presented to</div>

                <div class="recipient-name">{{ $certificate->user->name }}</div>

                <div class="cert-subtitle">for successfully completing the specialized industry curriculum in</div>

                <div class="course-title">
                    {{ $certificate->course?->title ?? $certificate->learningPath?->title ?? 'Software Engineering Training Program' }}
                </div>

                <table class="footer-table">
                    <tr>
                        <td class="footer-cell">
                            <div class="signature-line">{{ $certificate->issued_at->format('F j, Y') }}</div>
                            <div class="signature-sub">Date of Issuance</div>
                        </td>
                        <td class="footer-cell">
                            <div class="badge-gold">VERIFIED</div>
                        </td>
                        <td class="footer-cell">
                            <div class="signature-line">{{ $certificate->issuedBy->name }}</div>
                            <div class="signature-sub">Authorized Program Director</div>
                        </td>
                    </tr>
                </table>

                <div class="cert-meta">
                    Certificate ID: {{ $certificate->certificate_number }} &nbsp;|&nbsp; Verification URL: https://lythub.com/verify/{{ $certificate->certificate_number }}
                </div>

            </div>
        </div>
    </div>
</body>
</html>
