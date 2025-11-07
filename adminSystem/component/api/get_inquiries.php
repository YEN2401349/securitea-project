<?php
include("../DBconnect.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $db->prepare("SELECT * FROM Inquiries WHERE inquiry_id = ? AND parent_id IS NULL");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
} else if (isset($_GET['inquiry_id'])) {
    $id = intval($_GET['inquiry_id']);
    $stmt = $db->prepare("SELECT * FROM Inquiries WHERE parent_id = ?");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    $stmt = $db->query("SELECT * FROM Inquiries WHERE parent_id IS NULL ORDER BY created_at DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>