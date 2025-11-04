<?php include("../DBconnect.php");
try {
    $stmt = $db->prepare("SELECT u.user_email, u.role, p.full_name FROM Users u,Profiles p WHERE u.user_email = ? AND u.user_password = ? AND u.role = 'admin' AND p.user_id = u.user_id;");

    $stmt->execute( [$_POST['email'], $_POST['password']] );

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $user]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>