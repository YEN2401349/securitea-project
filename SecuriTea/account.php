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

  // 1. プロフィール情報とメールアドレスを取得
  $sql_user = $pdo->prepare("
      SELECT p.full_name, p.gender, p.phone, u.user_email, p.card_brand, p.masked_card_number
      FROM Profiles p
      JOIN Users u ON p.user_id = u.user_id
      WHERE p.user_id = ?
  ");
  $sql_user->execute([$user_id]);
  $user = $sql_user->fetch(PDO::FETCH_ASSOC);

  // 2. サブスク情報 (LIMIT 1 で最新の1件だけを取得)
  // ★重要: ここで現在の契約(または最新の予約)を1つだけ特定します
  $sql_subscription = $pdo->prepare("SELECT subscription_id, product_id, start_date, end_date, status_id FROM Subscription WHERE user_id = ? ORDER BY create_date DESC LIMIT 1");
  $sql_subscription->execute([$user_id]);
  $subscription = $sql_subscription->fetch(PDO::FETCH_ASSOC);

  // 3. 料金とプラン名の取得 (エラー修正箇所)
  // ★修正: サブクエリを使わず、上で取得した $subscription['subscription_id'] を使います
  $price_val = 0;
  $product_name = '未登録';

  if ($subscription) {
      $target_sub_id = $subscription['subscription_id'];
      $target_prod_id = $subscription['product_id'];

      // (A) カスタムプランの場合 (product_id が 0)
      if ($target_prod_id == 0) {
          $product_name = 'カスタムプラン';
          
          // オプションの合計金額を計算
          $sql_price = $pdo->prepare("
            SELECT SUM(p.price) AS price 
            FROM SubscriptionCustoms s 
            LEFT JOIN Products p ON p.product_id = s.product_id 
            WHERE s.subscription_id = ?
          ");
          $sql_price->execute([$target_sub_id]);
          $res = $sql_price->fetch(PDO::FETCH_ASSOC);
          if ($res && $res['price']) {
              $price_val = (int)$res['price'];
          }
      } 
      // (B) パッケージプランの場合
      else {
          // 商品情報を取得
          $sql_prod = $pdo->prepare("SELECT name, price FROM Products WHERE product_id = ?");
          $sql_prod->execute([$target_prod_id]);
          $prod_res = $sql_prod->fetch(PDO::FETCH_ASSOC);
          
          if ($prod_res) {
              $product_name = $prod_res['name'];
              $price_val = (int)$prod_res['price'];
          }
      }

      // --- 期間に応じた金額計算 (年払いなどの倍率反映) ---
      // 現在の期間(日数)を計算
      $start = new DateTime($subscription['start_date']);
      $end = new DateTime($subscription['end_date']);
      $diff_days = $start->diff($end)->days;

      // 日数に応じて倍率をかける (月額ベースの価格が入っている前提)
      if ($diff_days > 1000) {
          // 3年プラン (25ヶ月分)
          $price_val = $price_val * 25;
      } elseif ($diff_days > 300) {
          // 年間プラン (10ヶ月分)
          $price_val = $price_val * 10;
      } else {
          // 月間プラン (1倍)
          // そのまま
      }
  }

  // 表示用に配列へ格納
  $subscriptionPrice = ["price" => $price_val];


  // 4. 【カスタム専用】契約中のオプション一覧を取得
  $custom_options = []; 
  if ($subscription) {
    $subscription_id = $subscription['subscription_id'];
    $sql_custom = $pdo->prepare("
          SELECT p.name 
          FROM SubscriptionCustoms sc
          JOIN Products p ON sc.product_id = p.product_id
          WHERE sc.subscription_id = ?
      ");
    $sql_custom->execute([$subscription_id]);
    $custom_options = $sql_custom->fetchAll(PDO::FETCH_ASSOC);
  }

  $payment_jp = $user['masked_card_number'] ?
    "{$user['card_brand']} **** **** **** " . substr($user['masked_card_number'], -4) :
    '未登録';

} catch (PDOException $e) {
  echo "エラー：" . htmlspecialchars($e->getMessage());
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
  <link rel="stylesheet" href="css/heder-footer.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <?php require "headerTag.php"; ?>

  <div class="container">
    <main class="content">
      <div class="card">

        <h2>個人情報</h2>
        <span id="user-id" hidden><?= htmlspecialchars($user_id) ?></span>
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
            <?php echo $subscription['end_date'] ?>までご利用いただけます。</h2>
        <?php endif; ?>

        <?php 
          // 今表示しているプランの開始日が未来なら、それは予約プランです
          $is_future = false;
          if ($subscription) {
              $start_dt = new DateTime($subscription['start_date']);
              $now = new DateTime();
              if ($start_dt > $now) {
                  $is_future = true;
                  echo '<h3 style="color: #e65100; margin-bottom:10px;">※次回更新予約分のプランを表示しています</h3>';
              }
          }
        ?>

        <div class="info-row">
          <div class="info-label">利用プラン</div>
          <div class="info-value">
            <?= htmlspecialchars($product_name) ?>
          </div>
        </div>
        <div class="info-row">
          <div class="info-label">料金</div>
          <div class="info-value"><?= number_format($price_val) ?>円</div>
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
        <?php if (!empty($custom_options)): ?>
          <?php if (isset($subscription['status_id']) && $subscription['status_id'] == 2): ?>
            <h2 style="color: red;">こちらは解約済みのオプションです。<br>
              <?php echo $subscription['end_date'] ?>までご利用いただけます。</h2>
          <?php endif; ?>
          <?php foreach ($custom_options as $option): ?>
            <div class="info-row">
              <div class="info-label">オプション</div>
              <div class="info-value"><?= htmlspecialchars($option['name']) ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>オプション未契約</p>
        <?php endif; ?>

        <div class="card-actions">
          <form action="product.php">
            <button class="btn btn-primary">プラン変更</button>
          </form>
          
          <?php if (!$is_future && isset($subscription['status_id']) && $subscription['status_id'] == 1): ?>
              <form action="confirm_cancel.php" method="post">
                <button type="submit" class="btn btn-danger">契約解除</button>
              </form>
          <?php endif; ?>
        </div>

      </div>
    </main>
  </div>
  <?php require "./component/modify-pay.php"; ?>
</body>
</html>