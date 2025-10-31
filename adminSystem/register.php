<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規登録</title>
    <link rel="stylesheet" href="./css/register.css">
</head>

<body>
    <div class="wrap">
        <div class="card">
            <div class="brand">
                <img src="./img/toukalogo.png" alt="管理画面ロゴ">
                <h1>新規登録</h1>
            </div>

            <form id="registerForm">
                <div class="field">
                    <label for="name">ユーザー名</label>
                    <input type="text" id="name" name="name" placeholder="例：山田太郎" required>
                </div>

                <div class="field">
                    <label for="employee_id">社員番号</label>
                    <input type="text" id="employee_id" name="employee_id" placeholder="例：A12345" required>
                    <span id="employee_idError" class="error">有効な社員番号を入力してください。</span>
                </div>

                <div class="field">
                    <label for="email">メールアドレス</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    <span id="emailError" class="error">有効なメールアドレスを入力してください。</span>
                </div>

                <div class="field">
                    <label for="password">パスワード</label>
                    <input type="password" id="password" name="password" placeholder="パスワード（8文字以上）" minlength="8"
                        required>
                </div>

                <div class="field">
                    <label for="confirmPassword">パスワード（確認）</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="もう一度入力してください"
                        required>
                    <span id="passwordError" class="error">パスワードが一致しません。</span>
                </div>

                <button type="submit" class="btn">登録する</button>
            </form>

            <button onclick="location.href='./login.php'" class="backBtn">ログイン画面へ戻る</button>
        </div>
    </div>

    <script src="./script/register.js"></script>
</body>

</html>