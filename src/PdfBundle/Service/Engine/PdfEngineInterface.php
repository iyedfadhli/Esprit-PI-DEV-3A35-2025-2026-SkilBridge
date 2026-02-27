<?php

namespace App\PdfBundle\Service\Engine;

interface PdfEngineInterface
{
    public function generate(string $html, array $options = []): string;
}

