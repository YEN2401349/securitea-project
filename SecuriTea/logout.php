<?php
session_start();
unset($_SESSION['login_state']);
unset($_SESSION['user_email']);
header("Location: index.php");
?>
