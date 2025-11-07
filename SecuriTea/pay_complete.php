<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お支払い完了</title>
    <link rel="stylesheet" href="css/new-pay.css"> <style>
        /* 2つ目のボタン (トップへ戻る) のスタイル */
        .form-actions .submit-btn + .submit-btn {
            margin-top: 1rem; /* ボタン間の余白 */
            background-color: #6b7280; /* グレー (セカンダリボタン風) */
        }
        .form-actions .submit-btn + .submit-btn:hover {
            background-color: #4b5563; /* ホバー時の色 */
        }
    </style>
    </head>
<body>
    <div class="payment-container">
        <h2>お支払いが完了しました</h2>
        <p>カスタムプランのご契約ありがとうございます。<br>サービスをご利用いただけます。</p>
        <div class="form-actions">
            <a href="account.php" class="submit-btn" style="text-align: center; text-decoration: none;">マイページへ</a>
            
            <a href="top.php" class="submit-btn" style="text-align: center; text-decoration: none;">トップページへ戻る</a>
        </div>
    </div>
</body>
</html>