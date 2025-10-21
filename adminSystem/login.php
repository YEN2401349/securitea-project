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
        <img src="../img/toukalogo.png" alt="管理画面ロゴ">
        <h1>管理者ログイン</h1>
      </div>
       <form action="../管理者トップページ/dashboard.html" method="get">
        <div class="field">
          <label for="email">メールアドレス</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required>
        </div>
        <div class="field">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" placeholder="パスワード" required>
        </div>
        <button type="submit" class="btn">ログイン</button>
      </form>
    </div>
  </div>
</body>
</html>
