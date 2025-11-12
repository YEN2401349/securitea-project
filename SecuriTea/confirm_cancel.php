<?php
session_start();
require "DBconnect.php";

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
  header("Location: login.php");
  exit();
}

if (isset($_SESSION['customer']['user_type']) && $_SESSION['customer']['user_type'] == 'custom') {
  $back_url = 'account_custom.php';
} else {
  $back_url = 'account_normal.php';
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>契約解除の確認</title>
  <link rel="stylesheet" href="css/account.css">
</head>
<body>
  <div class="container">
    <main class="content">
      <div class="card" style="text-align:center;">
        <h2>契約解除の確認</h2>
        <p>本当に契約を解除しますか？<br>この操作は取り消せません。</p>

        <div class="card-actions" style="justify-content:center;">
          <form action="cancel_process.php" method="post" style="display:inline;">
            <button type="submit" class="btn btn-danger">はい、解除します</button>
          </form>
          
          <?php echo'<a href= "',$back_url,'" class="btn btn-secondary">いいえ、戻る</a>';?>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
