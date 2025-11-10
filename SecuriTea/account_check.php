<?php
session_start();
require 'DBconnect.php'; 

// 1. custom.php からのセッションデータがあるか確認
// (save_session.php で保存したキー)
if (!isset($_SESSION['custom_options']) || empty($_SESSION['custom_options'])) {
    // もしセッションにカスタムデータがなければ、
    // 正規の導線ではないため、選択ページに戻す
    header('Location: custom.php');
    exit;
}

// 2. ログイン状態を確認 (headerTag.php に合わせたキー 'customer')
if(isset($_SESSION['customer'])){
    // 3a. ログイン済みの場合
    // cart.php 側で $_SESSION['custom_options'] などの
    // セッションデータを読み込んでカート内容を表示する
    header("Location: cart.php");
    exit;
}
else{
    // 3b. 未ログインの場合
    // ログインページへ転送する
    // (※ ログイン成功後に cart.php へ戻す処理が login.php 側に必要です)
    header('Location: login.php');
    exit;
}
?>