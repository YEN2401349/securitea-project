<?php
$config = parse_ini_file(__DIR__ . '/config.ini');

$db_host = $config['host'];
$db_name = $config['dbname'];
$db_charset = $config['charset'];
$db_user = $config['user'];
$db_pass = $config['password'];

try {
    $db = new PDO(
        'mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=' . $db_charset,
        $db_user,
        $db_pass
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "データベース接続に失敗しました: " . $e->getMessage();
    exit;
}
?>
