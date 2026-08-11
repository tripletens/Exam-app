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
        .wrapper-table {
            width: 100%;
            height: 570px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .outer-border-cell {
            padding: 10px;
            vertical-align: middle;
        }
        .outer-box {
            border: 3px solid #D4AF37;
            padding: 6px;
            background-color: #070D1B;
        }
        .inner-border-box {
            border: 1px solid #1E293B;
            padding: 15px 20px;
            text-align: center;
        }
        .brand-header {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #D4AF37;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .brand-sub {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            letter-spacing: 3px;
            color: #64748B;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .cert-title {
            font-size: 24px;
            color: #FFFFFF;
            font-weight: normal;
            letter-spacing: 3px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .cert-subtitle {
            font-size: 11px;
            color: #94A3B8;
            font-style: italic;
            margin-bottom: 5px;
        }
        .recipient-name {
            font-size: 30px;
            color: #F59E0B;
            font-weight: bold;
            font-style: italic;
            margin-top: 3px;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 2px solid #D4AF37;
            display: inline-block;
            min-width: 340px;
        }
        .course-title {
            font-size: 17px;
            color: #FFFFFF;
            font-weight: bold;
            margin-top: 5px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .footer-table {
            width: 100%;
            margin-top: 10px;
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
            padding-top: 4px;
            font-size: 10px;
            color: #E2E8F0;
            font-weight: bold;
        }
        .signature-sub {
            font-size: 8px;
            color: #94A3B8;
        }
        .seal-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 2px solid #D4AF37;
            background-color: #0F172A;
            margin: 0 auto;
            text-align: center;
        }
        .seal-text {
            font-size: 7px;
            color: #D4AF37;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 17px;
            text-transform: uppercase;
        }
        .cert-meta {
            font-size: 8px;
            color: #475569;
            margin-top: 10px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <table class="wrapper-table">
        <tr>
            <td class="outer-border-cell">
                <div class="outer-box">
                    <div class="inner-border-box">

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
