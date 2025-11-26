<?php include("../../../common/DBconnect.php");
try {
    $stmt = $db->prepare("SELECT count(*) AS user_count FROM Users u WHERE u.role = 'user';");
    $stmt->execute();

    $userCount = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $userCount]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>