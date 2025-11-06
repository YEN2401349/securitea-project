<?php include("../DBconnect.php");
try {
    $stmt = $db->prepare("SELECT
    product_id,
    name,
    description,
    price,
    billing_cycle,
    CASE
        WHEN billing_cycle = 'monthly' THEN '月付'
        WHEN billing_cycle = 'yearly' THEN '年付'
        WHEN billing_cycle = 'lifetime' THEN '買い切り'
    END AS plan_type,
    duration_months,
    category_id,
    image_path,
    created_date,
    updated_date
FROM Products
WHERE category_id = 1
ORDER BY product_id;");
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $products]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>