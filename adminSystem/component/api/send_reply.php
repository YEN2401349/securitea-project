<?php
require_once("../DBconnect.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

$data = json_decode(file_get_contents("php://input"), true);
$inquiry_id = $data['inquiry_id'];
$adminMessage = $data['message'];
$userName = $data['userName'];
$userEmail = $data['userEmail'];
$adminName = $data['adminName'];
$userSubject = $data['subject'];


$stmt = $db->prepare("INSERT INTO Inquiries (parent_id, name, message) VALUES (?, ?, ?)");
$stmt->execute([$inquiry_id, $adminName, $adminMessage]);


$stmt = $db->prepare("UPDATE Inquiries SET status = ? WHERE inquiry_id = ?");
$stmt->execute(["完了", $inquiry_id]);

$mail = new PHPMailer(true);

try {
    $message = <<<EOT
{$userName} 様

この度はお問い合わせいただき、誠にありがとうございます。

以下の内容でご回答申し上げます。

────────────────────
{$adminMessage}
────────────────────

ご不明点がございましたら、お気軽にお問い合わせください。

-----------------------------------
SecuriTea株式会社　カスタマーサポート
EOT;

    $mail->isSMTP();
    $mail->Host = 'smtp.lolipop.jp';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply@aso-2401349.greater.jp';
    $mail->Password = 'A7b9-K2_m4-Q8x1';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('noreply@aso-2401349.greater.jp', 'SecuriTea　カスタマーサポート');
    $mail->addAddress($userEmail);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->isHTML(false);
    $mail->Subject = 'Re: ' . $userSubject;;
    $mail->Body = $message;

    $mail->send();
    echo json_encode(["success" => true, "message" => "お問い合わせへの返信メールを送信しました。"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "メール送信失敗: " . $mail->ErrorInfo]);
}
?>
