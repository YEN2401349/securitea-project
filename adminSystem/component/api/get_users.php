<?php include("../DBconnect.php");
try {
    $stmt = $db->prepare("SELECT p.full_name,u.user_email,u.update_date FROM Users u ,Profiles p WHERE u.user_id = p.user_id AND u.role = 'user';");
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $products]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>