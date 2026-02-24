<?php

namespace App\Service;

use App\Entity\Hackathon;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private string $apiKey;
    private HttpClientInterface $httpClient;

    public function __construct(string $geminiApiKey, HttpClientInterface $httpClient)
    {
        $this->apiKey = $geminiApiKey;
        $this->httpClient = $httpClient;
    }

    /**
     * @return array<array{title: string, content: string}>
     */
    public function generateHackathonTips(Hackathon $hackathon): array
    {
        $prompt = sprintf(
            "Generate 4 professional and inspiring advice cards for a hackathon. 
            Theme: %s. 
            Description: %s. 
            Format the response as a valid JSON array of objects, each with 'title' and 'content' keys. 
            Do not include any other text or markdown formatting markers.",
            $hackathon->getTheme(),
            $hackathon->getDescription()
        );

        try {
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            ]);

            $data = $response->toArray();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '[]';

            // Clean up potentially included markdown
            $text = preg_replace('/^```json\s*|\s*```$/i', '', trim($text));

            return json_decode($text, true) ?: [];
        } catch (\Exception $e) {
            // In a real app we might log this
            return [];
        }
    }
}
