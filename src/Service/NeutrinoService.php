<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NeutrinoService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $neutrinoUserId,
        private readonly string $neutrinoApiKey,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return array{status: string, confidence: float}
     */
    public function verifyText(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return ['status' => 'SAFE', 'confidence' => 1.0];
        }

        if ($this->looksLikePlaceholder($this->neutrinoUserId) || $this->looksLikePlaceholder($this->neutrinoApiKey)) {
            return ['status' => 'SAFE', 'confidence' => 1.0];
        }

        try {
            $response = $this->httpClient->request('POST', 'https://neutrinoapi.net/bad-word-filter', [
                'body' => [
                    'user-id' => $this->neutrinoUserId,
                    'api-key' => $this->neutrinoApiKey,
                    'content' => $text,
                    'censor-character' => '*',
                ],
                'timeout' => 10,
            ]);
            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);
            if ($statusCode >= 400) {
                $error = (string) ($data['api-error-msg'] ?? $data['error'] ?? 'unknown error');
                throw new \RuntimeException('Neutrino bad-word-filter returned HTTP ' . $statusCode . ' (' . $error . ')');
            }
            $isBad = (bool) ($data['is-bad'] ?? false);

            if (!$isBad && isset($data['bad-words-list']) && is_array($data['bad-words-list']) && count($data['bad-words-list']) > 0) {
                $isBad = true;
            }

            return [
                'status' => $isBad ? 'OFFENSIVE' : 'SAFE',
                'confidence' => $isBad ? 0.95 : 1.0,
            ];
        } catch (\Throwable $e) {
            $this->logger->error('NeutrinoService::verifyText error', [
                'message' => $e->getMessage(),
                'user_id_set' => !$this->looksLikePlaceholder($this->neutrinoUserId),
                'api_key_set' => !$this->looksLikePlaceholder($this->neutrinoApiKey),
            ]);

            return ['status' => 'SAFE', 'confidence' => 0.0];
        }
    }

    private function looksLikePlaceholder(string $value): bool
    {
        if ($value === '' || $value === 'changeme') {
            return true;
        }

        if (strlen($value) > 3 && preg_match('/^(.)\1+$/', $value)) {
            return true;
        }

        return false;
    }
}
