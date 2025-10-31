<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワードを忘れた場合</title>
    <link rel="stylesheet" href="./css/login.css">
</head>

<body>
    <div class="wrap">
        <div class="card">
            <div class="brand">
                <img src="./img/toukalogo.png" alt="管理画面ロゴ">
                <h1>パスワードを忘れた場合</h1>
            </div>

            <form id="forgotForm">
                <div class="field">
                    <label for="email">登録メールアドレス</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    <span id="emailError" class="error">
                        入力されたメールアドレスが登録されていません。
                    </span>
                </div>

                <p class="info-text">
                    登録されたメールアドレス宛に、パスワード再設定用のリンクを送信します。
                </p>

                <button type="submit" class="btn">再設定メールを送信</button>
            </form>

            <button onclick="location.href='./login.php'" class="signup">ログイン画面に戻る</button>
        </div>
    </div>

    <script src="./script/passwordForget.js"></script>
</body>

</html>