<?php
session_start();

// JavaScriptからPOSTされたJSONデータを取得
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    // account_check.php が期待するキーでセッションに保存
    $_SESSION['custom_options'] = $data['options'] ?? [];
    $_SESSION['custom_total_price'] = $data['totalPrice'] ?? 0;
    $_SESSION['custom_billing_cycle'] = $data['billingCycle'] ?? 'monthly';
    $_SESSION['custom_term_start'] = $data['termStart'] ?? '';
    $_SESSION['custom_term_end'] = $data['termEnd'] ?? '';

    // 成功レスポンスを返す
    echo json_encode(['status' => 'success']);
} else {
    // データが空だった場合
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
}
exit();
?>