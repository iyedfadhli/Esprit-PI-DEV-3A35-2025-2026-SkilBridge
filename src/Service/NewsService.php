<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NewsService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $newsApiKey,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return array<int,array{title:string,url:string,source:string,publishedAt:string,category:string}>
     */
    public function fetchTopHeadlines(int $perCategory = 5): array
    {
        if ($this->looksLikePlaceholder($this->newsApiKey)) {
            return [];
        }

        $categories = ['business', 'technology'];
        $result = [];
        $seenUrls = [];

        foreach ($categories as $category) {
            try {
                $response = $this->httpClient->request('GET', 'https://newsapi.org/v2/top-headlines', [
                    'query' => [
                        'country' => 'us',
                        'category' => $category,
                        'pageSize' => $perCategory,
                        'apiKey' => $this->newsApiKey,
                    ],
                    'timeout' => 10,
                ]);

                if ($response->getStatusCode() >= 400) {
                    continue;
                }

                $data = $response->toArray(false);
                $articles = $data['articles'] ?? [];
                if (!is_array($articles)) {
                    continue;
                }

                foreach ($articles as $article) {
                    $url = (string) ($article['url'] ?? '');
                    $title = trim((string) ($article['title'] ?? ''));
                    if ($url === '' || $title === '' || isset($seenUrls[$url])) {
                        continue;
                    }

                    $seenUrls[$url] = true;
                    $result[] = [
                        'title' => $title,
                        'url' => $url,
                        'source' => (string) ($article['source']['name'] ?? 'News'),
                        'publishedAt' => (string) ($article['publishedAt'] ?? ''),
                        'category' => $category,
                    ];
                }
            } catch (\Throwable $e) {
                $this->logger->warning('NewsService::fetchTopHeadlines failed', [
                    'category' => $category,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        usort($result, static function (array $a, array $b): int {
            return strcmp($b['publishedAt'], $a['publishedAt']);
        });

        return array_slice($result, 0, max(1, $perCategory * 2));
    }

    private function looksLikePlaceholder(string $value): bool
    {
        $value = trim($value);
        if ($value === '' || $value === 'changeme' || $value === 'YOUR_KEY') {
            return true;
        }

        if (strlen($value) > 3 && preg_match('/^(.)\1+$/', $value)) {
            return true;
        }

        return false;
    }
}
