<?php
include("../DBconnect.php");
$input = json_decode(file_get_contents('php://input'), true);

$stmt = $db->prepare("INSERT INTO Products (name, price, billing_cycle, duration_months, category_id , description)
                       VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$input['name'], $input['price'], $input['billing_cycle'], $input['duration_months'],'1', $input['description']]);

echo json_encode(['success' => true, 'data' => ['id' => $db->lastInsertId()]]);
exit; ?>