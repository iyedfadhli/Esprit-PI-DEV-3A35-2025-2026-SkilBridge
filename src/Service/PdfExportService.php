<?php

namespace App\Service;

use App\Entity\Course;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfExportService
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generateCoursePdf(Course $course, array $chapters = []): string
    {
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isFontSubsettingEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // Render the PDF template
        $html = $this->twig->render('pdf/course_export.html.twig', [
            'course' => $course,
            'chapters' => $chapters,
            'exportDate' => new \DateTime(),
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->output();
    }

    public function getFilename(Course $course): string
    {
        $title = preg_replace('/[^a-zA-Z0-9_-]/', '_', $course->getTitle());
        return sprintf('cours_%s_%s.pdf', $title, date('Y-m-d'));
    }
}
