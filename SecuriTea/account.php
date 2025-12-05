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

  $sql_user = $pdo->prepare("
      SELECT p.full_name, p.gender, p.phone, u.user_email, p.card_brand, p.masked_card_number
      FROM Profiles p
      JOIN Users u ON p.user_id = u.user_id
      WHERE p.user_id = ?
  ");
  $sql_user->execute([$user_id]);
  $user = $sql_user->fetch(PDO::FETCH_ASSOC);

  $sql_subs = $pdo->prepare("SELECT * FROM Subscription WHERE user_id = ? AND status_id IN (1, 2, 5, 6) ORDER BY start_date ASC");
  $sql_subs->execute([$user_id]);
  $all_subs = $sql_subs->fetchAll(PDO::FETCH_ASSOC);

  $current_sub = null;
  $reserved_sub = null;
  $now = new DateTime();
  $today = $now->format('Y-m-d');

  // 振り分け
  foreach ($all_subs as $sub) {
      if ($sub['start_date'] <= $today && $sub['end_date'] >= $today) {
          if ($current_sub === null || ($current_sub['status_id'] == 2 && $sub['status_id'] == 1)) {
              $current_sub = $sub; 
          }    
      } elseif ($sub['start_date'] > $today) {
          $reserved_sub = $sub; 
      }
  }

  $subscription = null;
  $is_future_main = false;

  if ($current_sub) {
      $subscription = $current_sub;
  } elseif ($reserved_sub) {
      $subscription = $reserved_sub;
      $reserved_sub = null;
      $is_future_main = true;
  } else {
      if (!empty($all_subs)) {
          $subscription = end($all_subs);
      }
  }


  // 表示用
  $price_val = 0;
  $product_name = '未登録';

  if ($subscription) {
      $target_sub_id = $subscription['subscription_id'];
      $target_prod_id = $subscription['product_id'];

      // カスタムプラン
      if ($target_prod_id == 0) {
          $product_name = 'カスタムプラン';
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
      // パッケージプラン
      else {
          $sql_prod = $pdo->prepare("SELECT name, price FROM Products WHERE product_id = ?");
          $sql_prod->execute([$target_prod_id]);
          $prod_res = $sql_prod->fetch(PDO::FETCH_ASSOC);
          if ($prod_res) {
              $product_name = $prod_res['name'];
              $price_val = (int)$prod_res['price'];
          }
      }

      // 期間の計算
      $start = new DateTime($subscription['start_date']);
      $end = new DateTime($subscription['end_date']);
      $diff_days = $start->diff($end)->days;
      if ($diff_days > 1000) $price_val *= 25; // 3年
      elseif ($diff_days > 300) $price_val *= 10; // 1年
  }



  // 予約
  $reserve_info = null;
  if ($reserved_sub) {
      $r_name = "";
      if ($reserved_sub['product_id'] == 0) {
          $r_name = "カスタムプラン";
      } else {
          $stmt = $pdo->prepare("SELECT name FROM Products WHERE product_id = ?");
          $stmt->execute([$reserved_sub['product_id']]);
          $r_res = $stmt->fetch();
          $r_name = $r_res['name'] ?? "パッケージプラン";
      }
      $reserve_info = [
          'name' => $r_name,
          'start' => $reserved_sub['start_date'],
          'end' => $reserved_sub['end_date'],
          'status' => $reserved_sub['status_id']
      ];
  }


  // オプション
  $custom_options = []; 
  if ($subscription) {
    $sql_custom = $pdo->prepare("
          SELECT p.name 
          FROM SubscriptionCustoms sc
          JOIN Products p ON sc.product_id = p.product_id
          WHERE sc.subscription_id = ?
      ");
    $sql_custom->execute([$subscription['subscription_id']]);
    $custom_options = $sql_custom->fetchAll(PDO::FETCH_ASSOC);
  }

  // =支払い方法
  $payment_jp = '未登録';

  if (!empty($user['masked_card_number'])) {
      if (!empty($user['card_brand'])) {
          $payment_jp = $user['card_brand'] . " **** **** **** " . substr($user['masked_card_number'], -4);
      }
      else {
          $payment_jp = $user['masked_card_number'];
      }
  }

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

        <?php if ($reserve_info): ?>
            <?php if ($reserve_info['status'] == 6): ?>
                <div class="reserve-banner" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24;">
                    <i class="fas fa-exclamation-circle" style="color: #dc3545;"></i>
                    <div class="reserve-content">
                        <h3 style="color: #721c24;">次回予約プラン（自動更新停止済み）</h3>
                        <p>
                            <strong><?= htmlspecialchars($reserve_info['name']) ?></strong> が 
                            <strong><?= htmlspecialchars($reserve_info['start']) ?></strong> から開始され、期間終了までご利用いただけます。<br>
                            <span style="font-size: 0.85rem;">※期間終了後の自動更新は行われません。</span>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="reserve-banner">
                    <i class="fas fa-clock"></i>
                    <div class="reserve-content">
                        <h3>次回プラン変更の予約済み</h3>
                        <p>
                            <strong><?= htmlspecialchars($reserve_info['name']) ?></strong> が 
                            <strong><?= htmlspecialchars($reserve_info['start']) ?></strong> から開始されます。
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($is_future_main): ?>
             <h3 style="color: #e65100;">※開始待ちのプランを表示しています</h3>
        <?php elseif (!empty($subscription) && isset($subscription['status_id']) && $subscription['status_id'] == 2): ?>
             <h3 style="color: red;">こちらは自動更新停止済みのプランです。<br>
             <?php echo $subscription['end_date'] ?>までご利用いただけます。</h3>
        <?php endif; ?>

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
          
          <?php 
          // 契約中(status=1)の場合のみ解約ボタンを表示
          // ※解約済み(status=2)の場合は非表示
          if (!$is_future_main && isset($subscription['status_id']) && $subscription['status_id'] == 1): 
          ?>
              <form action="confirm_cancel.php" method="post">
                <button type="submit" class="btn btn-danger">契約解除</button>
              </form>
          <?php elseif (isset($subscription['status_id']) && $subscription['status_id'] == 2): ?>
              <p style="color: red;">自動更新は停止されています</p>
          <?php endif; ?>
        </div>
        
      </div>
    </main>
  </div>
  <?php require "./component/modify-pay.php"; ?>
</body>
</html>