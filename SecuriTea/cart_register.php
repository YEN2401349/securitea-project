<?php
session_start();
require 'DBconnect.php'; 

// (A) カスタムプランがカートにあるか
$hasCustomPlan = isset($_SESSION['custom_options']) && !empty($_SESSION['custom_options']);
// (B) パッケージプランがカートにあるか
$hasPackagePlan = isset($_SESSION['package_plan']);
// (A) も (B) もどちらもカートに無ければ、商品一覧ページに戻す
if (!$hasCustomPlan && !$hasPackagePlan) {
    header('Location: product.php');
    exit;
}

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
  header("Location: login.php");
  exit();
}

$total_amount = 0;

//どちらのプランがセッションにあるかで分岐
if ($hasCustomPlan) {
    // カスタムプラン
    $total_amount = (int)$_SESSION['custom_total_price'];
} elseif ($hasPackagePlan) {
    // パッケージプラン
    $total_amount = (int)$_SESSION['package_plan']['totalPrice'];
}

if ($total_amount <= 0) {
    // できればエラーメッセージ表示処理追加する
    header('Location: product.php');
    exit;
}

// DBに登録
$sql = $pdo->prepare("INSERT INTO Cart(`user_id`, `total_amount`, `created_at`, `updated_at`) VALUES (?,?,NOW(),NOW())");
$sql->execute([$_SESSION['customer']['user_id'], $total_amount]);

// 登録が完了したら、カートのセッションをクリアする
// (リロードなどで二重登録されるのを防ぐため)
unset($_SESSION['custom_options']);
unset($_SESSION['custom_total_price']);
unset($_SESSION['custom_billing_cycle']);
unset($_SESSION['custom_term_start']);
unset($_SESSION['custom_term_end']);
unset($_SESSION['package_plan']);

header('Location: cart.php');
exit;
?>