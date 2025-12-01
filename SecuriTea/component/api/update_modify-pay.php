<?php
// component/api/update_modify-pay.php
// ※ DB接続ファイルのパスは実際の環境に合わせて調整してください
//   account.phpと同じ階層のcommon/DBconnect.phpを読み込む場合、階層を2つ上がる必要があります
require_once "../../../common/DBconnect.php"; 

session_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // JSONデータの受け取り
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (!$input) {
        throw new Exception('無効なリクエストです');
    }

    // ログインチェック（セッションの改ざん防止のため、IDはセッションから取得推奨）
    if (!isset($_SESSION['customer']['user_id'])) {
        throw new Exception('ログインしていません');
    }
    $user_id = $_SESSION['customer']['user_id'];

    // 入力値の取得
    $card_brand = $input['card_brand'] ?? '';
    $masked_card_number = $input['masked_card_number'] ?? '';

    // データベース更新
    // Profilesテーブルにカード情報を保存する想定
    $stmt = $db->prepare("
        UPDATE Profiles 
        SET card_brand = :brand, 
            masked_card_number = :masked 
        WHERE user_id = :uid
    ");
    
    $stmt->bindValue(':brand', $card_brand, PDO::PARAM_STR);
    $stmt->bindValue(':masked', $masked_card_number, PDO::PARAM_STR);
    $stmt->bindValue(':uid', $user_id, PDO::PARAM_INT);
    
    $stmt->execute();

    // 成功レスポンス
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // エラーレスポンス
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>