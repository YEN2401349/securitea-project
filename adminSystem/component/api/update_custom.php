<?php
include("../DBconnect.php");
$input = json_decode(file_get_contents('php://input'), true);

$stmt = $db->prepare("UPDATE Products
                       SET name=?, price=?, billing_cycle=?, duration_months=?, description=?
                       WHERE product_id=?");
$stmt->execute([$input['custom_name'], $input['custom_price'], $input['custom_billing_cycle'], $input['custom_duration_months'], $input['custom_description'], $input['id']]);

echo json_encode(['success' => true]);
exit; ?>