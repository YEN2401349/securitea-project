<?php
session_start();
require 'DBconnect.php'; 
if(isset($_SESSION["costomer"])){
    header("Location: cart.php");
}
else{
    header('Location: login.php');
    echo "<script>alert('先にログインしてください')</script>";
}
?>