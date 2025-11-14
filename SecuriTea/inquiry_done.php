<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お問い合わせ完了</title>
  <link rel="stylesheet" href="css/login-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>
  <?php require 'headerTag.php'; ?>

  <main>
    <div class="done-container">
      <i class="fa-solid fa-circle-check" style="font-size:3rem; color:#28a745; margin-bottom:10px;"></i>
      <h1>お問い合わせありがとうございます！</h1>
      <p>
        お問い合わせ内容を受け付けました。<br>
        担当者より折り返しご連絡いたしますので、<br>
        今しばらくお待ちください。
      </p>
      <a href="top.php">トップページに戻る</a>
    </div>
  </main>

  <!-- フッター -->
  <?php require "footer.php"; ?>
</body>
</html>
