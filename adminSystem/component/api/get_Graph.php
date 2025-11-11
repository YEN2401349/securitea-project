<?php
include('../DBconnect.php'); 

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$stmt = $db->prepare( "
    SELECT DATE_FORMAT(created_at, '%m') AS month, 
           SUM(price * quantity) AS total_sales
    FROM Order_Items
    WHERE YEAR(created_at) = ?
    GROUP BY DATE_FORMAT(created_at, '%m')
    ORDER BY month
");
$stmt->execute([$year]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sales = array_fill(1, 12, 0);
foreach ($data as $row) {
    $sales[intval($row['month'])] = floatval($row['total_sales']);
}

echo json_encode(['year' => $year, 'sales' => array_values($sales)]);
