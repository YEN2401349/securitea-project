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

  // 現在有効(status=1)なものをすべて解約予約(status=2)

  $sql = "UPDATE Subscription
          SET status_id = 2,
              canceled_date = NOW(),
              update_date = NOW()
          WHERE user_id = ?
          AND status_id = 1";

  $stmt = $pdo->prepare($sql);
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