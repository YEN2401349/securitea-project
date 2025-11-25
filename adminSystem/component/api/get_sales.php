<?php include("../DBconnect.php");
try {
    $stmt = $db->prepare("SELECT 
  DATE(created_at) AS order_date,
  product_name,
  price,
  SUM(o.quantity) AS total_quantity,
  category_id
FROM Order_Items 
GROUP BY DATE(created_at),product_name, price 
ORDER BY order_date ASC;");
    $stmt->execute();

    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $sales]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>