<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class FlowiseGraderService
{
    private HttpClientInterface $http;
    private string $apiHost;
    private string $flowId;
    private ?string $apiKey;

    public function __construct(
        HttpClientInterface $http,
        #[Autowire('%env(FLOWISE_API_HOST)%')] string $apiHost,
        #[Autowire('%env(FLOWISE_GRADE_FLOW_ID)%')] string $flowId,
        #[Autowire('%env(FLOWISE_API_KEY)%')] ?string $apiKey = null
    ) {
        $this->http = $http; // <-- fixed property name
        $this->apiHost = rtrim($apiHost, '/');
        $this->flowId = $flowId;
        $this->apiKey = $apiKey;
    }

    /**
     * Grade a submission against a challenge PDF
     * @param string $challengeUrl
     * @param string $submissionUrl
     * @param array $options Optional keys: 'prompt', 'flowId'
     * @return array
     */
    public function gradeFromFiles(string $challengePath, string $submissionPath, array $options = []): array
    {
        $flowId = $options['flowId'] ?? $this->flowId;
        $prompt = $options['prompt'] ?? 'Grade the student submission PDF against the challenge PDF. Return ONLY JSON with keys: overall_score (0-20), criteria { completeness, accuracy, clarity, structure }, strengths[], weaknesses[], missing_requirements[], final_feedback.';
        $challengeUrl = $options['challengeUrl'] ?? null;
        $submissionUrl = $options['submissionUrl'] ?? null;

        $url = $this->apiHost . '/api/v1/prediction/' . $flowId;

        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            $headers['x-api-key'] = $this->apiKey;
        }
        $submissionContent = base64_encode(file_get_contents($submissionPath));
        $challengeContent = base64_encode(file_get_contents($challengePath));
        $body = [
            'question' => $prompt,
            'overrideConfig' => [
                'pdfFile_0' => [[
                    'data' => 'data:application/pdf;base64,' . $challengeContent,
                    'name' => 'challenge.pdf',
                ]],
                'pdfFile_1' => [[
                    'data' => 'data:application/pdf;base64,' . $submissionContent,
                    'name' => 'submission.pdf',
                ]],
                'fileUrl' => $challengeUrl,
                'fileUrl_0' => $challengeUrl,
                'fileUrl_1' => $submissionUrl,
            ],
            'streaming' => false,
        ];

        $response = $this->http->request('POST', $url, [
            'headers' => $headers,
            'json' => $body,
            'timeout' => 60,
        ]);
        $data = $response->toArray(false);
        return $data['json'] ?? $data;
    }
    public function gradeByTextExtraction(string $challengePath, string $submissionPath, string $appUrl, string $apiKey): array
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();

            $requirementText = $parser->parseFile($challengePath)->getText();
            $submissionText = $parser->parseFile($submissionPath)->getText();
            if (empty(trim($submissionText))) {
                $submissionText = "DEBUG: The PDF parser failed to read the submission file at " . $submissionPath;
            }

            $instruction = "Instruction: You are an academic evaluator. \n                    Task: Grade the submission below against the requirements. \n                    Scale: Provide all scores (overall and criteria) as numbers between 0 and 20.\n                    Example: 15.5, 10, 20.\n                    Output: Return ONLY valid JSON.\n\n";
            $fullPayload = $instruction .
                "REQUIREMENTS:\n" . $requirementText .
                "\n\nSTUDENT WORK:\n" . $submissionText;

            $body = [
                'question' => $fullPayload,
                'chatId' => 'final_fix_' . time()
            ];

            $response = $this->http->request('POST', 'http://localhost:3000/api/v1/prediction/de64765e-9921-4e5e-a168-213de75683bc', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Origin' => rtrim($appUrl, '/'),
                    'Referer' => rtrim($appUrl, '/'),
                    'Authorization' => 'Bearer ' . $apiKey,
                    'X-API-Key' => $apiKey
                ],
                'json' => $body,
                'timeout' => 180
            ]);

            $data = $response->toArray(false);
            $grade = $data['json'] ?? json_decode($data['text'] ?? '{}', true);

            return is_array($grade) ? $grade : [];
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
