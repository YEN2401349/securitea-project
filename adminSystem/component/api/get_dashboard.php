<?php
include("../DBconnect.php");

try {

    $userStmt = $db->query("SELECT COUNT(*) AS user_count FROM Users WHERE role = 'user'");
    $userCount = $userStmt->fetch(PDO::FETCH_ASSOC);

    $amountStmt = $db->query("
        SELECT SUM(quantity * price) AS total_amount_sum
        FROM Order_Items
    ");
    $totalAmount = $amountStmt->fetch(PDO::FETCH_ASSOC);

    $mailStmt = $db->query("SELECT COUNT(*) AS mail_count FROM Inquiries WHERE parent_id IS NULL AND status = '未対応'");
    $mailCount = $mailStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'userCount' => $userCount['user_count'],
        'totalAmount' => $totalAmount['total_amount_sum'] ?? 0,
        'mailCount' => $mailCount['mail_count']
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
