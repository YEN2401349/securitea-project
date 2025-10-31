<?php include("../DBconnect.php");
try {
    $stmt = $db->prepare("SELECT * FROM Users WHERE user_email = ? AND user_password = ? AND role = 'admin'");

    $stmt->execute( [$_POST['email'], $_POST['password']] );

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $user]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>