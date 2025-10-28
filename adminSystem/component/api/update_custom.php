<?php
include("../DBconnect.php");
$input = json_decode(file_get_contents('php://input'), true);

$stmt = $db->prepare("UPDATE Products
                       SET name=?, price=?, billing_cycle=?, duration_months=?, description=?
                       WHERE product_id=?");
$stmt->execute([$input['name'], $input['price'], $input['billing_cycle'], $input['duration_months'], $input['description'], $input['id']]);

echo json_encode(['success' => true]);
exit; ?>