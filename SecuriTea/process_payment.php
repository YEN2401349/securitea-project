<?php
session_start();
require 'DBconnect.php';

if (!isset($_SESSION['customer']['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['payment-method'])) {
    header('Location: new-pay.php');
    exit;
}

// --- 2. 決済処理 (スタブ) ---
// 本来はここに Stripe, PayPal などの決済API呼び出し処理が入ります。
// クレジットカード情報 ($_POST['card-number'] など) もここで使います。
// 今回は「必ず成功する」と仮定します。
$payment_succeeded = true;

if (!$payment_succeeded) {
    $_SESSION['payment_error'] = "決済処理に失敗しました。";
    header('Location: payment-error.php');
    exit;
}

try {
    $db->beginTransaction();

    // １．user_idを元に、OrdersテーブルにCartテーブルの情報を登録
    // ２．もしカスタムプランなら、cart_idを元に、Order_ItemsにCart_itemsの情報を登録
    // ３．order_idを元に、Paymentsテーブルに登録
    // ４，user_idを元に、Subscriptionテーブルに登録
    // 5．もしカスタムプランなら、subscription_idを元に、SubscriptionCustomsテーブルにCart_itemsの情報を登録


    // --- 4. 完了処理 ---
    $db->commit();

    // カートセッションをクリア
    unset($_SESSION['custom_options']);
    unset($_SESSION['custom_total_price']);
    unset($_SESSION['custom_billing_cycle']);
    unset($_SESSION['custom_term_start']);
    unset($_SESSION['custom_term_end']);
    unset($_SESSION['order_id']);

    // 完了ページへリダイレクト
    header('Location: payment_complete.php');
    exit;

} catch (Exception $e) {
    // --- 5. エラー処理 ---
    $db->rollBack();
    
    // エラーメッセージをセッションに保存してエラーページへ
    $_SESSION['payment_error'] = "データベース登録中にエラーが発生しました: " . $e->getMessage();
    header('Location: payment_error.php');
    exit;
}
?>