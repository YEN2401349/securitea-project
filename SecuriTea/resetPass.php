<?php
session_start();
if (!isset($_SESSION['reset_email'])) {
    header('Location: remindPass.php');
    exit;
}
if (isset($_POST['number'])) {
    $code = $_POST['number'];
    if (strlen($code) !== 6 || !is_numeric($code)) {
        header('Location: remindPass-output.php?error=code_invalid');
        exit;
    }
    $_SESSION['password_reset_step'] = 2;
} else if (isset($_SESSION['password_reset_step']) && $_SESSION['password_reset_step'] === 2) {
} else {
    header('Location: remindPass-output.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecuriTea - ログイン</title>
  <link rel="stylesheet" href="css/login-style.css">
  <link rel="stylesheet" href="css/heder-footer.css">
</head>
<body>
  <!-- ヘッダー -->
  <?php require 'headerTag.php' ?>

  <!-- ログインカード -->
  <main class="login-container">
    <div class="login-card">
      <h2 class="section-title">パスワードの再設定</h2>
      <p class="login-description">
        新しいパスワードを入力してください。
      </p>

      <?php
      if (isset($_GET['error']) && $_GET['error'] === 'mismatch') {
          echo '<p style="color: red;">パスワードが一致しません。</p>';
      }
      ?>

      <form action="resetPass_process.php" method="post" class="login-form">
        <div class="form-group">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" required placeholder="パスワード">
        </div>
        <div class="form-group">
          <label for="password-confirm">パスワード(確認)</label>
          <input type="password" id="password-confirm" name="password_confirm" required placeholder="パスワード">
        </div>
          

        <button type="submit" class="product-btn">再設定</button>
      </form>
    </div>
  </main>

  <!-- フッター -->
  <?php require "footer.php"; ?>
</body>
</html>
