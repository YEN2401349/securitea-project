<?php
session_start();
require '../common/DBconnect.php'; 

$email = $_POST['email'];
$password = $_POST['password'];

try {
    $stmt = $db->prepare("SELECT * FROM Users WHERE user_email = ? AND role = 'user'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['user_password'])) {
    $_SESSION['customer'] = $user; 
    unset($_SESSION['customer']['user_password']);
    header('Location: top.php');
    exit;

    } else {
        header('Location: login.php?error=1');
        exit;
    }

} catch (PDOExceptiona $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>