<?php

require_once("../DBconnect.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";

if (empty($email)) {
    echo json_encode(["success" => false, "message" => "メールアドレスが未入力です。"]);
    exit;
}

$stmt = $db->prepare("SELECT * FROM Users WHERE user_email = ? AND role = 'admin'");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(["success" => false, "message" => "該当する管理者が見つかりません。"]);
    exit;
}
$user_id = $user['user_id'];
$stmt = $db->prepare("SELECT full_name FROM Profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$full_name = $user['full_name'];
$token = bin2hex(random_bytes(16));
$expire = date("Y-m-d H:i:s", strtotime("+1 hour"));

$stmt = $db->prepare("UPDATE Users SET reset_token = ?, reset_token_expire = ? WHERE user_email = ?");
$stmt->execute([$token, $expire, $email]);

$reset_link = "https://aso-2401349.greater.jp/adminSystem//reset_password.php?token=" . urlencode($token)."&email=" . urlencode($email);

$mail = new PHPMailer(true);

try {
    $message = <<<EOT
{$full_name} 様

パスワード再設定をご希望いただきありがとうございます。
以下のリンクをクリックして、新しいパスワードを設定してください。

{$reset_link}

このリンクの有効期限は1時間です。

-----------------------------
管理者システム サポート
EOT;
    $mail->isSMTP();
    $mail->Host = 'smtp.lolipop.jp';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply@aso-2401349.greater.jp';
    $mail->Password = 'A7b9-K2_m4-Q8x1';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('noreply@aso-2401349.greater.jp', '管理者システム');
    $mail->addAddress($email);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->isHTML(false);
    $mail->Subject = '【管理者システム】パスワード再設定リンク';
    $mail->Body = $message;

    $mail->send();
    echo json_encode(["success" => true, "message" => "再設定リンクを送信しました。"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "メール送信失敗: " . $mail->ErrorInfo]);
}
?>