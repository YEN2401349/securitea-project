<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お支払い完了</title>
    <link rel="stylesheet" href="css/login-style.css">
</head>
<body>

<main class="login-container">
    <div class="login-card">

        <h2 class="section-title">お支払いが完了しました</h2>
        <p>ご契約ありがとうございます。<br>サービスをご利用いただけます。</p>

        <div class="form-actions" style="margin-top: 2rem; display: flex; flex-direction: column; gap: 1rem;">
            <a href="account.php" class="product-btn" style="text-align:center; text-decoration:none;">
                マイページへ
            </a>

            <a href="top.php" class="product-btn" 
               style="text-align:center; text-decoration:none; background-color:#6b7280;">
                トップページへ
            </a>
        </div>

    </div>
</main>

<?php require "footer.php"; ?>

</body>
</html>
