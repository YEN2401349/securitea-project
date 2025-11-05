<?php session_start(); ?>
<?php require_once 'DBconnect.php'; ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecuriTea(セキュリティー)</title>
    <link rel="stylesheet" href="css/soft.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php require 'headerTag.php' ?>
    <main class="main">
        <div class="container">
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
            <!--商品項目-->
            <section class="products">
                <h2 class="section-title">商品一覧</h2>
                <div class="products-grid">

                    <?php
                        $data=$db->query("select * FROM Products where category_id = 1");
                        foreach($data as $value){
                                echo "<div class='product-card'>",
                                        "<div class='product-image'>",
                                            "<img src='images/PackIcon/",$value["image_path"],"' alt='",$value["name"],"'>",
                                        "</div>",
                                        "<div class='product-content'>",
                                            "<h3 class='product-title'>",$value["name"],"</h3>",
                                            "<p class='product-description'>",$value["description"],"</p>",
                                            "<p>DB出力</p>",
                                            "<a href='pack.php?id=",$value["product_id"],"' class='product-btn'>",
                                                "<span>詳細を見る</span>",
                                                "<i class='fas fa-arrow-right'></i>",
                                            "</a>",
                                        "</div>",
                                     "</div>";
                        }

                    ?>
                    <!--カスタムだけ直接遷移-->
                    <div class="product-card">
                        <div class="product-image">
                            <img src="images/CostomIcon/costomIcon.png" alt="商品画像1">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">カスタム</h3>
                            <p class="product-description">必要な分だけ</p>
                            <div class="product-tags">
                                <span class="tag">#玄人向け</span>
                                <span class="tag">#必要最低限</span>
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

    
    <?php require 'footer.php'; ?>
    <?php include 'component/chatBot.php'; ?>
</body>

</html>