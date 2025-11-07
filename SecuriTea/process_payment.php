<?php
session_start();
require 'DBconnect.php'; // DB接続

// --- 1. 事前チェック ---

// ログインチェック (customerセッションに 'id' があると仮定)
if (!isset($_SESSION['customer']['user_id'])) {
    header('Location: login.php'); // ログインしてなければログインページへ
    exit;
}

// カート情報チェック (custom.php からの情報)
if (!isset($_SESSION['custom_options']) || empty($_SESSION['custom_options']) || !isset($_SESSION['custom_total_price'])) {
    header('Location: custom.php'); // カートが空ならカスタム選択ページへ
    exit;
}

// POSTデータチェック
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['payment-method'])) {
    header('Location: new-pay.php'); // POST以外、または支払い方法が未選択なら支払いページへ
    exit;
}

// --- 2. 決済処理 (スタブ) ---
// 本来はここに Stripe, PayPal などの決済API呼び出し処理が入ります。
// クレジットカード情報 ($_POST['card-number'] など) もここで使います。
// 今回は「必ず成功する」と仮定します。
$payment_succeeded = true; 

if (!$payment_succeeded) {
    // 決済失敗時の処理
    $_SESSION['payment_error'] = "決済処理に失敗しました。";
    header('Location: payment-error.php');
    exit;
}

// --- 3. データベース登録処理 ---

try {
    $db->beginTransaction();

    // --- 3a. Payments テーブルへの登録 ---
    
    // 前提: 注文(Orders)は作成済みで、セッションに order_id があると仮定
    // (もし Orders テーブルがない場合、ここは機能しません)
    $order_id = $_SESSION['order_id'] ?? 0; // 仮にセッションになければ 0 を設定 (FK制約に注意)
    $amount = $_SESSION['custom_total_price'];
    
    // ENUM の値 ('credit card', 'paypal', 'bank transfer') にマッピング
    $payment_method_raw = $_POST['payment-method']; // 'credit', 'paypal', 'bank'
    $payment_method_db = [
        'credit' => 'credit card',
        'paypal' => 'paypal',
        'bank' => 'bank transfer'
    ][$payment_method_raw] ?? 'credit card'; // 不明な場合は credit card
    
    // Payments テーブル に挿入
    $sql_payment = "INSERT INTO Subscription (order_id, amount, payment_method, status) 
                    VALUES (?, ?, ?, 'success')";
    $stmt_payment = $db->prepare($sql_payment);
    $stmt_payment->execute([
        $order_id,
        $amount,
        $payment_method_db
    ]);

    // --- 3b. Subscriptions テーブルへの登録 ---

    $user_id = $_SESSION['customer']['id'];
    $options = $_SESSION['custom_options'];
    $termStartStr = $_SESSION['custom_term_start'];
    $termEndStr = $_SESSION['custom_term_end'];

    // 期間文字列 (例: 2025年10月07日(火)) を Y-m-d 形式に変換
    // (preg_replace で曜日部分を除去してから DateTime でパース)
    $termStart = preg_replace('/\(.+\)/', '', $termStartStr);
    $termEnd = preg_replace('/\(.+\)/', '', $termEndStr);
    
    $start_date = DateTime::createFromFormat('Y年m月d日', $termStart)->format('Y-m-d');
    $end_date = DateTime::createFromFormat('Y年m月d日', $termEnd)->format('Y-m-d');

    // SQL準備 (ループの外で)
    $sql_get_id = "SELECT product_id FROM Products WHERE name = ?";
    $sql_insert_sub = "INSERT INTO Subscriptions (user_id, product_id, start_date, end_date, status_id) 
                       VALUES (?, ?, ?, ?, 4)"; // status_id=4 (デフォルト値)
    
    $stmt_get_id = $db->prepare($sql_get_id);
    $stmt_insert_sub = $db->prepare($sql_insert_sub);

    // セッションにあるオプションの「ラベル名」を元に product_id を検索し、
    // オプション（商品）の数だけ Subscriptions に登録
    foreach ($options as $option) {
        $label = $option['label'];
        
        // ラベル名(name)から product_id を逆引き
        $stmt_get_id->execute([$label]);
        $product = $stmt_get_id->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $product_id = $product['product_id'];
            
            // Subscriptions テーブル に挿入
            $stmt_insert_sub->execute([
                $user_id,
                $product_id,
                $start_date,
                $end_date
            ]);
        } else {
            // もし商品名(label)から product_id が見つからなかったらエラー
            throw new Exception("商品が見つかりません: " . htmlspecialchars($label, ENT_QUOTES));
        }
    }

    // --- 4. 完了処理 ---
    $db->commit();

    // カートセッションをクリア
    unset($_SESSION['custom_options']);
    unset($_SESSION['custom_total_price']);
    unset($_SESSION['custom_billing_cycle']);
    unset($_SESSION['custom_term_start']);
    unset($_SESSION['custom_term_end']);
    unset($_SESSION['order_id']); // 仮

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