<?php
include("../DBconnect.php");
$input = json_decode(file_get_contents('php://input'), true);

$stmt = $db->prepare("UPDATE Products
                       SET name=?, price=?, billing_cycle=?, duration_months=?, description=?
                       WHERE product_id=?");
$stmt->execute([$input['package_name'], $input['package_price'], $input['package_billing_cycle'], $input['package_duration_months'], $input['package_description'], $input['id']]);

echo json_encode(['success' => true]);
exit; ?>