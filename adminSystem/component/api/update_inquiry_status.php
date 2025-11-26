<?php
include("../../../common/DBconnect.php");
$data = json_decode(file_get_contents("php://input"), true);

$id = $data["id"] ?? 0;
$status = $data["status"] ?? "";

if (!$id || !$status) {
    echo json_encode(["success" => false, "message" => "invalid input"]);
    exit;
}

$stmt = $db->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
$ok = $stmt->execute([$status, $id]);

echo json_encode(["success" => $ok]);
?>
