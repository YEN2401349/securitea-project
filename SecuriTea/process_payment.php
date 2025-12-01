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

// カード情報の受け取り
$card_type   = $_POST['card-type'] ?? '';
$card_number = $_POST['card_number'] ?? '';

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

// --- 決済処理 (外部API連携などは省略) ---
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
    if ($change_mode !== 'payment_update') {
        $itemSql = $db->prepare("INSERT INTO Order_Items (order_id, product_name, category_id, price, quantity, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");

        if (isset($_SESSION['custom_options'])) {
            foreach ($_SESSION['custom_options'] as $item) {
                // カスタムオプションは category_id=2 と仮定
                $itemSql->execute([$order_id, $item['label'], 2, $item['price']]);
            }
        } elseif (isset($_SESSION['package_plan'])) {
            $plan = $_SESSION['package_plan'];
            // パッケージは category_id=1 と仮定
            $itemSql->execute([$order_id, $plan['product_name'], 1, $plan['totalPrice']]);
        }
    }


    // ==================================================
    // 3. Paymentsテーブルへの登録 (履歴用)
    // ==================================================
    $paymentSql = $db->prepare("INSERT INTO Payments (order_id, amount, payment_date, payment_method, status) VALUES (?, ?, NOW(), ?, 'success')");
    $paymentSql->execute([$order_id, $pay_amount, $payment_method]);


    // ==================================================
    // 4. Profilesテーブル (現在の支払い設定) の更新
    // ==================================================
    $db_card_brand = null;
    $db_masked_num = null;

    if ($payment_method === 'paypal') {
        $db_card_brand = ''; 
        $db_masked_num = 'PayPal';
    } elseif ($payment_method === 'bank_transfer') {
        $db_card_brand = ''; 
        $db_masked_num = '銀行引き落とし';
    } elseif ($payment_method === 'credit_card') {
        $db_card_brand = $card_type;
        $clean_num = str_replace(['-', ' '], '', $card_number);
        $db_masked_num = substr($clean_num, -4);
    }

    if ($db_masked_num !== null) {
        $profileSql = $db->prepare("UPDATE Profiles SET card_brand = ?, masked_card_number = ? WHERE user_id = ?");
        $profileSql->execute([$db_card_brand, $db_masked_num, $user_id]);
    }


    // ==================================================
    // 5. Subscription (契約) の登録・更新処理
    // ★ここが最も重要な修正ポイントです★
    // ==================================================
    
    if ($change_mode !== 'payment_update') {

        // A. 古い契約（予約済み含む）を無効化(status_id=2)する
        // UpgradeやSwitchの場合は、既存の有効な契約(1)を終了させます
        if ($change_mode === 'upgrade' || $change_mode === 'switch') {
             $cancelResSql = $db->prepare("UPDATE Subscription SET status_id = 2, canceled_date = NOW(), update_date = NOW() WHERE user_id = ? AND status_id = 1");
             $cancelResSql->execute([$user_id]);
        }

        // B. 日付の計算
        $start_dt = new DateTime();
        if ($change_mode === 'reserve') {
            // 予約の場合は、現在の終了日の翌日から開始
            $current_end_date = $_SESSION['change_info']['current_end_date'];
            $start_dt = new DateTime($current_end_date);
            $start_dt->modify('+1 day');
        }
        
        $plan_type = $_SESSION['package_plan']['plan_type'] ?? $_SESSION['custom_billing_cycle'] ?? 'monthly';
        $end_dt = clone $start_dt;
        
        if($plan_type === 'triennially') {
            $end_dt->modify('+3 years');
        } elseif($plan_type === 'yearly') {
            $end_dt->modify('+1 year');
        } else {
            $end_dt->modify('+1 month');
        }
        
        $start_date_sql = $start_dt->format('Y-m-d');
        $end_date_sql   = $end_dt->format('Y-m-d');

        // C. 新しい契約情報の登録 (Subscriptionテーブル)
        // status_id = 1 (有効) で登録します
        
        $new_product_id = 0; // カスタムの場合は0
        if (isset($_SESSION['package_plan'])) {
            $new_product_id = $_SESSION['package_plan']['product_id'];
        }

        $subInsertSql = $db->prepare("
            INSERT INTO Subscription (user_id, product_id, start_date, end_date, status_id, create_date, update_date) 
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
        ");
        $subInsertSql->execute([$user_id, $new_product_id, $start_date_sql, $end_date_sql]);
        $new_subscription_id = $db->lastInsertId();

        // D. カスタムオプション詳細の登録 (SubscriptionCustomsテーブル)
        // ※もしSubscriptionCustomsテーブルが存在する場合の処理
        if ($new_product_id == 0 && isset($_SESSION['custom_options'])) {
             // SubscriptionCustomsテーブルがある前提のコードです
             $customInsertSql = $db->prepare("INSERT INTO SubscriptionCustoms (subscription_id, product_id) VALUES (?, ?)");
             foreach ($_SESSION['custom_options'] as $opt) {
                 $customInsertSql->execute([$new_subscription_id, $opt['id']]);
             }
        }

        // E. カートのクリア
        $cartSql = $db->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
        $cartSql->execute([$user_id]);
        $cart = $cartSql->fetch(PDO::FETCH_ASSOC);
        if ($cart) {
            $db->prepare("DELETE FROM Cart_Items WHERE cart_id = ?")->execute([$cart['cart_id']]);
            $db->prepare("DELETE FROM Cart WHERE cart_id = ?")->execute([$cart['cart_id']]);
        }

    } // --- if ($change_mode !== 'payment_update') の終了

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
    // エラー内容を表示する場合はコメントアウトを外してください
    // echo $e->getMessage(); exit;
    $_SESSION['payment_error'] = "処理中にエラーが発生しました。";
    header('Location: payment-error.php');
    exit;
}
?>