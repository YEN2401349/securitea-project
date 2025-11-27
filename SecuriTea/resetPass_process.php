<?php session_start(); ?>
<?php require '../common/DBconnect.php' ?>
<?php
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['password_reset_step']) || $_SESSION['password_reset_step'] !== 2) {
    header('Location: remindPass.php');
    exit;
}
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];
if ($password !== $password_confirm) {
    header('Location: resetPass.php?error=mismatch');
    exit;
}

try {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $email = $_SESSION['reset_email'];

    $stmt = $db->prepare("UPDATE Users SET user_password = ? WHERE user_email = ?");
    $stmt->execute([$hashed_password, $email]);

    session_destroy();

    header('Location: login.php?success=reset');
    exit;

} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
    exit;
}
?>