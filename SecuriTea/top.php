<?php session_start(); ?>
<?php require "DBconnect.php"; ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecuriTea(セキュリティー)</title>
    <link rel="stylesheet" href="css/top-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <!--ヘッダータグ -->
    <?PHP require 'headerTag.php'?>

    <main class="main">
        <div class="container">
            <section class="hero">
                <h1 class="hero-title">
                    <span class="hero-title-main">パソコン初心者の</span>
                    <span class="hero-title-sub">あなたへ</span>
                </h1>
                <p class="hero-description">
                    最先端のセキュリティソフトが、あなたのパソコンを守ります
                </p>
                <!--検索機能（未完成）-->
                <div class="search-section">
                    <div class="search-container">
                        <div class="search-bar">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" placeholder="特徴でセキュリティソフトを探す" class="search-input">
                            <button class="search-btn">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <!--hero-title-sub商品項目パッケージ３つ表示（未完成）-->
            <section class="products">
                <h2 class="section-title">商品一例</h2>
                <div class="products-grid">
                    <?php
                        $count = 0;
                        $data=$db->query("select * FROM Products where category_id = 1 ORDER BY RAND() LIMIT 3");
                        foreach($data as $value){
                                echo "<div class='product-card'>",
                                        "<div class='product-image'>",
                                            "<img src='images/PackIcon/",$value["image_path"],"' alt='",$value["name"],"'>",
                                        "</div>",
                                        "<div class='product-content'>",
                                            "<h3 class='product-title'>",$value["name"],"</h3>",
                                            "<p class='product-description'>",$value["description"],"</p>",
                                            "<p>DB出力</p>",
                                            "<a href='product.php' class='product-btn'>",
                                                "<span>詳細を見る</span>",
                                                "<i class='fas fa-arrow-right'></i>",
                                            "</a>",
                                        "</div>",
                                     "</div>";
                        }
                    ?>
                    <!--カスタムページに遷移-->
                    <div class="product-card">
                        <div class="product-image">
                            <img src="images/CostomIcon/costomIcon.png" alt="商品画像3">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">カスタム</h3>
                            <p class="product-description">必要な分だけ</p>
                            <div class="product-tags">
                                <span class="tag">#表示最低限</span>
                                <span class="tag">#お好み</span>
                            </div>
                            <a href="custom.php" class="product-btn">
                                <span>詳細を見る</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <!--フッターとチャットボット-->
    <?php require "footer.php"; ?>
    <?php include './component/chatBot.php'; ?>
</body>

</html>