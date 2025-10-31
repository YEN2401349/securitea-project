<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>管理者ログイン</title>
  <link rel="stylesheet" href="./css/login.css">
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="brand">
        <img src="./img/toukalogo.png" alt="管理画面ロゴ">
        <h1>管理者ログイン</h1>
      </div>
      <form id="loginForm">
        <div class="field">
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required>
          <span id="emailError" class="error">
            ユーザー名またはパスワードが正しくありません。
          </span>
        </div>
        <div class="field">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" placeholder="パスワード" required>
        </div>
        <div class="forget">
          <a href="./passwordForget.php">パスワードを忘れた場合</a>
        </div>
        <button type="submit" class="btn">ログイン</button>
      </form>
        <button onclick="location.href='./register.php'" class="signup">新規登録</button>
    </div>
  </div>
</body>
</html>

<script src="./script/login.js"></script>