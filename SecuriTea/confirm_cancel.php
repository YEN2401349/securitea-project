<?php
session_start();
require "../common/DBconnect.php";

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['customer']['user_id'];
$has_reservation = false;

try {
    $pdo = $db;
    
    // 予約(status=5)があるか
    $sql = "SELECT COUNT(*) FROM Subscription WHERE user_id = ? AND start_date > NOW() AND status_id = 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $has_reservation = true;
    }

} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>契約解除の確認</title>
  <link rel="stylesheet" href="css/account.css">
  <link rel="stylesheet" href="css/heder-footer.css">
  <style>
      .warning-box {
          background-color: #fff3cd;
          border: 1px solid #ffeeba;
          color: #856404;
          padding: 15px;
          border-radius: 6px;
          margin: 15px 0;
          text-align: left;
          font-size: 0.95rem;
      }
      .warning-box strong {
          color: #d9534f;
          display: block;
          margin-bottom: 5px;
      }
  </style>
</head>
<body>
  <?php require "headerTag.php"; ?>

  <div class="container">
    <main class="content">
      <div class="card" style="text-align:center; max-width: 600px; margin: 0 auto;">
        <h2>契約解除の確認</h2>
        
        <p>本当に契約を解除（自動更新を停止）しますか？</p>

        <?php if ($has_reservation): ?>
            <div class="warning-box">
                <strong>【重要】次回プラン変更の予約が含まれています</strong>
                <p style="margin:0;">
                    現在、次回のプラン変更予約が設定されています。<br>
                    このまま解除すると、<strong>予約済みのプランについても自動更新が停止されます。</strong><br>
                    <span style="font-size:0.85rem; color:#666;">※お支払い済みの期間終了までは引き続きご利用いただけます。</span>
                </p>
            </div>
        <?php else: ?>
            <p style="color: #666; font-size: 0.9rem;">
                解除後も、現在の契約期間終了まではサービスをご利用いただけます。<br>
                期間終了後の自動課金は行われません。
            </p>
        <?php endif; ?>

        <div class="card-actions" style="justify-content:center; margin-top: 20px;">
          <form action="cancel_process.php" method="post" style="display:inline;">
            <button type="submit" class="btn btn-danger">はい、解除します</button>
          </form>
          <a href="account.php" class="btn btn-secondary">いいえ、戻る</a>
        </div>
      </div>
    </main>
  </div>
  
  <?php require "footer.php"; ?>
</body>
</html>