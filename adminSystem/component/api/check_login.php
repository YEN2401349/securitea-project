<?php include("../../../common/DBconnect.php");
try {
    $stmt = $db->prepare("SELECT  u.user_id, u.user_email, u.user_password, u.role, p.full_name FROM Users u,Profiles p WHERE u.user_email = ? AND u.role = 'admin' AND p.user_id = u.user_id;");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['user_password'])) {
        unset($user['user_password']);
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'メールアドレスまたはパスワードが正しくありません。']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>