<?php session_start(); ?>
<?php require 'DBconnect.php' ?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecuriTea - ログイン</title>
  <link rel="stylesheet" href="css/login-style.css">
</head>
<body>
  <?PHP require 'headerTag.php'?>

  <!-- ログインカード -->
  <main class="login-container">
    <div class="login-card">
      <h2 class="section-title">ログイン</h2>
      <p class="login-description">
        SecuriTea のサービスをご利用いただくには、<br>
        アカウントにログインしてください。
      </p>

    <?php
    if (isset($_GET['error'])) {
        echo '<p style="color: red;">メールアドレスまたはパスワードが違います。</p>';
    }
    ?>

      <form action="login_check.php" method="post" class="login-form">
        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="email" required placeholder="メールアドレス">
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" required placeholder="パスワード">
        </div>

        <a href="remindPass.php" class="forgot-password">パスワードをお忘れですか？</a>

        <button type="submit" class="product-btn">続ける</button>
      </form>

      <p class="register">
        アカウントが未登録ですか？ <a href="add_account-input.php">アカウントの作成</a>
      </p>
    </div>
  </main>

  <!-- フッター -->
  <footer class="footer">
    <p>&copy; 2025 SecuriTea. All rights reserved.</p>
  </footer>
</body>
</html>
