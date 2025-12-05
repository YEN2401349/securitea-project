<?php
session_start();
require "../common/DBconnect.php"; //

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) { //
  header("Location: login.php");
  exit();
}

$pdo = $db; //
$user_id = $_SESSION['customer']['user_id']; //
$errors = []; //

// --- ▼ POSTリクエスト（フォーム送信）処理 ▼ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') { //
    try {
        // 入力値の受け取り
        $full_name = trim($_POST['full_name'] ?? ''); //
        $gender = $_POST['gender'] ?? ''; //
        $user_email = trim($_POST['user_email'] ?? ''); //
        $phone1 = trim($_POST['phone1'] ?? ''); //
        $phone2 = trim($_POST['phone2'] ?? ''); //
        $phone3 = trim($_POST['phone3'] ?? ''); //

        // (バリデーション)
        if (empty($full_name)) { //
            $errors[] = '名前は必須です。'; //
        }

        // メールチェック
        if (empty($user_email)) { //
            $errors[] = 'メールアドレスは必須です。'; //
        } else if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) { //
            $errors[] = '正しいメール形式で入力してください。'; //
        }

        // ▼ 電話番号バリデーション ("0" を空と誤判定しないように)
        if ($phone1 === '' || $phone2 === '' || $phone3 === '') { //
            $errors[] = '電話番号は3つの欄すべてに入力してください。'; //
        }
        
        // エラーがなければDB更新
        if (empty($errors)) { //
            $phone_combined = $phone1 . '-' . $phone2 . '-' . $phone3; //

            $pdo->beginTransaction(); //

            // 1. Profiles テーブル更新
            $sql_profile = $pdo->prepare("UPDATE Profiles SET full_name = ?, gender = ?, phone = ? WHERE user_id = ?"); //
            $sql_profile->execute([$full_name, $gender, $phone_combined, $user_id]); //

            // 2. Users テーブル更新 (メール)
            $sql_user_email = $pdo->prepare("UPDATE Users SET user_email = ? WHERE user_id = ?"); //
            $sql_user_email->execute([$user_email, $user_id]); //

            $pdo->commit(); //

            header("Location: account.php"); //
            exit(); //
        }
        
        // エラーがあった場合、POSTの値を保持するロジックは削除 (常にDBの値を表示するため)

    } catch (PDOException $e) { //
        $pdo->rollBack(); //
        $errors[] = "更新に失敗しました：" . $e->getMessage(); //
    }
}
// --- ▲ POSTリクエスト処理ここまで ▲ ---


// --- ▼ フォーム表示処理 (GETリクエスト時 / POSTエラー時 共通) ▼ ---
// ★エラーの有無にかかわらず、常にDBから最新の情報を取得する★
try {
  // 1. DBから最新のユーザー情報を取得 (ご要望のSQL)
  $sql_user = $pdo->prepare("
      SELECT p.full_name, p.gender, p.phone, u.user_email 
      FROM Profiles p
      JOIN Users u ON p.user_id = u.user_id
      WHERE p.user_id = ?
  "); //
  $sql_user->execute([$user_id]); //
  $user = $sql_user->fetch(PDO::FETCH_ASSOC); //

  // フォーム表示用変数にDBの値を入れる
  $name_val = $user['full_name'] ?? ''; //
  $gender_val = $user['gender'] ?? ''; //
  $email_val = $user['user_email'] ?? ''; //

  // 電話番号を3つに分割
  $phone_parts = explode('-', $user['phone'] ?? ''); //
  $phone1_val = $phone_parts[0] ?? ''; //
  $phone2_val = $phone_parts[1] ?? ''; //
  $phone3_val = $phone_parts[2] ?? ''; //

} catch (PDOException $e) { //
  // ユーザー情報取得失敗
  echo "エラー：" . $e->getMessage(); //
  exit(); //
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>アカウント情報の修正</title>
  <link rel="stylesheet" href="css/account.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"> </head>

<body>
  <?php require "headerTag.php"; ?> 
    <div class="container"> 
        <main class="content"> 
            <div class="card"> 
                <form action="edit_account.php" method="POST"> 
                    <h2>個人情報</h2>
                    <div class="form-group"> 
                        <label for="full_name">名前</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($name_val) ?>" required>
                     </div>

                    <div class="form-group">
                        <label for="gender">性別</label>
                        <select id="gender" name="gender"> 
                            <option value="男性" <?= ($gender_val === '男性') ? 'selected' : '' ?>>男性</option>
                            <option value="女性" <?= ($gender_val === '女性') ? 'selected' : '' ?>>女性</option> 
                            <option value="その他" <?= ($gender_val === 'その他') ? 'selected' : '' ?>>その他</option> 
                        </select>
                    </div>

                    <h2>連絡先情報</h2> 
                    <div class="form-group"> 
                        <label for="user_email">メール</label> 
                        <input type="email" id="user_email" name="user_email" value="<?= htmlspecialchars($email_val) ?>" required> 
                    </div>

                    <div class="form-group"> 
                        <label for="phone1">電話</label> 
                        <div class="phone-inputs">
                        <input type="tel" id="phone1" name="phone1" value="<?= htmlspecialchars($phone1_val) ?>" placeholder="090" maxlength="4" pattern="[0-9]{2,4}" required> <span>-</span> 
                        <input type="tel" id="phone2" name="phone2" value="<?= htmlspecialchars($phone2_val) ?>" placeholder="1234" maxlength="4" pattern="[0-9]{2,4}" required> <span>-</span> 
                        <input type="tel" id="phone3" name="phone3" value="<?= htmlspecialchars($phone3_val) ?>" placeholder="5678" maxlength="4" pattern="[0-9]{2,4}" required> </div>
                    </div>


                <hr class="form-divider"> <div class="form-actions-split"> <a href="account.php" class="btn btn-secondary btn-split-1">戻る</a> <button type="submit" class="btn btn-primary btn-split-3">修正を完了する</button> </div>

            </form>
            </div>
    </main>
</body>
</html>