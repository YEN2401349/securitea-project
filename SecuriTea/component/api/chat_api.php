<?php
require_once __DIR__ . '/../../env.php';
loadEnv(__DIR__ . '/../../.env');

$apiKey = $_ENV['API_KEY']; // 從 .env 讀取

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$prompt = $input['prompt'] ?? '';

if (!$prompt) {
    echo json_encode(['error' => 'No prompt']);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

$body = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => curl_error($ch)]);
    exit;
}
curl_close($ch);

echo $response;
