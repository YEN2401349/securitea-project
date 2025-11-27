<?php
include("../../../common/DBconnect.php");
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? '';
$email = $data['email'] ?? '';

if (empty($token) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'token または email が不足しています']);
    exit;
}

try {
    $stmt = $db->prepare("SELECT reset_token, reset_token_expire FROM Users WHERE user_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'ユーザーが存在しません']);
        exit;
    }

    if ($user['reset_token'] !== $token) {
        echo json_encode(['success' => false, 'message' => 'token が正しくありません']);
        exit;
    }


    $currentTime = new DateTime();
    $expireTime = new DateTime($user['reset_token_expire']);
    if ($currentTime > $expireTime) {
        echo json_encode(['success' => false, 'message' => 'token の有効期限が切れています']);
        exit;
    }

    $password = $data['newPassword'] ?? '';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE Users SET reset_token = NULL, reset_token_expire = NULL,user_password = ? WHERE user_email = ?");
    $stmt->execute([$hashedPassword,$email]);
    echo json_encode(['success' => true, 'message' => 'パスワードが更新されました']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>