<?php include("component/header.php"); ?>
<link rel="stylesheet" href="./css/dashboard.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
<div style="width:80%; max-width:1000px; margin:50px auto;">
    <h2 style="text-align:center;">📈 年間売上折線図</h2>
    <div style="text-align:right; margin-bottom:10px;">
        <label for="yearSelect">年度選択：</label>
        <select id="yearSelect" class="yearSelect">
        </select>
    </div>
    <canvas id="salesChart"></canvas>
</div>

<script src="./script/dashboard.js"></script>
<?php include("component/footer.php"); ?>