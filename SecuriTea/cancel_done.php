<?php
session_start();

// ログインしていない場合はログインページへ
if (!isset($_SESSION['customer']['user_id'])) {
  header("Location: login.php");
  exit();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>契約解除完了</title>
  <link rel="stylesheet" href="css/login-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <?php require "headerTag.php"; ?>

  <div class="containerA">
    <div class="card">
      <i class="fa-solid fa-circle-check" style="font-size:3rem; color:#28a745; margin-bottom:10px;"></i>
      <h1>契約解除が完了しました</h1>
      <p>ご利用ありがとうございました。<br>
         アカウント情報ページから契約内容を確認できます。</p>

      <a href= "account.php" class="btn">アカウント情報へ戻る</a>
    </div>
  </div>

  <?php require "footer.php"; ?>
</body>
</html>
