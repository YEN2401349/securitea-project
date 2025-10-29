<?php
include("../DBconnect.php");
$input = json_decode(file_get_contents('php://input'), true);

$stmt = $db->prepare("INSERT INTO Products (name, price, billing_cycle, duration_months, category_id , description)
                       VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$input['custom_name'], $input['custom_price'], $input['custom_billing_cycle'], $input['custom_duration_months'],'2', $input['custom_description']]);

echo json_encode(['success' => true, 'data' => ['id' => $db->lastInsertId()]]);
exit; ?>
