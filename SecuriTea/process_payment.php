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

if (isset($_SESSION['change_info']['amount'])) {
    $pay_amount = $_SESSION['change_info']['amount'];
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
    $orderSql = $db->prepare("INSERT INTO Orders (user_id, total_amount, status, created_at, updated_at) VALUES (?, ?, 'paid', NOW(), NOW())");
    $orderSql->execute([$user_id, $pay_amount]);
    $order_id = $db->lastInsertId();


    // ==================================================
    // 2. Order_Items (明細) への登録
    // ==================================================
    $itemSql = $db->prepare("INSERT INTO Order_Items (order_id, product_name, category_id, price, quantity, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");

    if (isset($_SESSION['custom_options'])) {
        foreach ($_SESSION['custom_options'] as $item) {
            $itemSql->execute([$order_id, $item['label'], 2, $item['price']]);
        }
    } elseif (isset($_SESSION['package_plan'])) {
        $plan = $_SESSION['package_plan'];
        $itemSql->execute([$order_id, $plan['product_name'], 1, $plan['totalPrice']]);
    }


    // ==================================================
    // 3. Paymentsテーブルへの登録
    // ==================================================
    $paymentSql = $db->prepare("INSERT INTO Payments (order_id, amount, payment_date, payment_method, status) VALUES (?, ?, NOW(), ?, 'success')");
    $paymentSql->execute([$order_id, $pay_amount, $payment_method]);


    // ==================================================
    // ★追加: 予約の強制キャンセル処理
    // upgradeまたはswitchの場合、未来の予約(start_date > NOW)は無効化する
    // ==================================================
    if ($change_mode === 'upgrade' || $change_mode === 'switch') {
        // 解約扱い(status=2)にする、または物理削除する
        // ここでは「キャンセル済み」として履歴に残るよう status=2 にUPDATEします
        $subsciptionId = $db->prepare("DELETE FROM Subscription WHERE user_id = ?");
        $subsciptionId->execute([$user_id]);
        $subscriptionId = $subsciptionId->fetch(PDO::FETCH_ASSOC);
    }


    // ==================================================
    // 4. 日付の計算
    // ==================================================
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


    // ==================================================
    // 5. Subscription (契約) の処理 (分岐)
    // ==================================================

    if ($change_mode === 'reserve') {
        // --- Reserve (予約) ---
        $new_pid = isset($_SESSION['package_plan']) ? $_SESSION['package_plan']['product_id'] : 0;
        $subSql = $db->prepare("INSERT INTO Subscription (user_id, product_id, start_date, end_date, status_id, create_date, update_date) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
        $subSql->execute([$user_id, $new_pid, $start_date_sql, $end_date_sql]);
        $new_sub_id = $db->lastInsertId();

        if (isset($_SESSION['custom_options'])) {
            $subCSql = $db->prepare("INSERT INTO SubscriptionCustoms (subscription_id, product_id, create_date, update_date) VALUES (?, ?, NOW(), NOW())");
            foreach ($_SESSION['custom_options'] as $opt) {
                $subCSql->execute([$new_sub_id, $opt['id']]);
            }
        }

    } elseif ($change_mode === 'upgrade') {
        // --- Upgrade ---
        $target_sub_id = $_SESSION['change_info']['current_sub_id'];

        if (isset($_SESSION['package_plan'])) {
            $new_pid = $_SESSION['package_plan']['product_id'];
            $updSql = $db->prepare("UPDATE Subscription SET product_id = ?, status_id = 1, update_date = NOW() WHERE subscription_id = ?");
            $updSql->execute([$new_pid, $target_sub_id]);
        
        } elseif (isset($_SESSION['custom_options'])) {
            $updStatusSql = $db->prepare("UPDATE Subscription SET status_id = 1, update_date = NOW() WHERE subscription_id = ?");
            $updStatusSql->execute([$target_sub_id]);

            $delDSql = $db->prepare("DELETE FROM SubscriptionCustoms WHERE subscription_id = ?");
            $delDSql->execute([$target_sub_id]);
            
            $insDSql = $db->prepare("INSERT INTO SubscriptionCustoms (subscription_id, product_id, create_date, update_date) VALUES (?, ?, NOW(), NOW())");
            foreach ($_SESSION['custom_options'] as $opt) {
                $insDSql->execute([$target_sub_id, $opt['id']]);
            }
        }

    } elseif ($change_mode === 'switch') {
        // --- Switch ---
        if(isset($_SESSION['change_info']['current_sub_id'])){
            $old_sub_id = $_SESSION['change_info']['current_sub_id'];
            $stopSql = $db->prepare("UPDATE Subscription SET status_id = 2, end_date = NOW(), update_date = NOW() WHERE subscription_id = ?");
            $stopSql->execute([$old_sub_id]);
        }

        $new_pid = isset($_SESSION['package_plan']) ? $_SESSION['package_plan']['product_id'] : 0;
        $subSql = $db->prepare("INSERT INTO Subscription (user_id, product_id, start_date, end_date, status_id, create_date, update_date) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
        $subSql->execute([$user_id, $new_pid, $start_date_sql, $end_date_sql]);
        $new_sub_id = $db->lastInsertId();

        if (isset($_SESSION['custom_options'])) {
            $subCSql = $db->prepare("INSERT INTO SubscriptionCustoms (subscription_id, product_id, create_date, update_date) VALUES (?, ?, NOW(), NOW())");
            foreach ($_SESSION['custom_options'] as $opt) {
                $subCSql->execute([$new_sub_id, $opt['id']]);
            }
        }

    } else {
        // --- New ---
        $new_pid = isset($_SESSION['package_plan']) ? $_SESSION['package_plan']['product_id'] : 0;
        $subSql = $db->prepare("INSERT INTO Subscription (user_id, product_id, start_date, end_date, status_id, create_date, update_date) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
        $subSql->execute([$user_id, $new_pid, $start_date_sql, $end_date_sql]);
        $new_sub_id = $db->lastInsertId();

        if (isset($_SESSION['custom_options'])) {
            $subCSql = $db->prepare("INSERT INTO SubscriptionCustoms (subscription_id, product_id, create_date, update_date) VALUES (?, ?, NOW(), NOW())");
            foreach ($_SESSION['custom_options'] as $opt) {
                $subCSql->execute([$new_sub_id, $opt['id']]);
            }
        }
    }

    // ==================================================
    // 6. カート情報のクリア
    // ==================================================
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