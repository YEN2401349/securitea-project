<?php
session_start();
require "DBconnect.php";
if (!isset($_SESSION['customer']['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['customer']['user_id'];
$pdo = $db;

// 14行目のSQLはサブスクは有効か無効かでしか判断してなくて、Subscription_statusテーブルの3,4のケースは考慮してない　３，４でもとりあえず動くとは思うけど、正しい動作は無理そう
try {
    $sql_sub = $pdo->prepare("SELECT subscription_id FROM Subscription WHERE user_id = ? AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY create_date DESC LIMIT 1");
    $sql_sub->execute([$user_id]);
    $subscription = $sql_sub->fetch(PDO::FETCH_ASSOC);

    if ($subscription) {
        $subscription_id = $subscription['subscription_id'];

        $sql_custom = $pdo->prepare("SELECT product_id FROM SubscriptionCustoms WHERE subscription_id = ?");
        $sql_custom->execute([$subscription_id]);

        if ($sql_custom->fetch()) {
            // カスタム情報あり → カスタム用画面へ
            $_SESSION['customer']['user_type'] = 'custom';
            header("Location: account_custom.php");
            exit();
        } else {
            // カスタム情報なし → 通常サブスク用画面へ
            $_SESSION['customer']['user_type'] = 'normal';
            header("Location: account_normal.php");
            exit();
        }

    } else {
        // サブスク契約なし → 通常画面（未契約状態として表示）へ
        $_SESSION['customer']['user_type'] = 'normal';
        header("Location: account_normal.php");
        exit();
    }

} catch (PDOException $e) {
    echo "エラー：" . $e->getMessage();
    exit();
}
?>