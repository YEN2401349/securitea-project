<?php session_start(); ?>
<?php require 'DBconnect.php' ?>
<?php
if (isset($_POST['email'])) {
    $email = $_POST['email'];
    try {
        $stmt = $db->prepare("SELECT * FROM Users WHERE user_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['reset_email'] = $user['user_email'];
        } else {
            header('Location: remindPass.php?error=not_found');
            exit;
        }
    } catch (PDOException $e) {
        echo "データベースエラー: " . $e->getMessage();
        exit;
    }
} else if (isset($_SESSION['reset_email'])) {
} else {
    header('Location: remindPass.php');
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
</head>
<body>
  <!-- ヘッダー -->
  <?php require 'headerTag.php' ?>

  <!-- ログインカード -->
  <main class="login-container">
    <div class="login-card">
      <h2 class="section-title">パスワードの再設定</h2>
      <p class="login-description">
        入力したメールアドレスに届いた<br>
        6桁のコードを入力してください。
      </p>

      <?php
      if (isset($_GET['error']) && $_GET['error'] === 'code_invalid') {
          echo '<p style="color: red;">コードが違います。</p>';
      }
      ?>

      <form action="resetPass.php" method="post" class="login-form">
        <div class="form-group">
          <label for="number">コード</label>
          <input type="text" id="number" name="number">
        </div>

        <button type="submit" class="product-btn">送信</button>
      </form>
    </div>
  </main>

  <!-- フッター -->
  <?php require "footer.php"; ?>
</body>
</html>
