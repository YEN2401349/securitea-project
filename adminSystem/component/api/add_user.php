<?php
include("../DBconnect.php");
try {
    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO Users (user_email, user_password, role) VALUES (?, ?, 'admin')");
    $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt->execute([$_POST['email'], $hashed]);

    $user = [
        'id' => $db->lastInsertId(),
        'email' => $_POST['email'],
        'role' => 'admin',
        'name' => $_POST['name']
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