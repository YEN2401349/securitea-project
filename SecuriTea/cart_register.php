<?php
session_start();
require 'DBconnect.php'; 

// (A) プランの有無を確認
$hasCustomPlan = isset($_SESSION['custom_options']) && !empty($_SESSION['custom_options']);
$hasPackagePlan = isset($_SESSION['package_plan']);

// (B) どちらのプランも無ければ戻す
if (!$hasCustomPlan && !$hasPackagePlan) {
    header('Location: product.php');
    exit;
}

// (C) ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
  header("Location: login.php");
  exit();
}

// (D) 登録するユーザーIDと金額を決定
$user_id = $_SESSION['customer']['user_id'];
$total_amount = 0; 

if ($hasCustomPlan) {
    $total_amount = (int)$_SESSION['custom_total_price'];
} elseif ($hasPackagePlan) {
    $total_amount = (int)$_SESSION['package_plan']['totalPrice'];
}

// (E) 金額が不正なら戻す
if ($total_amount <= 0) {
    header('Location: product.php');
    exit;
}

try {
    // (F)このユーザーIDに紐づく「既存のカート情報」をすべて削除する
    // (UPDATEの代わりに、DELETE -> INSERT を行う)
    // (F-1) 既存の cart_id を探す
    $checkSql = $db->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
    $checkSql->execute([$user_id]);
    $existingCarts = $checkSql->fetchAll(PDO::FETCH_ASSOC);

    if ($existingCarts) {
        // (F-2) 既存のカートIDが見つかったら、関連する Cart_Items と Cart を削除
        $deleteItemsSql = $db->prepare("DELETE FROM Cart_Items WHERE cart_id = ?");
        $deleteCartSql = $db->prepare("DELETE FROM Cart WHERE cart_id = ?");

        foreach ($existingCarts as $cart) {
            $old_cart_id = $cart['cart_id'];
            $deleteItemsSql->execute([$old_cart_id]); // 子テーブル(Cart_Items)から削除
            $deleteCartSql->execute([$old_cart_id]);  // 親テーブル(Cart)から削除
        }
    }

    // (G) ★常にINSERT★
    // 新しくカート情報を作成
    $insertSql = $db->prepare(
        "INSERT INTO Cart(user_id, total_amount, created_at, updated_at) VALUES (?, ?, NOW(), NOW())"
    );
    $insertSql->execute([$user_id, $total_amount]);
    
    // (G-2) 今INSERTした cart_id を取得
    $cart_id = $db->lastInsertId(); 
    $message = "カートに登録しました。";

    // (H) Cart_Items への登録処理 (INSERT)
    if ($hasCustomPlan) {
        $itemSql = $db->prepare(
        "INSERT INTO `Cart_Items`(`cart_id`, `product_id`, `price`, `created_at`, `updated_at`) VALUES (?, ?, ?, NOW(), NOW())"
    );
        $itemSql->execute([$cart_id,0,0]);

        foreach ($_SESSION['custom_options'] as $item) {
            $product_id = $item['id'];
            $price = $item['price'];
            $itemSql->execute([$cart_id, $product_id, $price]);
        }
    }else{
        $itemSql = $db->prepare("INSERT INTO `Cart_Items`(`cart_id`, `product_id`, `price`, `created_at`, `updated_at`) VALUES (?, ?, ?, NOW(), NOW())");
        $itemSql->execute([$cart_id,$_SESSION['package_plan']['product_id'],$total_amount]);
    }

    // 新規契約者と既存契約者を判断する処理
    $subSql = $db->prepare("SELECT COUNT(*) FROM Subscription WHERE user_id = ? AND end_date >= NOW()");
    $subSql->execute([$user_id]);
    $subCount = $subSql->fetchColumn();

    // 件数が0より大きければ「既存契約者」、そうでなければ「新規」
    if ($subCount > 0) {
        // 既存契約者用のページへ（例: 契約更新用のカート画面など）
        header('Location: cart_planChange.php'); 
    } else {
        // 新規登録者用のページへ（通常のカート画面）
        header('Location: cart.php');
    }
    exit; // 忘れずにexit

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
    exit;
}
?>