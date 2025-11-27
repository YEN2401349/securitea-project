<?php include("../../../common/DBconnect.php");
try {
    $stmt = $db->prepare("SELECT
    p.product_id,
    p.name,
    p.description,
    p.price,
    p.billing_cycle,
    p.security_features,
    p.eye_catch,
    CASE
        WHEN p.billing_cycle = 'monthly' THEN '月付'
        WHEN p.billing_cycle = 'yearly' THEN '年付'
        WHEN p.billing_cycle = 'lifetime' THEN '買い切り'
    END AS plan_type,
    p.duration_months,
    CASE 
       WHEN c.category_name = 'plan' THEN 'プラン'
       WHEN c.category_name = 'option' THEN 'カスタム'
    END AS category_name
FROM Products p
left join Categories c on p.category_id = c.category_id
ORDER BY p.product_id;");
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $products]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>