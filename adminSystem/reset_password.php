<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード再設定</title>
    <link rel="stylesheet" href="./css/reset_password.css">
</head>

<body>
    <div class="wrap">
        <div class="card">
            <div class="brand">
                <img src="./img/toukalogo.png" alt="管理画面ロゴ">
                <h1>パスワード再設定</h1>
            </div>

            <form id="resetForm">
                <div class="field">
                    <label for="email">登録メールアドレス</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="field">
                    <label for="newPassword">新しいパスワード</label>
                    <input type="password" id="newPassword" name="newPassword" placeholder="新しいパスワード" required>
                </div>

                <div class="field">
                    <label for="confirmPassword">新しいパスワード（確認）</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="もう一度入力" required>
                    <span id="passwordError" class="error">
                        パスワードが一致しません。
                    </span>
                </div>

                <button type="submit" class="btn">パスワードを再設定</button>
            </form>

            <button onclick="location.href='./login.php'" class="signup">ログイン画面に戻る</button>
        </div>
    </div>

    <script src="./script/reset_password.js"></script>
</body>

</html>