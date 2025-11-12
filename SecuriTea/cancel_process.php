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

  $sql = $pdo->prepare("SELECT subscription_id FROM Subscription WHERE user_id = ? ORDER BY create_date DESC LIMIT 1");
  $sql->execute([$_SESSION['customer']['user_id']]);
  $sub = $sql->fetch(PDO::FETCH_ASSOC);

  if ($sub) {
    // 契約ステータスを「解約済み」に更新（例: status_id = 3）
    $updateOrder = $pdo->prepare("
        UPDATE Subscription
        SET status_id = ?, canceled_date = NOW()
        WHERE subscription_id = ?
    ");
    $updateOrder->execute([2, $sub['subscription_id']]); // 2 = canceled
}
  // 完了ページへ
  header("Location: cancel_done.php");
  exit();
} catch (PDOException $e) {
  echo "エラー：" . $e->getMessage();
  exit();
}
