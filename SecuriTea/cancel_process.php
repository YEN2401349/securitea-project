<?php
session_start();
require "DBconnect.php";

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
  header("Location: login.php");
  exit();
}

try {
  $pdo = $db;

  // 最新の注文（契約）を取得
  $sql = $pdo->prepare("SELECT order_id FROM Orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
  $sql->execute([$_SESSION['customer']['user_id']]);
  $order = $sql->fetch(PDO::FETCH_ASSOC);

  if ($order) {
    // 契約ステータスを「解約済み」に更新（例: status_id = 3）
    $updateOrder = $pdo->prepare("
        UPDATE Orders
        SET status_id = ?, cancelled_at = NOW()
        WHERE order_id = ?
    ");
    $updateOrder->execute([2, $order['order_id']]); // 3 = 解約済み

    // 支払い情報も同様に更新（例: status_id = 3）
    $updatePay = $pdo->prepare("
        UPDATE Payments
        SET status_id = ?, updated_at = NOW()
        WHERE order_id = ?
    ");
    $updatePay->execute([2, $order['order_id']]);
}


  // 完了ページへ
  header("Location: cancel_done.php");
  exit();
} catch (PDOException $e) {
  echo "エラー：" . $e->getMessage();
  exit();
}
