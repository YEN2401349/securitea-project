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
  <style>
    .done-container {
      text-align: center;
      margin: 100px auto;
      max-width: 600px;
      padding: 40px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .done-container h1 {
      color: #007bff;
      font-size: 1.8rem;
      margin-bottom: 1rem;
    }
    .done-container p {
      color: #333;
      line-height: 1.8;
      margin-bottom: 2rem;
    }
    .done-container a {
      display: inline-block;
      background-color: #007bff;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 6px;
      text-decoration: none;
      transition: 0.2s;
    }
    .done-container a:hover {
      background-color: #0056b3;
      transform: translateY(-2px);
    }
  </style>
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

  <footer class="footer">
    <div class="container">
      <p>&copy; 2025 Modern Securitea. All Rights Reserved.</p>
    </div>
  </footer>
</body>
</html>
