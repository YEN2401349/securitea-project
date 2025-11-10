<?php include("component/header.php"); ?>
<link rel="stylesheet" href="./css/dashboard.css">

<header class="flex mb-4">
    <h1>ダッシュボード</h1>
</header>

<section class="cards">
    <div class="card">
        <h3>ユーザー数</h3>
        <p id="userCount"></p>
    </div>
    <div class="card">
        <h3>総売上</h3>
        <p id="totalPrice"></p>
    </div>
    <div class="card">
        <h3>未処理メッセージ</h3>
        <p id="totalMail"></p>
    </div>
</section>

<script src="./script/dashboard.js"></script>
<?php include("component/footer.php"); ?>