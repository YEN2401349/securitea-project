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
  $user_id = $_SESSION['customer']['user_id'];

  // 1. プロフィール情報とメールアドレスを JOIN で一度に取得
  $sql_user = $pdo->prepare("
      SELECT p.full_name, p.gender, p.phone, u.user_email 
      FROM Profiles p
      JOIN Users u ON p.user_id = u.user_id
      WHERE p.user_id = ?
  ");
  $sql_user->execute([$user_id]);
  $user = $sql_user->fetch(PDO::FETCH_ASSOC);

  // 2. サブスク情報 (check.php で存在確認済みだが、ID取得のために再度実行)
  $sql_subscription = $pdo->prepare("SELECT subscription_id, product_id, start_date, end_date,status_id FROM Subscription WHERE user_id = ? ORDER BY create_date DESC LIMIT 1");
  $sql_subscription->execute([$user_id]);
  $subscription = $sql_subscription->fetch(PDO::FETCH_ASSOC);

  // 3. user_id を使って Orders テーブルから最新の order_id を取得
  $sql_order = $pdo->prepare("SELECT * FROM Orders WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
  $sql_order->execute([$user_id]);
  $order = $sql_order->fetch(PDO::FETCH_ASSOC);

  // 3.5 契約プラン名取得
  $product = null; 
  if ($subscription && !empty($subscription['product_id'])) {
    $sql_product = $pdo->prepare("SELECT name FROM Products WHERE product_id = ?");
    $sql_product->execute([$subscription['product_id']]);
    $product = $sql_product->fetch(PDO::FETCH_ASSOC);
  }

  // 4. order_id が取得できたら、Payments テーブルを検索
  $payment_method = '未登録';
  if ($order && !empty($order['order_id'])) {
    $sql_payment = $pdo->prepare("SELECT payment_method FROM Payments WHERE order_id = ?");
    $sql_payment->execute([$order['order_id']]);
    $payment = $sql_payment->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
      $payment_method = $payment['payment_method'];
    }
  }

  // 5. 【カスタム専用】契約中のオプション一覧を取得
  $custom_options = []; // オプション情報を入れるための配列を初期化
  if ($subscription && !empty($subscription['subscription_id'])) {
      $subscription_id = $subscription['subscription_id'];
      
      // CustomsテーブルとProductsテーブルをJOINして、オプションの「名前」を取得
      $sql_custom = $pdo->prepare("
          SELECT p.name 
          FROM SubscriptionCustoms sc
          JOIN Products p ON sc.product_id = p.product_id
          WHERE sc.subscription_id = ?
      ");
      $sql_custom->execute([$subscription_id]);
      
      // fetchAll ですべてのオプションを取得
      $custom_options = $sql_custom->fetchAll(PDO::FETCH_ASSOC);
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

        <form action="edit_account.php" method="POST">
          <div class="card-actions">
            <button class="btn btn-primary">情報の修正</button>
          </div>
        </form>

        <h2>利用状況</h2>
        <?php if ($subscription['status_id'] == 2): // 解約済みならそれを ?>
          <h2 style="color: red;">こちらは解約済みのセキュリティソフトです。<br>
          ーー月ーー日までご利用いただけます。</h2>
          <!-- 上のところは後々編集する -->
          <?php endif; ?>
        <div class="info-row">
          <div class="info-label">利用プラン</div>
          <div class="info-value"><?= htmlspecialchars($product['name'] ?? '未登録') ?></div>
        </div>
        <div class="info-row">
          <div class="info-label">料金</div>
          <!-- $user['billing_cycle'] 必要に応じて -->
          <div class="info-value"><?= htmlspecialchars($order['total_amount'] ?? '---') ?>円</div>
        </div>
        <div class="info-row">
          <div class="info-label">契約期間</div>
          <div class="info-value">
            <?= htmlspecialchars($subscription['start_date'] ?? '---') . ' ～ ' . htmlspecialchars($subscription['end_date'] ?? '---') ?>
          </div>
        </div>
        <div class="info-row">
          <div class="info-label">次回更新日</div>
          <div class="info-value"><?= htmlspecialchars($subscription['end_date'] ?? '---') ?></div>
        </div>
        <div class="info-row">
          <div class="info-label">お支払い方法</div>
          <div class="info-value"><?= htmlspecialchars($payment_method) ?></div>
        </div>

        <form action="pay-change.php">
          <div class="card-actions">
            <button class="btn btn-primary">お支払いの変更</button>
          </div>
        </form>

        <h2>基本オプション</h2>
        <?php if (!empty($custom_options)): // オプションが1件以上あれば ?>
          <?php if ($subscription['status_id'] == 2): // 解約済みならそれを ?>
          <h2 style="color: red;">こちらは解約済みのオプションです。<br>
          ーー月ーー日までご利用いただけます。</h2>
          <!-- 上のところは後々編集する -->
          <?php endif; ?>
          <?php foreach ($custom_options as $option): // ループで全部表示 ?>
            <div class="info-row">
              <div class="info-label">オプション</div>
              <div class="info-value"><?= htmlspecialchars($option['name']) ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: // オプションが1件もなければ 念のためで一応ね ?>
          <p>オプション未契約</p>
        <?php endif; ?>

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
