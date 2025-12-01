<?php
session_start();
require '../common/DBconnect.php'; 

// 【重要】account.php に合わせて、$db を $pdo に代入します
// もし DBconnect.php が $connect という名前なら、$pdo = $connect; にしてください
if (isset($db)) {
    $pdo = $db;
} elseif (isset($connect)) { // 念のため $connect の場合も考慮
    $pdo = $connect;
}

// --- ログイン状態を確認 ---
if(!isset($_SESSION['customer'])){
    echo "<script>alert('まずはログインしてください'); window.location.href = 'login.php';</script>";
    exit;
}

$user_id = $_SESSION['customer']['user_id'];

try {
    // --- 1. 最新の注文情報(order_id)を取得 ---
    $sql_order = $pdo->prepare("SELECT order_id FROM Orders WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
    $sql_order->execute([$user_id]);
    $order = $sql_order->fetch(PDO::FETCH_ASSOC);

    // 注文履歴がない場合
    if (!$order) {
        // まだ購入していない場合は変更画面へ行かせるか、エラーにするか
        // ここでは「購入履歴がないので変更できません」として戻します
        echo "<script>alert('購入履歴がありません。'); window.location.href = 'account.php';</script>";
        exit;
    }

    $current_order_id = $order['order_id'];

    // --- 2. 最新の支払い変更時間(payment_date)を取得 ---
    // ここで user_id ではなく order_id を使って検索します
    $sql_payment = $pdo->prepare("SELECT payment_date FROM Payments WHERE order_id = ?");
    $sql_payment->execute([$current_order_id]);
    $payment = $sql_payment->fetch(PDO::FETCH_ASSOC);

    if ($payment) {
        // --- 3. 時間経過のチェック ---
        $last_change_date = new DateTime($payment['payment_date']);
        $now = new DateTime();
        
        // 差分を計算
        $interval = $now->diff($last_change_date);

        // 1日 (days >= 1) 経過しているかチェック
        // ※ もし「24時間以内はダメ」なら $interval->days < 1 で判定します
        if ($interval->days < 1) {
            // 1日経っていない場合
            echo "<script>
                alert('お支払い情報の変更は1日1回までです。\\n前回の変更日時: " . $last_change_date->format('Y-m-d H:i') . "');
                window.location.href = 'account.php';
            </script>";
            exit;
        }
    }

    // --- 4. チェックOKの場合 ---
    // 実際に変更を行うページへリダイレクトします
    $_SESSION['change_info'] = [
        'mode' => 'payment_update', // モード名
        'amount' => 0               // 金額は0円
    ];
    // ※ 変更画面のファイル名が 'pay-change.php' だと仮定しています。正しいファイル名に変更してください。
    header("Location: pay-change.php");
    exit;

} catch (PDOException $e) {
    echo "エラーが発生しました: " . $e->getMessage();
    exit;
}
?>