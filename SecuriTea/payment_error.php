<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お支払いエラー</title>
    <link rel="stylesheet" href="css/new-pay.css"> <style>
        .error-message {
            background: #fff0f0;
            color: #d00;
            border: 1px solid #d00;
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
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
        <h2>お支払いが完了できませんでした</h2>
        
        <?php if (isset($_SESSION['payment_error'])): ?>
            <div class="error-message">
                <?php 
                echo htmlspecialchars($_SESSION['payment_error'], ENT_QUOTES); 
                unset($_SESSION['payment_error']); // エラーメッセージを削除
                ?>
            </div>
        <?php else: ?>
            <p>不明なエラーが発生しました。</p>
        <?php endif; ?>
        
        <p>お手数ですが、もう一度お試しいただくか、サポートまでお問い合わせください。</p>
        
        <div class="form-actions">
            <a href="new-pay.php" class="submit-btn" style="text-align: center; text-decoration: none;">支払い画面に戻る</a>
            
            <a href="top.php" class="submit-btn" style="text-align: center; text-decoration: none;">トップページへ戻る</a>
        </div>
    </div>
</body>
</html>