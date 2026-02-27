<?php

namespace App\PdfBundle\Service;

use App\PdfBundle\Service\Engine\PdfEngineInterface;
use Twig\Environment;

class PdfGenerator
{
    private Environment $twig;
    private PdfEngineInterface $engine;

    public function __construct(Environment $twig, PdfEngineInterface $engine)
    {
        $this->twig = $twig;
        $this->engine = $engine;
    }

    public function render(string $template, array $context = [], array $options = []): string
    {
        $html = $this->twig->render($template, $context);
        return $this->engine->generate($html, $options);
    }
}

