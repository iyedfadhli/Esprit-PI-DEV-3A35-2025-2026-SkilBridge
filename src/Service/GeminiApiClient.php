<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Client HTTP pour l'API Google Gemini 1.5 Flash.
 *
 * Encapsule la communication avec l'API Gemini :
 * - Gestion de la clé API
 * - Construction des requêtes
 * - Parsing des réponses
 * - Gestion des erreurs
 */
class GeminiApiClient
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $geminiApiKey,
    ) {
    }

    /**
     * Envoie un prompt à Gemini 1.5 Flash et retourne la réponse textuelle.
     *
     * @param string $prompt Le prompt complet à envoyer
     * @param float  $temperature Créativité (0.0 = déterministe, 1.0 = créatif)
     * @param int    $maxTokens Nombre max de tokens en sortie
     *
     * @return string La réponse textuelle de Gemini
     *
     * @throws \RuntimeException Si l'appel API échoue
     */
    public function generateContent(
        string $prompt,
        float $temperature = 0.7,
        int $maxTokens = 2048,
    ): string {
        $url = self::API_URL . '?key=' . $this->geminiApiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxTokens,
                'topP' => 0.95,
                'topK' => 40,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE',
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_NONE',
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_NONE',
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_NONE',
                ],
            ],
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                $errorBody = $response->getContent(false);
                $this->logger->error('Gemini API error', [
                    'status' => $statusCode,
                    'body' => $errorBody,
                ]);
                throw new \RuntimeException("Gemini API returned status $statusCode: $errorBody");
            }

            $data = $response->toArray();

            // Extraire le texte de la réponse
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($text === null) {
                $this->logger->warning('Gemini returned empty response', ['data' => $data]);
                throw new \RuntimeException('Gemini API returned an empty response.');
            }

            return $text;

        } catch (\Symfony\Contracts\HttpClient\Exception\ExceptionInterface $e) {
            $this->logger->error('Gemini API request failed', [
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to communicate with Gemini API: ' . $e->getMessage(), 0, $e);
        }
    }
}
