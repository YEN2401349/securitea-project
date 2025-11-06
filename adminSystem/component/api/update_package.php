<?php
include("../DBconnect.php");

$input = json_decode(file_get_contents('php://input'), true);

$imagePath = null;

if ($input['id']) {
    $stmt = $db->prepare("SELECT image_path FROM Products WHERE product_id = ?");
    $stmt->execute([$input['id']]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldImagePath = $old ? $old['image_path'] : null;
}

if (!empty($input['package_image'])) {
    $uploadDir = '../../products_img/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $base64 = $input['package_image'];

    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
        $type = $matches[1];
        $base64 = substr($base64, strpos($base64, ',') + 1);
        $base64 = str_replace(' ', '+', $base64);
        $data = base64_decode($base64);

        if ($data === false) {
            die(json_encode(['success' => false, 'message' => '画像データのデコードに失敗しました']));
        }

        $fileName = time() . '.' . $type;
        $filePath = $uploadDir . $fileName;
        file_put_contents($filePath, $data);
        $imagePath = 'products_img/' . $fileName;

        if (!empty($oldImagePath)) {
            $realOldPath = __DIR__ . '/../../' . ltrim($oldImagePath, './');

            if (file_exists($realOldPath)) {
                unlink($realOldPath);
            }
        }

    }
}


$stmt = $db->prepare("UPDATE Products
    SET name=?, price=?, billing_cycle=?, duration_months=?, description=?, image_path=?
    WHERE product_id=?");

$stmt->execute([
    $input['package_name'],
    $input['package_price'],
    $input['package_billing_cycle'],
    $input['package_duration_months'],
    $input['package_description'],
    $imagePath,
    $input['id']
]);

echo json_encode(['success' => true]);
exit;
?>