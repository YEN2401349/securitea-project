<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理システム</title>
    <link rel="stylesheet" href="./component/css/sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/jwt-decode/build/jwt-decode.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body>
    <?php include("component/sidebar.php"); ?>
    <main class="main-content">

        <header class="main-header">
            <div class="user-info"><span id="admin_name"></span><button class="logout-btn">
                    <ion-icon name="log-out-outline"></ion-icon>
                    ログアウト
                </button></div>
        </header>

        <script src="./component/script/header.js"></script>