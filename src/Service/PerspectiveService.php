<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PerspectiveService
{
    private const API_URL = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $perspectiveApiKey,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return array{
     *   status: string,
     *   confidence: float,
     *   scores: array<string,float>,
     *   triggers: string[]
     * }
     */
    public function verifyText(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [
                'status' => 'SAFE',
                'confidence' => 1.0,
                'scores' => [],
                'triggers' => [],
            ];
        }

        if ($this->looksLikePlaceholder($this->perspectiveApiKey)) {
            return [
                'status' => 'SAFE',
                'confidence' => 1.0,
                'scores' => [],
                'triggers' => [],
            ];
        }

        $attributes = [
            'TOXICITY',
            'SEVERE_TOXICITY',
            'IDENTITY_ATTACK',
            'THREAT',
            'INSULT',
            'PROFANITY',
            'SEXUALLY_EXPLICIT',
        ];

        $requestBody = [
            'comment' => ['text' => $text],
            'languages' => ['en'],
            'requestedAttributes' => array_fill_keys($attributes, (object) []),
            'doNotStore' => true,
        ];

        try {
            $response = $this->httpClient->request('POST', self::API_URL . '?key=' . $this->perspectiveApiKey, [
                'json' => $requestBody,
                'timeout' => 10,
            ]);

            if ($response->getStatusCode() >= 400) {
                throw new \RuntimeException('Perspective returned HTTP ' . $response->getStatusCode());
            }

            $data = $response->toArray(false);
            $scores = $this->extractScores($data);
            $triggers = $this->evaluateTriggers($scores);
            $policyMatches = $this->policyMatches($text);
            $allTriggers = array_values(array_unique(array_merge($triggers, $policyMatches)));

            return [
                'status' => $allTriggers === [] ? 'SAFE' : 'UNSAFE',
                'confidence' => $allTriggers === [] ? 1.0 : max($this->maxScore($scores), $policyMatches === [] ? 0.0 : 0.99),
                'scores' => $scores,
                'triggers' => $allTriggers,
            ];
        } catch (\Throwable $e) {
            $this->logger->error('PerspectiveService::verifyText error', [
                'message' => $e->getMessage(),
                'api_key_set' => !$this->looksLikePlaceholder($this->perspectiveApiKey),
            ]);

            if (($_ENV['APP_ENV'] ?? 'prod') === 'dev') {
                throw new \RuntimeException('Perspective verifyText error: ' . $e->getMessage(), 0, $e);
            }

            return [
                'status' => 'SAFE',
                'confidence' => 0.0,
                'scores' => [],
                'triggers' => [],
            ];
        }
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,float>
     */
    private function extractScores(array $payload): array
    {
        $scores = [];
        $attributeScores = $payload['attributeScores'] ?? [];
        if (!is_array($attributeScores)) {
            return $scores;
        }

        foreach ($attributeScores as $attribute => $meta) {
            $value = (float) ($meta['summaryScore']['value'] ?? 0.0);
            $scores[(string) $attribute] = max(0.0, min(1.0, $value));
        }

        return $scores;
    }

    /**
     * @param array<string,float> $scores
     * @return string[]
     */
    private function evaluateTriggers(array $scores): array
    {
        $thresholds = [
            'SEVERE_TOXICITY' => 0.55,
            'IDENTITY_ATTACK' => 0.50,
            'THREAT' => 0.45,
            'SEXUALLY_EXPLICIT' => 0.55,
            'TOXICITY' => 0.75,
            'INSULT' => 0.70,
            'PROFANITY' => 0.80,
        ];

        $triggers = [];
        foreach ($thresholds as $attribute => $threshold) {
            if (($scores[$attribute] ?? 0.0) >= $threshold) {
                $triggers[] = $attribute;
            }
        }

        // If multiple medium-risk signals appear together, treat as unsafe.
        $mediumSignals = ['TOXICITY', 'INSULT', 'IDENTITY_ATTACK', 'SEXUALLY_EXPLICIT', 'THREAT'];
        $mediumCount = 0;
        foreach ($mediumSignals as $signal) {
            if (($scores[$signal] ?? 0.0) >= 0.45) {
                $mediumCount++;
            }
        }
        if ($mediumCount >= 2) {
            $triggers[] = 'COMBINED_MEDIUM_RISK';
        }

        return $triggers;
    }

    /**
     * @return string[]
     */
    private function policyMatches(string $text): array
    {
        $matches = [];
        $patterns = [
            'SEXUAL_HARASSMENT_PATTERN' => [
                '/\b(my|our|the)\s+(teacher|student|boss|coworker|classmate)\s+is\s+(hot|sexy)\b/i',
                '/\b(i|we)\s+(can\'t|cannot|cant)\s+stop\s+thinking\b.*\b(do|doing)\b.*\b(to)\b/i',
                '/\b(i|we)\s+want\s+to\s+(do|touch|use|force)\b.*\b(her|him|them|teacher|student|girl|boy)\b/i',
            ],
            'HATE_SPEECH_PATTERN' => [
                '/\b(ching\s*chong|ching\s*chang|ching\s*chag|chink|gook|kike|spic)\b/i',
                '/\b(asian|black|white|jew|muslim|arab|indian|mexican|gay|trans|women|men)\b.{0,40}\b(suck|stupid|inferior|dirty|animals?)\b/i',
                '/\b(kill|burn|rape|lynch|exterminate)\b.{0,40}\b(them|him|her|those|people|group)\b/i',
            ],
        ];

        foreach ($patterns as $label => $group) {
            foreach ($group as $pattern) {
                if (preg_match($pattern, $text) === 1) {
                    $matches[] = $label;
                    break;
                }
            }
        }

        return $matches;
    }

    /**
     * @param array<string,float> $scores
     */
    private function maxScore(array $scores): float
    {
        if ($scores === []) {
            return 0.0;
        }

        return max($scores);
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
