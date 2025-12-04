<?php
session_start();
require "../common/DBconnect.php";

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
  header("Location: login.php");
  exit();
}

try {
  $pdo = $db;
  $user_id = $_SESSION['customer']['user_id'];

  $pdo->beginTransaction();

  // 1. 現在のプラン (status=1) を 解約予約 (status=2) に変更
  $sqlCurrent = "UPDATE Subscription
                 SET status_id = 2, canceled_date = NOW(), update_date = NOW()
                 WHERE user_id = ? AND status_id = 1";
  $stmt = $pdo->prepare($sqlCurrent);
  $stmt->execute([$user_id]);

  // 2. 予約中のプラン (status=5) を 予約解約 (status=6) に変更
  $sqlFuture = "UPDATE Subscription
                SET status_id = 6, canceled_date = NOW(), update_date = NOW()
                WHERE user_id = ? AND status_id = 5";
  $stmt = $pdo->prepare($sqlFuture);
  $stmt->execute([$user_id]);

  $pdo->commit();

  header("Location: cancel_done.php");
  exit();

} catch (PDOException $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  echo "エラー：" . htmlspecialchars($e->getMessage());
  exit();
}
?>