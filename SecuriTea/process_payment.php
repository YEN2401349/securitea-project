<?php
session_start();
require "../common/DBconnect.php";

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['payment-method'])) {
    header('Location: new-pay.php');
    exit;
}

$user_id = $_SESSION['customer']['user_id'];
$payment_method = $_POST['payment-method'];
// credit_card, paypal, bank_transfer

$change_mode = $_SESSION['change_info']['mode'] ?? 'new';
$pay_amount = 0;

if (isset($_SESSION['change_info']['amount'])) {
    $pay_amount = $_SESSION['change_info']['amount'];
} else {
    if (isset($_SESSION['package_plan'])) {
        $pay_amount = $_SESSION['package_plan']['totalPrice'];
    } elseif (isset($_SESSION['custom_options'])) {
        $pay_amount = $_SESSION['custom_total_price'];
    }
}

$payment_succeeded = true; 

if (!$payment_succeeded) {
    header('Location: payment-error.php');
    exit;
}

try {
    $db->beginTransaction();

    // profiles
    $card_brand_val = null;
    $masked_number_val = '';

    if ($payment_method === 'credit_card') {
        $card_brand_val = $_POST['card-type'] ?? '';
        $raw_number = $_POST['card_number'] ?? '';
        $only_num = preg_replace('/[^0-9]/', '', $raw_number);
        $masked_number_val = substr($only_num, -4);
        if (empty($masked_number_val)) $masked_number_val = '****';
    } elseif ($payment_method === 'paypal') {
        $masked_number_val = 'PayPal';
    } elseif ($payment_method === 'bank_transfer') {
        $masked_number_val = '銀行引き落とし';
    }

    $profSql = $db->prepare("UPDATE Profiles SET card_brand = ?, masked_card_number = ? WHERE user_id = ?");
    $profSql->execute([$card_brand_val, $masked_number_val, $user_id]);
    
    // Orders
    $orderSql = $db->prepare("INSERT INTO Orders (user_id, total_amount, status, created_at, updated_at) VALUES (?, ?, 'paid', NOW(), NOW())");
    $orderSql->execute([$user_id, $pay_amount]);
    $order_id = $db->lastInsertId();

    // Order_Items
    $itemSql = $db->prepare("INSERT INTO Order_Items (order_id, product_name, category_id, price, quantity, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");

    if (isset($_SESSION['custom_options'])) {
        foreach ($_SESSION['custom_options'] as $item) {
            $itemSql->execute([$order_id, $item['label'], 2, $item['price']]);
        }
    } elseif (isset($_SESSION['package_plan'])) {
        $plan = $_SESSION['package_plan'];
        $itemSql->execute([$order_id, $plan['product_name'], 1, $plan['totalPrice']]);
    }

    // Payments
    $paymentSql = $db->prepare("INSERT INTO Payments (order_id, amount, payment_date, payment_method, status) VALUES (?, ?, NOW(), ?, 'success')");
    $paymentSql->execute([$order_id, $pay_amount, $payment_method]);


    // 日付計算
    $start_date_sql = "";
    $end_date_sql = "";
    $today = new DateTime();

    // 開始日
    if ($change_mode === 'reserve') {
        // 予約: 現在の終了日の翌日から
        $current_end_date = $_SESSION['change_info']['current_end_date'];
        $start_dt = new DateTime($current_end_date);
        $start_dt->modify('+1 day');
        $start_date_sql = $start_dt->format('Y-m-d');
    } else {
        // Upgrade / Switch / New: 今日から
        $start_date_sql = $today->format('Y-m-d');
    }

    // 終了日
    if ($change_mode === 'upgrade') {
        // Upgrade: 現在の終了日を維持 (期間引継ぎ)
        $end_date_sql = $_SESSION['change_info']['current_end_date'];
    } else {
        // Switch / Reserve / New: プランの期間タイプから計算
        $start_dt_calc = new DateTime($start_date_sql);
        
        $plan_type = 'monthly'; // デフォルト
        if (isset($_SESSION['package_plan'])) {
            $plan_type = $_SESSION['package_plan']['plan_type'];
        } elseif (isset($_SESSION['custom_billing_cycle'])) {
            $plan_type = $_SESSION['custom_billing_cycle'];
        }

        if ($plan_type === 'triennially') {
            $start_dt_calc->modify('+3 years');
        } elseif ($plan_type === 'yearly') {
            $start_dt_calc->modify('+1 year');
        } else {
            $start_dt_calc->modify('+1 month');
        }
        $end_date_sql = $start_dt_calc->format('Y-m-d');
    }


    // 古い契約について
    if ($change_mode === 'upgrade' || $change_mode === 'switch') {
        
        // 現在の契約を削除
        if (isset($_SESSION['change_info']['current_sub_id'])) {
            $old_sub_id = $_SESSION['change_info']['current_sub_id'];   
            $db->prepare("DELETE FROM SubscriptionCustoms WHERE subscription_id = ?")->execute([$old_sub_id]);
            $db->prepare("DELETE FROM Subscription WHERE subscription_id = ?")->execute([$old_sub_id]);
        }

        //  予約も削除
        $futureSql = $db->prepare("SELECT subscription_id FROM Subscription WHERE user_id = ? AND start_date > NOW()");
        $futureSql->execute([$user_id]);
        $futures = $futureSql->fetchAll(PDO::FETCH_COLUMN);
        
        if ($futures) {
            foreach ($futures as $fid) {
                $db->prepare("DELETE FROM SubscriptionCustoms WHERE subscription_id = ?")->execute([$fid]);
                $db->prepare("DELETE FROM Subscription WHERE subscription_id = ?")->execute([$fid]);
            }
        }
    }


    // 新しい契約をインサートする
    $new_pid = isset($_SESSION['package_plan']) ? $_SESSION['package_plan']['product_id'] : 0;
    // 予約か新規・更新かで分ける
    $new_status_id = ($change_mode === 'reserve') ? 5 : 1;
    
    // Subscription
    $subSql = $db->prepare("INSERT INTO Subscription (user_id, product_id, start_date, end_date, status_id, create_date, update_date) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $subSql->execute([$user_id, $new_pid, $start_date_sql, $end_date_sql, $new_status_id]);
    $new_sub_id = $db->lastInsertId();

    // SubscriptionCustoms
    if (isset($_SESSION['custom_options'])) {
        $subCSql = $db->prepare("INSERT INTO SubscriptionCustoms (subscription_id, product_id, create_date, update_date) VALUES (?, ?, NOW(), NOW())");
        foreach ($_SESSION['custom_options'] as $opt) {
            $p_id = $opt['id'] ?? $opt['product_id']; 
            $subCSql->execute([$new_sub_id, $p_id]);
        }
    }


    // 購入完了したのでカートを空にする
    $cartSql = $db->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
    $cartSql->execute([$user_id]);
    $cart = $cartSql->fetch(PDO::FETCH_ASSOC);
    
    if ($cart) {
        $db->prepare("DELETE FROM Cart_Items WHERE cart_id = ?")->execute([$cart['cart_id']]);
        $db->prepare("DELETE FROM Cart WHERE cart_id = ?")->execute([$cart['cart_id']]);
    }

    $db->commit();

    // セッションクリア
    unset($_SESSION['custom_options']);
    unset($_SESSION['custom_total_price']);
    unset($_SESSION['custom_billing_cycle']);
    unset($_SESSION['custom_term_start']);
    unset($_SESSION['custom_term_end']);
    unset($_SESSION['package_plan']);
    unset($_SESSION['change_info']); 

    // 完了画面へ
    header('Location: pay_complete.php');
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    // エラーハンドリング
    $_SESSION['payment_error'] = "処理中にエラーが発生しました: " . $e->getMessage();
    header('Location: payment-error.php');
    exit;
}
?>