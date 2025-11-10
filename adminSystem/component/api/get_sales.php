<?php include("../DBconnect.php");
try {
    $stmt = $db->prepare("SELECT 
  DATE(o.created_at) AS order_date,
  p.name,
  p.price,
  SUM(o.quantity) AS total_quantity,
  p.category_id
FROM Order_Items o
LEFT JOIN Products p ON o.product_id = p.product_id
GROUP BY DATE(o.created_at), p.name, p.price 
ORDER BY order_date ASC;");
    $stmt->execute();

    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $sales]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>