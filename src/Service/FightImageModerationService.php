<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FightImageModerationService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $fightModerationUrl,
        private readonly int $fightModerationTimeout,
        private readonly bool $fightModerationFailOpen,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return array{status: string, reason: string, confidence: float}
     */
    public function verifyImage(string $absoluteImagePath): array
    {
        if (!is_file($absoluteImagePath)) {
            return ['status' => 'SAFE', 'reason' => '', 'confidence' => 1.0];
        }

        if ($this->looksLikePlaceholder($this->fightModerationUrl)) {
            return ['status' => 'UNKNOWN', 'reason' => 'fight_moderation_unavailable', 'confidence' => 0.0];
        }

        try {
            $formData = new FormDataPart([
                'file' => DataPart::fromPath($absoluteImagePath),
            ]);

            $response = $this->httpClient->request('POST', $this->fightModerationUrl, [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
                'timeout' => max(3, $this->fightModerationTimeout),
            ]);

            if ($response->getStatusCode() >= 400) {
                throw new \RuntimeException('Fight moderation service returned HTTP ' . $response->getStatusCode());
            }

            $data = $response->toArray(false);
            $safe = (bool) ($data['safe'] ?? true);
            $reason = (string) ($data['reason'] ?? '');
            $confidence = (float) ($data['confidence'] ?? 0.0);
            return [
                'status' => $safe ? 'SAFE' : 'UNSAFE',
                'reason' => $reason,
                'confidence' => max(0.0, min(1.0, $confidence)),
            ];
        } catch (\Throwable $e) {
            $this->logger->warning('FightImageModerationService::verifyImage error', [
                'message' => $e->getMessage(),
                'url' => $this->fightModerationUrl,
                'fail_open' => $this->fightModerationFailOpen,
            ]);

            if ($this->fightModerationFailOpen) {
                return ['status' => 'UNKNOWN', 'reason' => 'fight_moderation_unavailable', 'confidence' => 0.0];
            }

            return ['status' => 'UNSAFE', 'reason' => 'fight_moderation_unavailable', 'confidence' => 0.0];
        }
    }

    private function looksLikePlaceholder(string $value): bool
    {
        $value = trim($value);
        if ($value === '' || $value === 'changeme') {
            return true;
        }
        if (strlen($value) > 3 && preg_match('/^(.)\1+$/', $value)) {
            return true;
        }
        return false;
    }
}
