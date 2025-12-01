<?php
session_start();
require "../common/DBconnect.php";

// 1. ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2. POSTデータチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['payment-method'])) {
    header('Location: new-pay.php');
    exit;
}

$user_id = $_SESSION['customer']['user_id'];
$payment_method = $_POST['payment-method'];

// 3. 支払い金額と変更モードの取得
$change_mode = $_SESSION['change_info']['mode'] ?? 'new';
$pay_amount = 0;

// ★修正: 金額設定ロジック
if (isset($_SESSION['change_info']['amount'])) {
    $pay_amount = $_SESSION['change_info']['amount']; // payment_updateなら0になる
} else {
    if (isset($_SESSION['package_plan'])) {
        $pay_amount = $_SESSION['package_plan']['totalPrice'];
    } elseif (isset($_SESSION['custom_options'])) {
        $pay_amount = $_SESSION['custom_total_price'];
    }
}

// --- 決済処理 (スタブ) ---
$payment_succeeded = true; 

if (!$payment_succeeded) {
    header('Location: payment-error.php');
    exit;
}

try {
    $db->beginTransaction();

    // ==================================================
    // 1. Ordersテーブルへの登録
    // ==================================================
    // amountが0でも、最新の支払い方法を紐付けるためにOrderを作成します
    $orderSql = $db->prepare("INSERT INTO Orders (user_id, total_amount, status, created_at, updated_at) VALUES (?, ?, 'paid', NOW(), NOW())");
    $orderSql->execute([$user_id, $pay_amount]);
    $order_id = $db->lastInsertId();


    // ==================================================
    // 2. Order_Items (明細) への登録
    // ★修正: 支払い情報の更新のみ(0円)の場合は商品の登録をスキップする
    // ==================================================
    if ($change_mode !== 'payment_update') {
        $itemSql = $db->prepare("INSERT INTO Order_Items (order_id, product_name, category_id, price, quantity, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");

        if (isset($_SESSION['custom_options'])) {
            foreach ($_SESSION['custom_options'] as $item) {
                $itemSql->execute([$order_id, $item['label'], 2, $item['price']]);
            }
        } elseif (isset($_SESSION['package_plan'])) {
            $plan = $_SESSION['package_plan'];
            $itemSql->execute([$order_id, $plan['product_name'], 1, $plan['totalPrice']]);
        }
    }


    // ==================================================
    // 3. Paymentsテーブルへの登録
    // ★ここがご要望の「amountを0にして追加」する部分です
    // ==================================================
    $paymentSql = $db->prepare("INSERT INTO Payments (order_id, amount, payment_date, payment_method, status) VALUES (?, ?, NOW(), ?, 'success')");
    $paymentSql->execute([$order_id, $pay_amount, $payment_method]);


    // ==================================================
    // ★これ以降の処理は「支払い情報の更新のみ」の場合は不要なためスキップします
    // ==================================================
    
    if ($change_mode !== 'payment_update') {

        // ... [既存の予約キャンセル処理] ...
        if ($change_mode === 'upgrade' || $change_mode === 'switch') {
             $cancelResSql = $db->prepare("UPDATE Subscription SET status_id = 2, update_date = NOW() WHERE user_id = ? AND start_date > NOW() AND status_id = 1");
             $cancelResSql->execute([$user_id]);
        }

        // ... [既存の日付計算処理] ...
        if ($change_mode === 'reserve') {
            $current_end_date = $_SESSION['change_info']['current_end_date'];
            $start_dt = new DateTime($current_end_date);
            $start_dt->modify('+1 day');
        } else {
            $start_dt = new DateTime();
        }
        $start_date_sql = $start_dt->format('Y-m-d');

        // 終了日の決定
        $plan_type = $_SESSION['package_plan']['plan_type'] ?? $_SESSION['custom_billing_cycle'] ?? 'monthly';
        $end_dt = clone $start_dt;
        
        if($plan_type === 'triennially') {
            $end_dt->modify('+3 years');
        } elseif($plan_type === 'yearly') {
            $end_dt->modify('+1 year');
        } else {
            $end_dt->modify('+1 month');
        }
        $end_date_sql = $end_dt->format('Y-m-d');

        // ... [既存のSubscription処理] ...
        // (長いので省略しますが、元のコードにある if ($change_mode === 'reserve') ... else { ... } のブロック全体をこのif文の中に入れます)
        
        // 元のコードの「5. Subscription (契約) の処理」全体をここに配置してください
        // ...
        
        // ... [既存のカート情報のクリア] ...
        $cartSql = $db->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
        $cartSql->execute([$user_id]);
        $cart = $cartSql->fetch(PDO::FETCH_ASSOC);
        if ($cart) {
            $db->prepare("DELETE FROM Cart_Items WHERE cart_id = ?")->execute([$cart['cart_id']]);
            $db->prepare("DELETE FROM Cart WHERE cart_id = ?")->execute([$cart['cart_id']]);
        }
    } // --- $change_mode !== 'payment_update' の終了カッコ

    $db->commit();

    // セッションクリア
    unset($_SESSION['custom_options']);
    unset($_SESSION['custom_total_price']);
    unset($_SESSION['custom_billing_cycle']);
    unset($_SESSION['custom_term_start']);
    unset($_SESSION['custom_term_end']);
    unset($_SESSION['package_plan']);
    unset($_SESSION['change_info']); 

    header('Location: pay_complete.php');
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['payment_error'] = "処理中にエラーが発生しました: " . $e->getMessage();
    header('Location: payment-error.php');
    exit;
}
?>