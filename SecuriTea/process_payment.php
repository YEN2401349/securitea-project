<?php
session_start();
require 'DBconnect.php';

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
    header('Location: login.php');
    exit;
}

// POSTリクエストチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['payment-method'])) {
    header('Location: new-pay.php');
    exit;
}

$user_id = $_SESSION['customer']['user_id'];
$payment_method = $_POST['payment-method']; // credit, paypal, bank

// --- 2. 決済処理 (スタブ) ---
// 本来はここに Stripe, PayPal などの決済API呼び出し処理が入ります。
// 今回は「必ず成功する」と仮定します。
$payment_succeeded = true;

if (!$payment_succeeded) {
    $_SESSION['payment_error'] = "決済処理に失敗しました。";
    header('Location: payment-error.php');
    exit;
}

try {
    $db->beginTransaction();

    // --------------------------------------------------
    // A. カート情報の取得 (DBから)
    // --------------------------------------------------
    $cartSql = $db->prepare("SELECT * FROM Cart WHERE user_id = ?");
    $cartSql->execute([$user_id]);
    $cart = $cartSql->fetch(PDO::FETCH_ASSOC);

    if (!$cart) {
        throw new Exception("カート情報が見つかりません。");
    }

    $cart_id = $cart['cart_id'];
    $total_amount = $cart['total_amount'];

    // カート内アイテムの取得
    $itemsSql = $db->prepare("SELECT * FROM Cart_Items WHERE cart_id = ?");
    $itemsSql->execute([$cart_id]);
    $cart_items = $itemsSql->fetchAll(PDO::FETCH_ASSOC);

    // --------------------------------------------------
    // B. 期間(サブスクリプション期間)の計算
    // --------------------------------------------------
    $start_date = new DateTime(); // 今日 (開始日)
    $end_date   = new DateTime(); // 計算用 (終了日)

    // パッケージプランがある場合
    if (isset($_SESSION['package_plan'])) {
        
        // ★ここで期間タイプを取得します
        // ※ add_pack.php で 'monthly', 'yearly', '3years' のいずれかをセットしておいてください
        $plan_cycle = $_SESSION['package_plan']['plan_type'] ?? 'monthly'; 

        switch ($plan_cycle) {
            case 'triennially':
                // 3年後の日付
                $end_date->modify('+3 years');
                break;
            case 'yearly':
                // 1年後の日付
                $end_date->modify('+1 year');
                break;
            case 'monthly':
            default:
                // 1ヶ月後の日付
                $end_date->modify('+1 month');
                break;
        }

    } 
    // カスタムプランがある場合
    elseif (isset($_SESSION['custom_options'])) {
        
        $custom_cycle = $_SESSION['custom_billing_cycle'] ?? 'monthly';

        if ($custom_cycle === 'yearly') {
            $end_date->modify('+1 year');
        } else {
            $end_date->modify('+1 month');
        }
    }

    // DB保存用のフォーマット (Y-m-d)
    $sql_start_date = $start_date->format('Y-m-d');
    $sql_end_date   = $end_date->format('Y-m-d');


    // --------------------------------------------------
    // 1. Ordersテーブルへの登録
    // --------------------------------------------------
    // user_id, total_amount, order_date
    $orderSql = $db->prepare("INSERT INTO Orders (user_id,total_amount,status,created_at,updated_at) VALUES (?, ?, 'paid',NOW(),NOW())");
    $orderSql->execute([$user_id, $total_amount]);
    $order_id = $db->lastInsertId();


    // --------------------------------------------------
    // 2. Order_Items (明細) への登録
    // --------------------------------------------------
    // カートの中身を注文明細として保存
    // あれする　ここでベースプランもcart_itemに登録するように変更してください
    $orderItemSql = $db->prepare("INSERT INTO Order_Items (order_id, product_id, price) VALUES (?, ?, ?)");
    if(isset($_SESSION['custom_options'])){
        $orderItemSql->execute([$order_id,0,0]);
    foreach ($cart_items as $item) {
        $orderItemSql->execute([
            $order_id, 
            $item['product_id'], 
            $item['price']
        ]);
    }
    }else{
        $orderItemSql->execute([$order_id,$_SESSION['package_plan']['product_id'],$_SESSION['package_plan']['totalPrice']]);
    }


    // --------------------------------------------------
    // 3. Paymentsテーブルへの登録
    // --------------------------------------------------
    // order_id, amount, method, payment_date
    $paymentSql = $db->prepare("INSERT INTO Payments (order_id,amount,payment_date,payment_method,status) VALUES (?,?,NOW(),?,'success')");
    $paymentSql->execute([$order_id,$total_amount, $payment_method]);


    // --------------------------------------------------
    // 4. Subscription (契約) テーブルへの登録
    // --------------------------------------------------
    // user_id, order_id, start_date, end_date, status(1=有効など)
    $subSql = $db->prepare("INSERT INTO Subscription (user_id,product_id,start_date,end_date,status_id,create_date,update_date) VALUES (?,?,?,?,1,NOW(),NOW())");
    if(isset($_SESSION['custom_options'])){
        $subSql->execute([$user_id,0,$sql_start_date,$sql_end_date]);
    }else{
        $subSql->execute([$user_id,$_SESSION['package_plan']['product_id'],$sql_start_date,$sql_end_date]);
    }
    $subscription_id = $db->lastInsertId();


    // --------------------------------------------------
    // 5. SubscriptionCustoms (契約詳細) への登録
    // --------------------------------------------------
    // どのオプションが含まれているかを記録
    $subCustomSql = $db->prepare("INSERT INTO SubscriptionCustoms (subscription_id, product_id,create_date,update_date) VALUES (?, ?,NOW(),NOW())");
    foreach ($cart_items as $item) {
        if($item['product_id'] != 0){
        $subCustomSql->execute([
            $subscription_id,
            $item['product_id']
        ]);
        }
    }


    // --------------------------------------------------
    // 6. カート情報の削除 (購入完了したので空にする)
    // --------------------------------------------------
    // 外部キー制約がある場合、子(Items)から消す必要があることが多いです
    $delItemSql = $db->prepare("DELETE FROM Cart_Items WHERE cart_id = ?");
    $delItemSql->execute([$cart_id]);
    
    $delCartSql = $db->prepare("DELETE FROM Cart WHERE cart_id = ?");
    $delCartSql->execute([$cart_id]);


    // --- 完了処理 ---
    $db->commit();

    // セッションのカート情報をクリア
    unset($_SESSION['custom_options']);
    unset($_SESSION['custom_total_price']);
    unset($_SESSION['custom_billing_cycle']);
    unset($_SESSION['custom_term_start']);
    unset($_SESSION['custom_term_end']);
    unset($_SESSION['package_plan']); // パッケージプランもあればクリア

    // 完了ページへリダイレクト
    header('Location: pay_complete.php');
    exit;

} catch (Exception $e) {
    // --- エラー処理 ---
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    // エラーメッセージをセッションに保存
    $_SESSION['payment_error'] = "システムエラーが発生しました: " . $e->getMessage();
    header('Location: payment_error.php');
    exit;
}
?>