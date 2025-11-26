<?php
include("../../../common/DBconnect.php");
$input = json_decode(file_get_contents('php://input'), true);

$imagePath = null;

if (!empty($input['custom_image'])) {
    $uploadDir = '../../products_img/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $base64 = $input['custom_image'];

    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
        $type = $matches[1];
        $base64 = substr($base64, strpos($base64, ',') + 1);
        $base64 = str_replace(' ', '+', $base64);
        $data = base64_decode($base64);

        if ($data === false) {
            echo json_encode(['success' => false, 'message' => '画像データのデコードに失敗しました']);
            exit;
        }

        $fileName = time() . '.' . $type;
        $filePath = $uploadDir . $fileName;
        file_put_contents($filePath, $data);
        $imagePath = 'products_img/' . $fileName;
    }
}

try {
    $stmt = $db->prepare("
        INSERT INTO Products (name, price, billing_cycle, security_features,eye_catch, duration_months, category_id, description, image_path)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $input['custom_name'],
        $input['custom_price'],
        $input['custom_billing_cycle'],
        $input['custom_security_features'],
        $input['custom_eye_catch'],
        $input['custom_duration_months'],
        2,
        $input['custom_description'],
        $imagePath
    ]);

    echo json_encode([
        'success' => true,
        'data' => ['id' => $db->lastInsertId()]
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage(), 'success' => false]);
}
exit;
?>