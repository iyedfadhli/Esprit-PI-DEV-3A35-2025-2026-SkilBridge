<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

/**
 * Load .env like Symfony does
 */
$dotenv = new Dotenv();
$dotenv->usePutenv(true)->loadEnv(__DIR__.'/.env');

$token = $_ENV['HF_API_TOKEN'] ?? null;
$model = $_ENV['HF_MODEL'] ?? null;

if (!$token || !$model) {
    die("❌ ENV variables not loaded. Check .env\n");
}

echo "Using model: $model\n";

/**
 * Correct Hugging Face Router endpoint
 */
$url = "https://router.huggingface.co/hf-inference/models/".$model;

$payload = [
    "inputs" => "Generate a JSON CV with fields: name, title, skills (array), education (array). Return ONLY JSON.",
    "parameters" => [
        "max_new_tokens" => 150,
        "temperature" => 0.2,
        "return_full_text" => false
    ]
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer ".$token,
        "Content-Type: application/json",
        "Accept: application/json",
        "X-Wait-For-Model: true"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
]);

$response = curl_exec($ch);

if ($response === false) {
    die("❌ Curl error: ".curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP STATUS: $httpCode\n";

/**
 * Save raw response for inspection
 */
file_put_contents(__DIR__.'/debug_body.txt', $response);

echo "Response saved to debug_body.txt\n";

/**
 * Try to extract JSON if present
 */
preg_match('/\{.*\}/s', $response, $matches);

if (isset($matches[0])) {
    $json = preg_replace('/,(\s*[}\]])/', '$1', $matches[0]);
    $decoded = json_decode($json, true);

    if ($decoded) {
        echo "✅ JSON parsed successfully:\n";
        print_r($decoded);
    } else {
        echo "⚠️ JSON found but could not parse.\n";
    }
} else {
    echo "⚠️ No JSON detected (model returned plain text).\n";
}