<?php
// api.php

// === 重要：APIキー ===
// 本当は環境変数（getenv('GEMINI_API_KEY')）から読み込むのがベストですが、
// ひとまずここに設定します。このファイルは絶対に公開しないでください。
$apiKey = 'AIzaSyDYuVhc0z89JA4BYW9G8x6mvvT4NnqCboU'; // あなたのAPIキー

// === ステップ2：ここに商品説明とQ&Aデータを記述 ===
$companyInfo = "
SecuriTeaは、2025年に制作されたカスタム可能なサイバーセキュリティです。
";

$qaData = "
Q: 料金プランを教えてください。
A: ライトプラン（月額500円）、スタンダードプラン（月額1,000円）、プレミアムプラン（月額1,500円）がございます。

Q: 無料トライアルはありますか？
A: はい、全てのプランで30日間の無料トライアルをご利用いただけます。

Q: 対応OSを教えてください。
A: Windows、macOS、Android、iOSに対応しています。
";
// ============================================

// JavaScriptから送られてきたJSONデータを取得
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$userMessage = $data['message'] ?? '';

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => 'メッセージがありません']);
    exit;
}

// Gemini APIに送信するプロンプトを構築
$prompt = "会社紹介：{$companyInfo}\n\nQ&A:{$qaData}\n\nユーザーの質問：{$userMessage}。\n\n提供された「会社紹介」と「Q&A（よくある質問とその回答）」の内容のみに基づいてユーザーの質問に回答してください。\nそれ以外の質問にはすべて「申し訳ありませんが、該当する質問が見つかりませんでした」とお答えください。\n以下のルールを厳守してください。\n\n1. 提供された「会社紹介」と「Q&A」の内容のみに基づいて質問に回答してください。\n\n2. 質問がこれらの範囲に該当しない場合は、必ず「申し訳ありませんが、該当する質問が見つかりませんでした」だけを返答し、それ以外の言葉は一切返答しないでください。\n\n3. 回答は必ず簡潔かつ短くしてください。説明や余計な言葉を付け加えないでください。\n";

// Gemini APIのエンドポイント
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

// 送信するデータ
$postData = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ]
];

// cURLセッションを初期化
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// APIリクエスト実行
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// レスポンス処理
if ($httpCode == 200) {
    $responseData = json_decode($response, true);
    $replyText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'エラー：応答を取得できませんでした。';

    // JavaScriptにJSON形式で返却
    header('Content-Type: application/json');
    echo json_encode(['reply' => trim($replyText)]);
} else {
    // エラー処理
    http_response_code(500);
    echo json_encode(['error' => "APIリクエストに失敗しました (HTTP {$httpCode})", 'details' => $response]);
}
?>