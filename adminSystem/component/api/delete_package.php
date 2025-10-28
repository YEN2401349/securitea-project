<?php
include("../DBconnect.php");
$input = json_decode(file_get_contents('php://input'), true);

$stmt = $db->prepare("DELETE FROM Products WHERE product_id = ?");
$stmt->execute([$input['id']]);

echo json_encode(['success' => true, 'data' => ['id' => $db->lastInsertId()]]);
exit; ?>