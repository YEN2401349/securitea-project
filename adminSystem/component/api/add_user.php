<?php
include("../../../common/DBconnect.php");
try {
    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO Users (user_email, user_password, role) VALUES (?, ?, 'admin')");
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt->execute([$_POST['email'], $hashed]);

    $user = [
        'id' => $db->lastInsertId(),
        'email' => htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'),
        'role' => 'admin',
        'name' => htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8')
    ];

    $stmt = $db->prepare("INSERT INTO Profiles (user_id, full_name) VALUES (?, ?)");
    $stmt->execute([$user['id'], $_POST['name']]);
    $db->commit();

    echo json_encode(['data' => $user, 'success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage(), 'success' => false]);
    $db->rollBack();
}
?>