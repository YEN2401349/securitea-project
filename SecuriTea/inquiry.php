<?php
require 'DBconnect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $product = $_POST["product"];
    $subject = $_POST["subject"];
    $message = $_POST["message"];

    $full_subject = $subject . ' ' . $product;

    $sql = "INSERT INTO Inquiries (name, email, subject, message, created_at)
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute([$name, $email, $full_subject, $message]);

    if ($success) {
        // ✅ 完了ページへリダイレクト
        header("Location: inquiry_done.php");
        exit;
    } else {
        $msg = "送信に失敗しました。もう一度お試しください。";
    }
}
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ</title>
    <link rel="stylesheet" href="css/login-style.css">
    <link rel="stylesheet" href="css/heder-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php require 'headerTag.php' ?>

<main>
    <div class="login-container">
        <div class="login-card">
            <h1 class="section-title">お問い合わせ</h1>

            <?php if (!empty($msg)): ?>
                <p style="color:green; text-align:center;"><?= htmlspecialchars($msg) ?></p>
            <?php endif; ?>

            <form class="login-form" action="" method="POST">
                <div class="form-group">
                    <label for="name">お名前</label>
                    <input type="text" id="name" name="name" placeholder="山田 太郎" required>
                </div>

                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input type="email" id="email" name="email" placeholder="example@securitea.com" required>
                </div>

                <div class="form-group">
                    <label for="product">製品</label>
                    <input type="text" id="product" name="product" placeholder="パッケージA" required>
                </div>

                <div class="form-group">
                    <label for="subject">件名</label>
                    <input type="text" id="subject" name="subject" placeholder="製品に関するご質問" required>
                </div>

                <div class="form-group">
                    <label for="message">お問い合わせ内容</label>
                    <textarea id="message" name="message" rows="6" placeholder="お問い合わせ内容をこちらにご記入ください。" required></textarea>
                </div>

                <button type="submit" class="product-btn">送信する</button>
            </form>
        </div>
    </div>
</main>

<!--フッターとチャットボット-->
    <?php require "footer.php"; ?>
    <?php include './component/chatBot.php'; ?>

</body>
</html>
