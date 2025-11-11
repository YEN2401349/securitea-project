<?php
session_start();
require 'DBconnect.php'; 

// --- 1. ★★★ 変更点 ★★★ ---
// (A) カスタムプランがカートにあるか
$hasCustomPlan = isset($_SESSION['custom_options']) && !empty($_SESSION['custom_options']);
// (B) パッケージプランがカートにあるか
$hasPackagePlan = isset($_SESSION['package_plan']);

// (A) も (B) もどちらもカートに無ければ、商品一覧ページに戻す
if (!$hasCustomPlan && !$hasPackagePlan) {
    // (戻り先を custom.php から product.php に変更すると、より親切です)
    header('Location: product.php');
    exit;
}

// --- 2. ログイン状態を確認 (変更なし) ---
if(isset($_SESSION['customer'])){
    // 3a. ログイン済みの場合
    // どちらかのプランがカートに入っているので cart.php へ
    header("Location: cart.php");
    exit;
}
else{
    // 3b. 未ログインの場合
    // ログインページへ転送する
    header('Location: login.php');
    exit;
}
?>