<?php include("DBconnect.php");
try {
    $stmt = $db->prepare('SELECT * FROM User');
    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>