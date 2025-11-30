<?php
session_start();
// ログアウト処理
session_unset();
session_destroy();
header("Location: top.php");
exit();
?>