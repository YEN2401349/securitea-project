<?php include("DBconnect.php");
try {
    $stmt = $db->prepare('SELECT * FROM Products');
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>