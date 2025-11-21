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
  <link rel="stylesheet" href="css/heder-footer.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    .containerA {
      text-align: center;
      margin: 100px auto;
      max-width: 600px;
      padding: 40px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .containerA h1 {
      color: #007bff;
      font-size: 1.8rem;
      margin-bottom: 1rem;
    }
    .containerA p {
      color: #333;
      line-height: 1.8;
      margin-bottom: 2rem;
    }
    .containerA a {
      display: inline-block;
      background-color: #007bff;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 6px;
      text-decoration: none;
      transition: 0.2s;
    }
    .containerA a:hover {
      background-color: #0056b3;
      transform: translateY(-2px);
    }
  </style>
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
