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

      <form id="resetForm" class="login-form">
        <?php
        $mail = $_GET['mail']?$_GET['mail']: '...';
        echo '<input type="hidden" id="mail" name="mail" value="' . $mail . '">';
        ?>
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
  <script src="./script/reset_password.js"></script>
</body>

</html>