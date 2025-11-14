<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SecuriTea - ログイン</title>
  <link rel="stylesheet" href="css/login-style.css">
</head>
<body>
  <!-- ヘッダー -->
  <?php require 'headerTag.php' ?>

  <!-- ログインカード -->
  <main class="login-container">
    <div class="login-card">
      <h2 class="section-title">パスワードの再設定</h2>
      <p class="login-description">
        パスワードを再設定するため、<b>登録している</b><br>
        メールアドレスを入力してください。
      </p>

      <?php
      if (isset($_GET['error']) && $_GET['error'] === 'not_found') {
          echo '<p style="color: red;">そのメールアドレスは登録されていません。</p>';
      }
      ?>

      <form action="remindPass-output.php" method="post" class="login-form">
        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="email" required placeholder="メールアドレス">
        </div>

        <button type="submit" class="product-btn">送信</button>
      </form>
    </div>
  </main>

  <!-- フッター -->
  <?php require "footer.php"; ?>
</body>
</html>
