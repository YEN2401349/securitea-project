<?php
session_start();
require "DBconnect.php";

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
  header("Location: login.php");
  exit();
}

// ユーザー情報を取得
try {
  $pdo = $db;
  $sql = $pdo->prepare("SELECT * FROM Profiles WHERE user_id = ?");
  $sql->execute([$_SESSION['customer']['user_id']]);
  $user = $sql->fetch(PDO::FETCH_ASSOC);
  $payment_method = '未登録';
  $order = null;   // $order を初期化
  $payment = null; // $payment を初期化

  // まず Orders テーブルから order_id を取得
  $sql_order = $pdo->prepare("SELECT order_id FROM Orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
  // ORDER BY create_at DESC LIMIT 1 状況見てこれをつける
  $sql_order->execute([$_SESSION['customer']['user_id']]);
  $order = $sql_order->fetch(PDO::FETCH_ASSOC);

  if ($order) {
    // order_id があれば Payments から payment_method を取得
    $sql_payment = $pdo->prepare("SELECT payment_method,start_date,end_date FROM Payments WHERE order_id = ?");
    $sql_payment->execute([$order['order_id']]);
    $payment = $sql_payment->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
      $payment_method = $payment['payment_method'];
    }
  }
} catch (PDOException $e) {
  echo "エラー：" . $e->getMessage();
  exit();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>アカウント情報</title>
  <link rel="stylesheet" href="css/account.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <?php require "headerTag.php"; ?>

  <div class="container">
    <main class="content">
      <div class="card">

        <h2>個人情報</h2>
        <div class="info-row">
          <div class="info-label">名前</div>
          <div class="info-value"><?= htmlspecialchars($user['full_name'] ?? '未登録') ?></div>
        </div>

        <div class="info-row">
          <div class="info-label">性別</div>
          <div class="info-value"><?= htmlspecialchars($user['gender'] ?? '未登録') ?></div>
        </div>

        <h2>連絡先情報</h2>
        <div class="info-row">
          <div class="info-label">メール</div>
          <div class="info-value"><?= htmlspecialchars($user['user_email'] ?? '未登録') ?></div>
        </div>
        <div class="info-row">
          <div class="info-label">電話</div>
          <div class="info-value"><?= htmlspecialchars($user['phone'] ?? '未登録') ?></div>
        </div>

        <form action="edit_account.php">
          <div class="card-actions">
            <button class="btn btn-primary">情報の修正</button>
          </div>
        </form>

        <h2>利用状況</h2>
        <div class="info-row">
          <div class="info-label">利用プラン</div>
          <div class="info-value"><?= htmlspecialchars($user['product_id'] ?? '未登録') ?></div>
        </div>
        <div class="info-row">
          <div class="info-label">料金</div>
          <!-- $user['billing_cycle'] 必要に応じて -->
          <div class="info-value"><?= htmlspecialchars($user['plan_price'] ?? '---') ?>円</div>
        </div>
        <div class="info-row">
          <div class="info-label">契約期間</div>
          <div class="info-value">
            <?= htmlspecialchars($payment['start_date'] ?? '---') . ' ～ ' . htmlspecialchars($payment['end_date'] ?? '---') ?>
          </div>
        </div>
        <div class="info-row">
          <div class="info-label">次回更新日</div>
          <div class="info-value"><?= htmlspecialchars($payment['end_date'] ?? '---') ?></div>
        </div>
        <div class="info-row">
          <div class="info-label">お支払い方法</div>
          <div class="info-value"><?= htmlspecialchars($payment_method) ?></div>
        </div>

        <form action="new-pay.php">
          <div class="card-actions">
            <button class="btn btn-primary">お支払いの変更</button>
          </div>
        </form>

        <h2>基本オプション</h2>
        <div class="info-row">
          <div class="info-label">オプション1</div>
          <div class="info-value"><?= htmlspecialchars($user['option1'] ?? '-') ?></div>
        </div>
        <div class="info-row">
          <div class="info-label">オプション2</div>
          <div class="info-value"><?= htmlspecialchars($user['option2'] ?? '-') ?></div>
        </div>
        <div class="info-row">
          <div class="info-label">オプション3</div>
          <div class="info-value"><?= htmlspecialchars($user['option3'] ?? '-') ?></div>
        </div>

        <div class="card-actions">
          <form action="software.php">
            <button class="btn btn-primary">プラン変更</button>
          </form>
          
          <form action="confirm_cancel.php" method="post">
            <button type="submit" class="btn btn-danger">契約解除</button>
          </form>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
