<?php
session_start();
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    header("Location: login.php?error=メールアドレスとパスワードを入力してください");
    exit;
}

$result = ['user@gmail.com' => (object)[
        'password' => '123',
        'state' => 'user'
], 'admin@gmail.com' => (object)[
        'password' => '123',
        'state' => 'admin'
]];

if (isset($result[$email])) {
    $user = $result[$email];

    if ($password === $user->password) {
        $_SESSION['login_state'] = $user->state;
        $_SESSION['user_email'] = $email;
        if($_SESSION['login_state'] == 'user'){
            header("Location: index.php");
        }
        exit;
    } else {
        $_SESSION["error"] ="パスワードが間違っています";
        header("Location: login-input.php");
        exit;
    }
} else {
    $_SESSION["error"] = "登録されていないメールアドレスです";
    header("Location: login-input.php");
    exit;
}
?>
