<?php
session_start();
require '../common/DBconnect.php'; 

// (A) カスタムプランがカートにあるか
$hasCustomPlan = isset($_SESSION['custom_options']) && !empty($_SESSION['custom_options']);
// (B) パッケージプランがカートにあるか
$hasPackagePlan = isset($_SESSION['package_plan']);

// (A) も (B) もどちらもカートに無ければ、商品一覧ページに戻す
if (!$hasCustomPlan && !$hasPackagePlan) {
    header('Location: product.php');
    exit;
}

// --- 2. ログイン状態を確認 ---
if(isset($_SESSION['customer'])){
    // 3a. ログイン済みの場合
    // どちらかのプランがカートに入っているので cart_register.php へ飛ばす
    header("Location: cart_register.php");
    exit;
}
else{
    // 3b. 未ログインの場合
    // ログインページへ転送する
    header('Location: login.php');
    exit;
}
?>