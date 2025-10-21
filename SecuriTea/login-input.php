<?php session_start(); include './component/header.php'; ?>
<link rel="stylesheet" href="./css/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<main class="login-container">
    <div class="login-card">
        <h2 class="section-title">ログイン</h2>
        <p class="login-description">
            SecuriTea のサービスをご利用いただくには、<br>
            アカウントにログインしてください。
        </p>

        <form action="login-output.php" method="post" class="login-form">
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" required placeholder="メールアドレス">
                <?php
                if (!empty($_SESSION['error']) && $_SESSION['error'] === '登録されていないメールアドレスです') {
                    echo '<p class="error">' . htmlspecialchars($_SESSION['error']) . '</p>';

                    unset($_SESSION['error']);
                } ?>
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required placeholder="パスワード">
                <?php
                if (!empty($_SESSION['error']) && $_SESSION['error'] === 'パスワードが間違っています') {
                    echo '<p class="error">' . htmlspecialchars($_SESSION['error']) . '</p>';

                    unset($_SESSION['error']);
                } ?>
            </div>

            <a href="#" class="forgot-password">パスワードをお忘れですか？</a>

            <button type="submit" class="product-btn">続ける</button>
        </form>

        <p class="register">
            アカウントが未登録ですか？ <a href="add_account.html">アカウントの作成</a>
        </p>
    </div>
</main>
<?php include './component/footer.php'; ?>