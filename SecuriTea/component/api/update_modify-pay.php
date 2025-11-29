<?php include("../../../common/DBconnect.php");
try {
    $input = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("UPDATE Profiles SET card_brand = ?,masked_card_number = ?, payment_token = ? WHERE user_id = ?");
    $stmt->execute([
        $input['card_brand'],
        $input['masked_card_number'],
        $input['payment_token'],
        $input['user_id']
    ]);
    // 成功レスポンス
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>