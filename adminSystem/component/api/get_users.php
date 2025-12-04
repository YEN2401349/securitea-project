<?php include("../../../common/DBconnect.php");
try {
    $stmt = $db->prepare("SELECT 
    p.full_name AS full_name,
    u.user_email AS user_email,
    DATE_FORMAT(u.create_date, '%Y-%m-%d') AS create_date, 
    CONCAT(s.start_date, ' ~ ', s.end_date) AS subscription_period,
    d.name AS subscription_product_name, 
    CAST(
        COALESCE(
            d.price + SUM(c.price),   
            d.price,
            SUM(c.price),
            0
        ) AS SIGNED
    ) AS tatle_price, 
    GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS custom_product_names
FROM 
    Users u
    JOIN Profiles p 
        ON u.user_id = p.user_id
    LEFT JOIN Subscription s 
        ON s.user_id = u.user_id AND s.status_id = '1'
    LEFT JOIN Products d 
        ON s.product_id = d.product_id
    LEFT JOIN SubscriptionCustoms o 
        ON o.subscription_id = s.subscription_id
    LEFT JOIN Products c 
        ON o.product_id = c.product_id
WHERE 
    u.role = 'user'
GROUP BY 
    u.user_id
ORDER BY 
    u.update_date DESC;


");
    $stmt->execute();

    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $user]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>