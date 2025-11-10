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
  <link rel="stylesheet" href="css/account.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', 'Noto Sans JP', sans-serif;
      background-color: #f7f8fa;
      margin: 0;
      padding: 0;
    }

    .container {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
    }

    .card {
      background: #fff;
      border-radius: 12px;
      padding: 40px 60px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    h1 {
      color: #333;
      margin-bottom: 20px;
      font-size: 28px;
    }

    p {
      color: #555;
      font-size: 16px;
      margin-bottom: 30px;
    }

    .btn {
      background-color: #007bff;
      color: white;
      text-decoration: none;
      padding: 12px 30px;
      border-radius: 6px;
      transition: background 0.2s;
    }

    .btn:hover {
      background-color: #0056b3;
    }

    .icon {
      color: #28a745;
      font-size: 50px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <?php require "headerTag.php"; ?>

  <div class="container">
    <div class="card">
      <i class="fa-solid fa-circle-check icon"></i>
      <h1>契約解除が完了しました</h1>
      <p>ご利用ありがとうございました。<br>
         アカウント情報ページから契約内容を確認できます。</p>

      <a href="account.php" class="btn">アカウント情報へ戻る</a>
    </div>
  </div>
</body>
</html>
