<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

class AIgeneratorService
{
    private HttpClientInterface $httpClient;
    private ?string $apiUrl;
    private ?string $apiKey;
    private ?string $model;
    private ?LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, ?string $apiUrl = null, ?string $apiKey = null, ?string $model = null, ?LoggerInterface $logger = null)
    {
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl ?: getenv('AI_API_URL') ?: 'https://api-inference.huggingface.co/v1/chat/completions';
        $this->apiKey = $apiKey ?: getenv('HF_API_TOKEN') ?: getenv('AI_API_KEY') ?: null;
        $envModel = $model ?: getenv('HF_MODEL') ?: getenv('AI_MODEL') ?: 'Qwen/Qwen2.5-0.5B-Instruct';
        $this->model = $this->normalizeModelName($envModel);
        $this->logger = $logger;
    }

    public function generateCvData(string $language, string $jobTitle, string $description): array
    {
        $useFallback = !$this->apiUrl || !$this->model || !$this->apiKey;
        if ($useFallback) {
            return $this->localSynthesizeCvData($language, $jobTitle, $description);
        }
        try {
            $prompt = $this->buildPrompt($language, $jobTitle, $description);
            $json = $this->callAi($prompt);
            return $this->parseAndValidate($json);
        }
        catch (\Throwable $e) {
            throw $e;
        }
    }

    private function buildPrompt(string $language, string $jobTitle, string $description): string
    {
        $lang = $this->normalizeLanguage($language);
        $schema = <<<JSON
{
  "cv": { "nomCv": "string (<= 30 chars)", "summary": "string (<= 1000 chars)", "langue": "Francais|Anglais" },
  "experiences": [
    { "job_title": "string (<=30)", "company": "string (<=30)", "location": "string (<=255)", "start_date": "YYYY-MM[-DD]", "end_date": "YYYY-MM[-DD] or null", "currently_working": true|false, "description": "string" }
  ],
  "educations": [
    { "degree": "string (<=30)", "field_of_study": "string (<=40)", "school": "string (<=30)", "city": "string (<=40)", "start_date": "YYYY-MM[-DD]", "end_date": "YYYY-MM[-DD]", "description": "string" }
  ],
  "skills": [
    { "nom": "string (<=35)", "type": "tech|soft", "level": "Debutant|Intermediaire|Avance" }
  ]
}
JSON;

        $style = <<<TXT
Rewrite and improve the user's text; never copy verbatim. Paraphrase. Use short, clear sentences. Keep the CV concise, consistent, and professional. Produce 3–6 experiences, 1–3 educations, and 6–12 skills when content allows. Align responsibilities with the target job. Remove redundancies and filler.
TXT;

        $prompt = "Task: Convert the candidate's unstructured background into a structured CV in {$lang}.\n"
            . "Target job title: {$jobTitle}.\n"
            . "{$style}\n"
            . "Rules:\n"
            . "- Return JSON ONLY, no markdown, no commentary.\n"
            . "- Start the response with '{' and end with '}'.\n"
            . "- Do not include triple backticks or explanations.\n"
            . "- Follow this exact JSON schema:\n{$schema}\n"
            . "- Dates must be ISO (YYYY-MM or YYYY-MM-DD). Use null for unknown end_date.\n"
            . "- Respect field max lengths.\n"
            . "Candidate background:\n{$description}";
        return $prompt;
    }

    private function callAi(string $prompt): string
    {
        $this->model = $this->extractModelId($this->model ?: 'Qwen/Qwen2.5-7B-Instruct');

        // Strategy 1: OpenAI-compatible Chat Completions (Stable & modern)
        try {
            $apiUrl = 'https://router.huggingface.co/v1/chat/completions';
            $response = $this->httpClient->request('POST', $apiUrl, [
                'headers' => array_filter([
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->apiKey ? ('Bearer ' . $this->apiKey) : null,
                ]),
                'json' => [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a professional CV assistant. Return ONLY valid JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 1500,
                ],
                'timeout' => 90,
            ]);
            $data = $response->toArray(false);
            if (isset($data['choices'][0]['message']['content'])) {
                return (string)$data['choices'][0]['message']['content'];
            }
        }
        catch (\Throwable $e) {
            if ($this->logger) {
                $this->logger->warning('Strategy 1 failed: ' . $e->getMessage(), ['url' => $apiUrl]);
            }
        // Fall through to Strategy 2 if Strategy 1 fails
        }

        // Strategy 2: Direct Model Inference API (Universal fallback)
        try {
            $apiUrl = 'https://api-inference.huggingface.co/models/' . $this->model;
            $response = $this->httpClient->request('POST', $apiUrl, [
                'headers' => array_filter([
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->apiKey ? ('Bearer ' . $this->apiKey) : null,
                ]),
                'json' => [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_new_tokens' => 1200,
                        'temperature' => 0.1,
                        'return_full_text' => false
                    ],
                    'options' => ['wait_for_model' => true]
                ],
                'timeout' => 90,
            ]);
            $data = $response->toArray(false);
            if (isset($data[0]['generated_text'])) {
                return (string)$data[0]['generated_text'];
            }
            if (isset($data['generated_text'])) {
                return (string)$data['generated_text'];
            }

            if ($this->logger) {
                $this->logger->error('Unexpected AI response shape', ['data' => $data]);
            }
            throw new \RuntimeException('Unexpected AI response shape');
        }
        catch (\Throwable $e) {
            throw new \RuntimeException('AI Communication Error: ' . $e->getMessage());
        }
    }

    private function extractModelId(string $m): string
    {
        $m = trim($m);
        if (str_contains($m, 'huggingface.co/') && str_contains($m, '/models/')) {
            $parts = explode('/models/', $m);
            return end($parts);
        }
        if (str_contains($m, 'huggingface.co/')) {
            $parts = explode('huggingface.co/', $m);
            return end($parts);
        }
        return $m;
    }

    private function normalizeModelName(?string $m): string
    {
        $m = (string)($m ?? '');
        $hash = strpos($m, '#');
        if ($hash !== false) {
            $m = substr($m, 0, $hash);
        }
        $m = trim($m);
        return $m;
    }

    private function parseAndValidate(string $json): array
    {
        $json = trim($json);
        $json = trim($json, "\xEF\xBB\xBF");
        if (str_starts_with($json, '```')) {
            $json = preg_replace('/^```(?:json)?/i', '', $json);
            $json = preg_replace('/```$/', '', $json);
            $json = trim($json);
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $extracted = $this->extractJsonObject($json);
            if ($extracted !== null) {
                $data = json_decode($extracted, true);
            }
            if (!is_array($data)) {
                $fixed = $this->minorJsonRepair($json);
                $data = json_decode($fixed, true);
            }
            if (!is_array($data)) {
                if ($this->logger) {
                    $this->logger->error('AI returned invalid JSON', ['snippet' => mb_substr($json, 0, 500)]);
                }
                throw new \RuntimeException('AI returned invalid JSON');
            }
        }

        $cv = $data['cv'] ?? [];
        $experiences = $data['experiences'] ?? [];
        $educations = $data['educations'] ?? [];
        $skills = $data['skills'] ?? [];

        $cvNom = $this->sanitizeString($cv['nomCv'] ?? 'Mon CV');
        $cvSummary = $this->sanitizeText($cv['summary'] ?? '');
        $cvLang = $this->normalizeCvLang($cv['langue'] ?? 'Francais');

        $expOut = [];
        foreach (is_array($experiences) ? $experiences : [] as $e) {
            $expOut[] = [
                'job_title' => $this->limitLength($this->sanitizeString($e['job_title'] ?? 'Experience'), 30),
                'company' => $this->limitLength($this->sanitizeString($e['company'] ?? 'Company'), 30),
                'location' => $this->limitLength($this->sanitizeString($e['location'] ?? ''), 255),
                'start_date' => $this->sanitizeDate($e['start_date'] ?? null),
                'end_date' => $this->sanitizeDate($e['end_date'] ?? null),
                'currently_working' => (bool)($e['currently_working'] ?? false),
                'description' => $this->sanitizeText($e['description'] ?? ''),
            ];
        }

        $eduOut = [];
        foreach (is_array($educations) ? $educations : [] as $ed) {
            $sd = $this->sanitizeDate($ed['start_date'] ?? null) ?: date('Y-m-01');
            $edate = $this->sanitizeDate($ed['end_date'] ?? null) ?: date('Y-m-01');
            $eduOut[] = [
                'degree' => $this->limitLength($this->sanitizeString($ed['degree'] ?? 'Diplome'), 30),
                'field_of_study' => $this->limitLength($this->sanitizeString($ed['field_of_study'] ?? ''), 40),
                'school' => $this->limitLength($this->sanitizeString($ed['school'] ?? 'Ecole'), 30),
                'city' => $this->limitLength($this->sanitizeString($ed['city'] ?? ''), 40),
                'start_date' => $sd,
                'end_date' => $edate,
                'description' => $this->sanitizeText($ed['description'] ?? ''),
            ];
        }

        $skillOut = [];
        foreach (is_array($skills) ? $skills : [] as $sk) {
            $skillOut[] = [
                'nom' => $this->limitLength($this->sanitizeString($sk['nom'] ?? 'Skill'), 35),
                'type' => $this->limitLength($this->sanitizeString($sk['type'] ?? 'tech'), 20),
                'level' => $this->limitLength($this->sanitizeString($sk['level'] ?? 'Intermediaire'), 30),
            ];
        }

        return [
            'cv' => [
                'nomCv' => $this->limitLength($cvNom, 30),
                'summary' => $this->limitLength($cvSummary, 1000),
                'langue' => $cvLang,
            ],
            'experiences' => $expOut,
            'educations' => $eduOut,
            'skills' => $skillOut,
        ];
    }

    private function extractJsonObject(string $text): ?string
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $candidate = substr($text, $start, $end - $start + 1);
        return trim($candidate);
    }

    private function minorJsonRepair(string $text): string
    {
        $t = preg_replace('/,\\s*([\\]}])/m', '$1', $text);
        $t = preg_replace('/(\\r?\\n)+/m', "\n", $t);
        return (string)$t;
    }

    private function localSynthesizeCvData(string $language, string $jobTitle, string $description): array
    {
        $cvLang = $this->normalizeCvLang($language);
        $title = $this->limitLength($this->sanitizeString($jobTitle ?: 'Profil'), 30);
        $summary = $this->limitLength($this->sanitizeText($description ?: ''), 1000);
        $keywords = $this->extractKeywords($description);
        $skills = [];
        foreach (array_slice($keywords, 0, 8) as $kw) {
            $skills[] = [
                'nom' => $this->limitLength($kw, 35),
                'type' => in_array(strtolower($kw), ['communication', 'leadership', 'teamwork']) ? 'soft' : 'tech',
                'level' => 'Intermediaire',
            ];
        }
        if (empty($skills)) {
            $skills = [
                ['nom' => 'Communication', 'type' => 'soft', 'level' => 'Intermediaire'],
                ['nom' => 'Organisation', 'type' => 'soft', 'level' => 'Intermediaire'],
            ];
        }
        $exp = [
            [
                'job_title' => $this->limitLength($title, 30),
                'company' => 'Entreprise',
                'location' => '',
                'start_date' => date('Y-01-01'),
                'end_date' => null,
                'currently_working' => true,
                'description' => $summary ? mb_substr($summary, 0, 300) : 'Missions principales en lien avec le poste visé.',
            ],
        ];
        $edu = [
            [
                'degree' => 'Diplome',
                'field_of_study' => '',
                'school' => 'Ecole',
                'city' => '',
                'start_date' => date('Y-01-01'),
                'end_date' => date('Y-12-01'),
                'description' => '',
            ],
        ];
        return [
            'cv' => [
                'nomCv' => $this->limitLength('CV - ' . $title, 30),
                'summary' => $summary,
                'langue' => $cvLang,
            ],
            'experiences' => $exp,
            'educations' => $edu,
            'skills' => $skills,
        ];
    }

    private function sanitizeString(?string $s): string
    {
        $s = (string)($s ?? '');
        return trim(preg_replace('/\\s+/', ' ', $s));
    }

    private function sanitizeText(?string $s): string
    {
        $s = (string)($s ?? '');
        return trim($s);
    }

    private function extractKeywords(?string $text): array
    {
        $t = strtolower((string)($text ?? ''));
        $t = preg_replace('/[^a-z0-9\\s\\-\\+\\.#]/u', ' ', $t);
        $parts = preg_split('/\\s+/', (string)$t, -1, PREG_SPLIT_NO_EMPTY);
        $stop = ['et', 'de', 'la', 'le', 'les', 'des', 'du', 'un', 'une', 'en', 'pour', 'avec', 'sur', 'dans', 'par', 'au', 'aux', 'the', 'a', 'an', 'to', 'of', 'and', 'or', 'on', 'at', 'from', 'my', 'mes', 'mon', 'ma'];
        $freq = [];
        foreach ($parts as $p) {
            if (strlen($p) < 3)
                continue;
            if (in_array($p, $stop))
                continue;
            $freq[$p] = ($freq[$p] ?? 0) + 1;
        }
        arsort($freq);
        return array_keys($freq);
    }

    private function limitLength(string $s, int $max): string
    {
        if (mb_strlen($s) <= $max) {
            return $s;
        }
        return mb_substr($s, 0, $max);
    }

    private function sanitizeDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }
        $date = trim($date);
        if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $date)) {
            return $date;
        }
        if (preg_match('/^\\d{4}-\\d{2}$/', $date)) {
            return $date . '-01';
        }
        if (preg_match('/^\\d{4}$/', $date)) {
            return $date . '-01-01';
        }
        return null;
    }

    private function normalizeLanguage(string $lang): string
    {
        $l = strtolower($lang);
        if (in_array($l, ['french', 'fr', 'francais', 'français'])) {
            return 'French';
        }
        if (in_array($l, ['english', 'en', 'anglais', 'ang'])) {
            return 'English';
        }
        if (in_array($l, ['dutch', 'nl', 'nederlands', 'néerlandais'])) {
            return 'Dutch';
        }
        return 'French';
    }

    private function normalizeCvLang(string $lang): string
    {
        $l = strtolower($lang);
        if (in_array($l, ['fr', 'french', 'francais', 'français'])) {
            return 'Francais';
        }
        if (in_array($l, ['en', 'english', 'anglais'])) {
            return 'Anglais';
        }
        if (in_array($l, ['nl', 'dutch', 'nederlands', 'néerlandais'])) {
            return 'Anglais';
        }
        return 'Francais';
    }
}
