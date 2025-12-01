<?php
session_start();
require "../common/DBconnect.php";

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
      SELECT p.full_name, p.gender, p.phone, u.user_email ,p.card_brand, p.masked_card_number
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

  // 3. 最新の注文情報 (Orders)
  $sql_order = $pdo->prepare("SELECT * FROM Orders WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
  $sql_order->execute([$user_id]);
  $order = $sql_order->fetch(PDO::FETCH_ASSOC);

  $display_plan_name = '未登録'; 
  $display_options = [];         
  $total_items_price = 0; // ★追加: 合計金額計算用

  if ($order && !empty($order['order_id'])) {
      $sql_items = $pdo->prepare("SELECT product_name, price, category_id FROM Order_Items WHERE order_id = ?");
      $sql_items->execute([$order['order_id']]);
      $items = $sql_items->fetchAll(PDO::FETCH_ASSOC);

      foreach ($items as $item) {
          // ★合計金額に加算
          $total_items_price += (int)$item['price'];

          if ($item['category_id'] == 1) {
              $display_plan_name = $item['product_name'];
          } elseif ($item['category_id'] == 2) {
              $display_options[] = $item;
          }
      }
      
      if ($display_plan_name === '未登録' && !empty($display_options)) {
          $display_plan_name = 'カスタムプラン';
      }
  }

  // 4. order_id が取得できたら、Payments テーブルを検索
  $payment_method = '未登録';
  if ($order && !empty($order['order_id'])) {
    $sql_payment = $pdo->prepare("SELECT payment_method, payment_date FROM Payments WHERE order_id = ?");
    $sql_payment->execute([$order['order_id']]);
    $payment = $sql_payment->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
      $payment_method = $payment['payment_method'];
      $date = $payment["payment_date"];
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


  $payment_jp = $user['masked_card_number'] ?
    "{$user['card_brand']} **** **** **** " . substr($user['masked_card_number'], -4) :
    '未登録';
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
        <span id="user-id" hidden><?= htmlspecialchars($user_id)?></span>
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
        <?php if (!empty($subscription) && isset($subscription['status_id']) && $subscription['status_id'] == 2): ?>
          <h2 style="color: red;">こちらは解約済みのセキュリティソフトです。<br>
          <?= htmlspecialchars($subscription['end_date'] ?? '---') ?>までご利用いただけます。</h2>
          <?php endif; ?>
        <div class="info-row">
          <div class="info-label">利用プラン</div>
          <div class="info-value">
            <?= htmlspecialchars($display_plan_name) ?>
          </div>
        </div>
        <div class="info-row">
          <div class="info-label">料金</div>
          <!-- $user['billing_cycle'] 必要に応じて -->
          <div class="info-value"><?= number_format($total_items_price) ?>円</div>
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
          <div id="payment-card" class="info-value"><?= htmlspecialchars($payment_jp) ?></div>
        </div>

          <div class="card-actions">
            <button class="btn btn-primary" id="btn-primary">お支払いの変更</button>
          </div>

        <h2>基本オプション</h2>
        <?php if (!empty($custom_options)): // オプションが1件以上あれば ?>
          <?php if ($subscription['status_id'] == 2): // 解約済みならそれを ?>
          <h2 style="color: red;">こちらは解約済みのオプションです。<br>
          <?= htmlspecialchars($subscription['end_date'] ?? '---') ?>までご利用いただけます。</h2>
          <?php endif; ?>
          <?php foreach ($custom_options as $option): // ループで全部表示 ?>
            <div class="info-row">
              <div class="info-label">オプション</div>
              <div class="info-value"><?= htmlspecialchars($option['name']) ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: // オプションが1件もなければ 念のため ?>
          <p>オプション未契約</p>
        <?php endif; ?>

        <div class="card-actions">
          <form action="product.php">
            <button class="btn btn-primary">プラン変更</button>
          </form>
          <?php
          // 契約中のみ契約解除ボタンを表示
          if (!empty($subscription) && isset($subscription['status_id']) && $subscription['status_id'] != 2):
          ?>
          <form action="confirm_cancel.php" method="post">
            <button type="submit" class="btn btn-danger">契約解除</button>
          </form>
          <?php else: ?>
          <p style="color: red;">契約は既に解約済みです</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="logout-box">
        <form action="logout.php" method="post">
        <button class="btn logout-btn">ログアウト</button>
        </form>
      </div>
    </main>
  </div>
  <?php require "./component/modify-pay.php"; ?>
</body>
</html>