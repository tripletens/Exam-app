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
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #070D1B;
            font-family: 'DejaVu Serif', 'Georgia', serif;
            color: #F8FAFC;
        }
        .cert-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .cert-cell {
            padding: 10mm 14mm;
            vertical-align: middle;
        }
        .outer-border {
            border: 3px solid #D4AF37;
            padding: 5px;
            background-color: #070D1B;
        }
        .inner-border {
            border: 1px solid #1E293B;
            padding: 22px 35px;
            text-align: center;
            background-color: #070D1B;
        }
        .brand-header {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 6px;
            color: #D4AF37;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .brand-sub {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            letter-spacing: 4px;
            color: #64748B;
            text-transform: uppercase;
            margin-bottom: 22px;
        }
        .cert-title {
            font-size: 26px;
            color: #FFFFFF;
            font-weight: normal;
            letter-spacing: 4px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .cert-subtitle {
            font-size: 11px;
            color: #94A3B8;
            font-style: italic;
            margin-bottom: 8px;
        }
        .recipient-name {
            font-size: 32px;
            color: #F59E0B;
            font-weight: bold;
            font-style: italic;
            margin-top: 6px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #D4AF37;
            display: inline-block;
            min-width: 360px;
        }
        .course-title {
            font-size: 19px;
            color: #FFFFFF;
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 22px;
            letter-spacing: 1px;
        }
        .footer-table {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
        }
        .footer-cell {
            vertical-align: bottom;
            text-align: center;
            width: 33%;
        }
        .signature-line {
            border-top: 1px solid #475569;
            width: 75%;
            margin: 0 auto 4px auto;
            padding-top: 5px;
            font-size: 11px;
            color: #E2E8F0;
            font-weight: bold;
        }
        .signature-sub {
            font-size: 9px;
            color: #94A3B8;
        }
        .seal-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #D4AF37;
            background-color: #0F172A;
            margin: 0 auto;
            text-align: center;
        }
        .seal-text {
            font-size: 8px;
            color: #D4AF37;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 18px;
            text-transform: uppercase;
        }
        .cert-meta {
            font-size: 8.5px;
            color: #475569;
            margin-top: 18px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <table class="cert-table">
        <tr>
            <td class="cert-cell">
                <div class="outer-border">
                    <div class="inner-border">

                        <div class="brand-header">Lythub Technologies</div>
                        <div class="brand-sub">Enterprise Learning &amp; Development Institute</div>

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
                                    <div class="seal-circle">
                                        <div class="seal-text">VERIFIED</div>
                                    </div>
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
            </td>
        </tr>
    </table>
</body>
</html>
