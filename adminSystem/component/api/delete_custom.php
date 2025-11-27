<?php
include("../../../common/DBconnect.php");
$input = json_decode(file_get_contents('php://input'), true);

if (!empty($input['id'])) {

    $stmt = $db->prepare("SELECT image_path FROM Products WHERE product_id = ?");
    $stmt->execute([$input['id']]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldImagePath = $old ? $old['image_path'] : null;

    $stmt = $db->prepare("DELETE FROM Products WHERE product_id = ?");
    $stmt->execute([$input['id']]);


    if (!empty($oldImagePath)) {
        $realOldPath = __DIR__ . '/../../' . ltrim($oldImagePath, './');
        if (file_exists($realOldPath)) {
            unlink($realOldPath);
        }
    }

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'IDが指定されていません']);
exit;
?>