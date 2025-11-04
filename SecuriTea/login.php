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
  <?php require 'headerTag.php' ?>

  <!-- ログインカード -->
  <main class="login-container">
    <div class="login-card">
      <h2 class="section-title">ログイン</h2>
      <p class="login-description">
        SecuriTea のサービスをご利用いただくには、<br>
        アカウントにログインしてください。
      </p>

      <form action="top.php" method="post" class="login-form">
        <div class="form-group">
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="email" required placeholder="メールアドレス">
        </div>

        <div class="form-group">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" required placeholder="パスワード">
        </div>

        <a href="remindPass.html" class="forgot-password">パスワードをお忘れですか？</a>

        <button type="submit" class="product-btn">続ける</button>
      </form>

      <p class="register">
        アカウントが未登録ですか？ <a href="add_account.html">アカウントの作成</a>
      </p>
    </div>
  </main>

<!-- ログイン処理 -->
<?php
unset($_SESSION['costomer']);
$pdo = new PDO($db,DB_USER,DB_PASS);
$sql = $pdo->prepare('select * from customer where login = ?');
$sql->execute([$_POST['login']]);
foreach($sql as $row){
    if(password_verify($_POST['input_password'],$row['password']) == true){
    $_SESSION['customer'] = [
        'id' => $row['id'],'name' => $row['name'],
        'address' => $row['address'],'login' => $row['login'],
        'password' => $row['password']];
    }
}

if(isset($_SESSION['customer'])){
    echo 'いらっしゃいませ、',$_SESSION['customer']['name'],'さん。';
}else{
    echo 'ログイン名またはパスワードが違います。';
}
?>

  <!-- フッター -->
  <footer class="footer">
    <p>&copy; 2025 SecuriTea. All rights reserved.</p>
  </footer>
</body>
</html>
