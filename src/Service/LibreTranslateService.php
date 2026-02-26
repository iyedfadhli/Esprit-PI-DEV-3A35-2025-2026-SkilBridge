<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LibreTranslateService
{
    private const API_URL = 'https://api.mymemory.translated.net/get';

    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    /**
     * Translate a single text string using MyMemory API (free, no key required).
     */
    public function translate(string $text, string $sourceLang, string $targetLang): string
    {
        if (trim($text) === '') {
            return $text;
        }

        try {
            $response = $this->httpClient->request('GET', self::API_URL, [
                'query' => [
                    'q'        => $text,
                    'langpair' => $sourceLang . '|' . $targetLang,
                ],
            ]);

            $content = $response->getContent(false);
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $text;
            }

            if (
                isset($data['responseStatus']) &&
                $data['responseStatus'] == 200 &&
                isset($data['responseData']['translatedText'])
            ) {
                return $data['responseData']['translatedText'];
            }
        } catch (\Throwable $e) {
            // Network error, timeout, etc.
        }

        return $text;
    }

    /**
     * Translate an array of strings (one HTTP call per string).
     *
     * @param  string[] $texts
     * @return string[]
     */
    public function translateBatch(array $texts, string $sourceLang, string $targetLang): array
    {
        $results = [];
        foreach ($texts as $key => $text) {
            $results[$key] = $this->translate($text, $sourceLang, $targetLang);
        }
        return $results;
    }
}
