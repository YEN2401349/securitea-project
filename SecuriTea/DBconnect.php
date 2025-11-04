<?php
const DB_HOST = 'mysql326.phy.lolipop.lan';
const DB_NAME = 'LAA1607550-securitea';
const DB_CHARSET = 'utf8';
const DB_USER = 'LAA1607550';
const DB_PASS = '0000';


try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "データベース接続に失敗しました: " . $e->getMessage();
    exit;
}
?>
