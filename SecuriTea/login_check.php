<?php
session_start();
require 'DBconnect.php'; 

$email = $_POST['email'];
$password = $_POST['password'];

try {
    $stmt = $db->prepare("SELECT * FROM Users WHERE user_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['user_password'])) { 

        $_SESSION['user_id'] = $user['user_id'];
        header('Location: top.php');
        exit;

    } else {
        header('Location: login.php?error=1');
        exit;
    }

} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>