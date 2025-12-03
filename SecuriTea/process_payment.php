<?php
session_start();
require "../common/DBconnect.php";
$saved_card_used = isset($_POST['use-saved-card']) && $_POST['use-saved-card'] == '1';
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
    // 通常購入の場合
    if (isset($_SESSION['package_plan'])) {
        $pay_amount = $_SESSION['package_plan']['totalPrice'];
    } elseif (isset($_SESSION['custom_options'])) {
        $pay_amount = $_SESSION['custom_total_price'];
    }
}
// 4. 保存カード使用時の検証
if (!$saved_card_used) {
    $token = bin2hex(random_bytes(16));
    $card_number = $_POST['card-number']; 
    $last4 = substr($card_number, -4);
    $stmt = $db->prepare("UPDATE Profiles SET card_brand = ? , masked_card_number = ? ,payment_token = ? WHERE user_id = ?");
    $stmt->execute(["VISA", $last4, $token, $user_id]);
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
    // 1. 注文履歴・決済履歴の登録 (共通処理)
    // ==================================================
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


    // ==================================================
    // 2. 日付の計算 (モードごとの終了日決定)
    // ==================================================
    $start_date_sql = "";
    $end_date_sql = "";
    $today = new DateTime();

    // (A) 開始日の決定
    if ($change_mode === 'reserve') {
        // 予約: 現在の終了日の「翌日」から開始
        $current_end_date = $_SESSION['change_info']['current_end_date'];
        $start_dt = new DateTime($current_end_date);
        $start_dt->modify('+1 day');
        $start_date_sql = $start_dt->format('Y-m-d');
    } else {
        // アップグレード / スイッチ / 新規: 「今日」から開始
        $start_date_sql = $today->format('Y-m-d');
    }

    // (B) 終了日の決定
    if ($change_mode === 'upgrade') {
        // アップグレード: 終了日は「現在の終了日」を維持する (画像1枚目の上のルート)
        $end_date_sql = $_SESSION['change_info']['current_end_date'];
    } else {
        // スイッチ / 予約 / 新規: 期間タイプに応じて計算する (画像1枚目の真ん中・下のルート)
        $start_dt_calc = new DateTime($start_date_sql);
        
        // プランの期間タイプを取得
        $plan_type = 'monthly';
        if (isset($_SESSION['package_plan'])) {
            $plan_type = $_SESSION['package_plan']['plan_type'];
        } elseif (isset($_SESSION['custom_billing_cycle'])) {
            $plan_type = $_SESSION['custom_billing_cycle'];
        }

        if($plan_type === 'triennially') {
            $start_dt_calc->modify('+3 years');
        } elseif($plan_type === 'yearly') {
            $start_dt_calc->modify('+1 year');
        } else {
            $start_dt_calc->modify('+1 month');
        }
        $end_date_sql = $start_dt_calc->format('Y-m-d');
    }


    // ==================================================
    // 3. 古いデータの削除処理 (物理削除)
    // ==================================================
    // Upgrade または Switch の場合、現在の契約を削除して作り直す
    if ($change_mode === 'upgrade' || $change_mode === 'switch') {
        
        // (A) 現在の契約を削除
        if (isset($_SESSION['change_info']['current_sub_id'])) {
            $old_sub_id = $_SESSION['change_info']['current_sub_id'];

            // 子テーブル(Customs)削除
            $db->prepare("DELETE FROM SubscriptionCustoms WHERE subscription_id = ?")->execute([$old_sub_id]);
            // 親テーブル(Subscription)削除
            $db->prepare("DELETE FROM Subscription WHERE subscription_id = ?")->execute([$old_sub_id]);
        }

        // (B) 未来の予約データも削除 (矛盾するため)
        // 例えば「来月の予約」がある状態で「今日から年契約にスイッチ」したら、来月の予約は無効になるべき
        $delFutureSql = $db->prepare("DELETE FROM Subscription WHERE user_id = ? AND start_date > NOW()");
        $delFutureSql->execute([$user_id]);
    }


    // ==================================================
    // 4. 新しいデータの INSERT
    // ==================================================
    // どのモードでも、最終的には新しいデータを1件 INSERT する
    
    $new_pid = isset($_SESSION['package_plan']) ? $_SESSION['package_plan']['product_id'] : 0;
    
    // Subscriptionテーブルへ INSERT
    $subSql = $db->prepare("INSERT INTO Subscription (user_id, product_id, start_date, end_date, status_id, create_date, update_date) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
    $subSql->execute([$user_id, $new_pid, $start_date_sql, $end_date_sql]);
    $new_sub_id = $db->lastInsertId();

    // SubscriptionCustomsテーブルへ INSERT (カスタムプランの場合)
    if (isset($_SESSION['custom_options'])) {
        $subCSql = $db->prepare("INSERT INTO SubscriptionCustoms (subscription_id, product_id, create_date, update_date) VALUES (?, ?, NOW(), NOW())");
        foreach ($_SESSION['custom_options'] as $opt) {
            // カート内のID等は cart.php/custom.php の保存形式に合わせる
            $p_id = $opt['id'] ?? $opt['product_id']; 
            $subCSql->execute([$new_sub_id, $p_id]);
        }
    }


    // ==================================================
    // 5. カート情報のクリア
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