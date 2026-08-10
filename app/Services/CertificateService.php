<?php

namespace App\Services;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    public function generate(Certificate $certificate): string
    {
        $certificate->load('user', 'course', 'learningPath', 'issuedBy');

        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $certificate,
        ])->setPaper('A4', 'landscape');

        $path = storage_path("app/certificates/{$certificate->certificate_number}.pdf");

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return $path;
    }

    public function download(Certificate $certificate): \Symfony\Component\HttpFoundation\Response
    {
        $certificate->load('user', 'course', 'learningPath', 'issuedBy');

        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $certificate,
        ])->setPaper('A4', 'landscape');

        return $pdf->download("{$certificate->certificate_number}.pdf");
    }
}
