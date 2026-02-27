<?php

namespace App\PdfBundle\Service\Engine;

use Knp\Snappy\Pdf;

class KnpSnappyEngine implements PdfEngineInterface
{
    private Pdf $pdf;

    public function __construct(Pdf $pdf)
    {
        $this->pdf = $pdf;
    }

    public function generate(string $html, array $options = []): string
    {
        $normalized = $this->normalizeOptions($options);
        return $this->pdf->getOutputFromHtml($html, $normalized);
    }

    private function normalizeOptions(array $options): array
    {
        if (isset($options['paper'])) {
            $options['page-size'] = $options['paper'];
            unset($options['paper']);
        }
        return $options;
    }
}
