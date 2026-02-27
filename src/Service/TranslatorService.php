<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TranslatorService
{
    private HttpClientInterface $httpClient;
    private string $apiUrl;
    private ?string $apiKey;
    private array $fallbackEndpoints = [
        'https://libretranslate.com/translate',
        'https://libretranslate.de/translate',
        'https://translate.astian.org/translate',
    ];

    public function __construct(HttpClientInterface $httpClient, string $apiUrl = 'https://libretranslate.com/translate', ?string $apiKey = null)
    {
        $this->httpClient = $httpClient;
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function translate(string $text, string $target, string $source): string
    {
        if ($text === '' || $text === null) {
            return $text;
        }

        $src = $this->normalizeLang($source);
        $tgt = $this->normalizeLang($target);

        $payload = [
            'q' => $text,
            'source' => $src ?: 'auto',
            'target' => $tgt,
            'format' => 'text',
        ];
        if ($this->apiKey) {
            $payload['api_key'] = $this->apiKey;
        }

        $endpoints = array_unique(array_merge([$this->apiUrl], $this->fallbackEndpoints));

        foreach ($endpoints as $endpoint) {
            try {
                $response = $this->httpClient->request('POST', rtrim($endpoint, '/'), [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => $payload,
                    'timeout' => 10,
                ]);
                $data = $response->toArray(false);
                if (isset($data['translatedText'])) {
                    return (string) $data['translatedText'];
                }
                if (isset($data[0]['translatedText'])) {
                    return (string) $data[0]['translatedText'];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        $mm = $this->translateWithMyMemory($text, $src, $tgt);
        if ($mm !== null && $mm !== '') {
            return $mm;
        }

        return $text;
    }

    public function translateFields(array $fields, string $target, string $source): array
    {
        $flat = [];
        $keys = [];
        foreach ($fields as $key => $value) {
            if ($value !== null && $value !== '') {
                $flat[] = (string) $value;
                $keys[] = $key;
            } else {
                $flat[] = (string) $value;
                $keys[] = $key;
            }
        }

        if (count($flat) === 0) {
            return $fields;
        }

        $src = $this->normalizeLang($source);
        $tgt = $this->normalizeLang($target);

        $payload = [
            'q' => $flat,
            'source' => $src ?: 'auto',
            'target' => $tgt,
            'format' => 'text',
        ];
        if ($this->apiKey) {
            $payload['api_key'] = $this->apiKey;
        }

        $endpoints = array_unique(array_merge([$this->apiUrl], $this->fallbackEndpoints));
        foreach ($endpoints as $endpoint) {
            try {
                $response = $this->httpClient->request('POST', rtrim($endpoint, '/'), [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => $payload,
                    'timeout' => 10,
                ]);
                $data = $response->toArray(false);

                $translated = [];
                if (isset($data[0]) && is_array($data)) {
                    foreach ($data as $idx => $row) {
                        $translated[] = is_array($row) && isset($row['translatedText']) ? (string) $row['translatedText'] : (string) ($flat[$idx] ?? '');
                    }
                } elseif (isset($data['translatedText'])) {
                    $translated[] = (string) $data['translatedText'];
                }

                if (!empty($translated)) {
                    $result = [];
                    foreach ($keys as $i => $k) {
                        $result[$k] = $translated[$i] ?? $fields[$k];
                    }
                    return $result;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        $mmResult = [];
        $hasAny = false;
        foreach ($keys as $i => $k) {
            $translated = $this->translateWithMyMemory($fields[$k] ?? '', $src, $tgt);
            if ($translated !== null && $translated !== '') {
                $mmResult[$k] = $translated;
                $hasAny = true;
            }
        }
        if ($hasAny) {
            foreach ($keys as $i => $k) {
                if (!isset($mmResult[$k])) {
                    $mmResult[$k] = $fields[$k];
                }
            }
            return $mmResult;
        }

        $result = [];
        foreach ($keys as $i => $k) {
            $result[$k] = $this->translate($fields[$k], $tgt, $src);
        }
        return $result;
    }

    private function translateWithMyMemory(string $text, string $source, string $target): ?string
    {
        $q = trim($text ?? '');
        if ($q === '') {
            return $q;
        }
        $src = $source ?: 'auto';
        $tgt = $target;
        $url = 'https://api.mymemory.translated.net/get';
        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => [
                    'q' => $q,
                    'langpair' => $src . '|' . $tgt,
                ],
                'timeout' => 10,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
            $data = $response->toArray(false);
            if (isset($data['responseData']['translatedText'])) {
                return (string) $data['responseData']['translatedText'];
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    private function normalizeLang(string $lang): string
    {
        $l = strtolower(trim($lang));
        return match ($l) {
            'fr', 'fra', 'fr-fr', 'français', 'francais' => 'fr',
            'en', 'eng', 'en-us', 'en-gb', 'anglais', 'ang' => 'en',
            'de', 'ger', 'de-de', 'allemand' => 'de',
            'ar', 'ara', 'ar-ar', 'arabe' => 'ar',
            default => $l,
        };
    }
}

