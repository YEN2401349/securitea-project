<?php include("component/header.php"); ?>
<link rel="stylesheet" href="./css/dashboard.css">

<main class="main-content">

    <header class="main-header">
        <div class="user-info"><span>管理者: 山田太郎</span><a href="#">ログアウト</a></div>
    </header>
    <header class="flex  mb-4">
        <h1>ダッシュボード</h1>
    </header>

    <section class="cards">
        <div class="card">
            <h3>ユーザー数</h3>
            <p>1,234 人</p>
        </div>
        <div class="card">
            <h3>総売上</h3>
            <p>¥567,890</p>
        </div>
        <div class="card">
            <h3>未処理メッセージ</h3>
            <p>5 件</p>
        </div>
    </section>
</main>

<?php include("component/footer.php"); ?>