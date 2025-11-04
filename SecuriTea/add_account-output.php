<?php
require 'DBconnect.php';

$pdo = $db;

// フォームの値を受け取る
$full_name = $_POST['Fname'] . ' '. $_POST['Lname'];
$gender = $_POST['gender'];
$email = $_POST['email'];
$phone = $_POST['tel1'] . '-' . $_POST['tel2'] . '-' . $_POST['tel3'];
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];

// 確認：パスワードが一致しない場合
if ($password !== $password_confirm) {
    echo '<p>パスワードが一致しません。<a href="add_account-input.php">戻る</a></p>';
    exit();
}

// パスワードをハッシュ化
$hash = password_hash($password, PASSWORD_DEFAULT);

// 登録処理
//ユーザー
$sql = $pdo->prepare("INSERT INTO Users (user_email,user_password,role) VALUES (?, ?,'user')");
$sql->execute([$email,$hash]);

$last_user_id = $pdo->lastInsertId();

//プロフィール
$sql = $pdo->prepare('INSERT INTO Profiles (user_id,full_name, gender, phone) VALUES (?, ?, ?, ?)');
$sql->execute([$last_user_id,$full_name,$gender,$phone]);

echo '<!DOCTYPE html><html lang="ja"><head><meta charset="UTF-8">';
echo '<title>登録完了</title>';
echo '<link rel="stylesheet" href="css/login-style.css">';
echo '</head><body>';

echo '<main class="login-container">';
echo '<div class="login-card">';
echo '<h2 class="section-title">登録が完了しました</h2>';
echo '<p>ご登録ありがとうございます。</p>';
echo '<p><a href="login.php" class="product-btn">ログイン画面へ</a></p>';
echo '</div>';
echo '</main>';

echo '<footer class="footer"><p>&copy; 2025 SecuriTea. All rights reserved.</p></footer>';
echo '</body></html>';

$pdo = null; // DB切断
?>
