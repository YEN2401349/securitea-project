<?php session_start(); include "./component/header.php"; ?>
<link rel="stylesheet" href="./css/productList.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<main class="main">

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
    <section class="products">
        <div class="container">
            <h2 class="section-title">商品一覧</h2>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-image">
                        <img src="./img/20200501_noimage.jpg" alt="商品画像1">
                        <div class="product-badge">人気</div>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">サブスク</h3>
                        <p class="product-description">セキュリティソフトを試してみたいあなたへ</p>
                        <div class="product-tags">
                            <span class="tag">#サブスク</span>
                            <span class="tag">#保護者機能</span>
                        </div>
                        <a href="#" class="product-btn">
                            <span>詳細を見る</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="./img/20200501_noimage.jpg" alt="商品画像2">
                        <div class="product-badge new">おすすめ</div>
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">スタンダード</h3>
                        <p class="product-description">パソコンを使い始めたばかりのあなたへ</p>
                        <div class="product-tags">
                            <span class="tag">#スタンダード</span>
                            <span class="tag">#パスワード管理</span>
                        </div>
                        <a href="#" class="product-btn">
                            <span>詳細を見る</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="product-card">
                    <div class="product-image">
                        <img src="./img/20200501_noimage.jpg" alt="商品画像3">
                    </div>
                    <div class="product-content">
                        <h3 class="product-title">エキスパート</h3>
                        <p class="product-description">普段のセキュリティソフトにプラスアルファしたいあなたへ</p>
                        <div class="product-tags">
                            <span class="tag">#エキスパート</span>
                            <span class="tag">#クラウドバックアップ</span>
                        </div>
                        <a href="#" class="product-btn">
                            <span>詳細を見る</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
    </section>

    <section class="mini-products">
        <div class="mini-products-grid">
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品A">
                <p class="mini-product-title">オプション1</p>
            </div>
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品B">
                <p class="mini-product-title">オプション1</p>
            </div>
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品C">
                <p class="mini-product-title">オプション3</p>
            </div>
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品D">
                <p class="mini-product-title">オプション4</p>
            </div>
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品E">
                <p class="mini-product-title">オプション5</p>
            </div>
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品A">
                <p class="mini-product-title">オプション6</p>
            </div>
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品B">
                <p class="mini-product-title">オプション7</p>
            </div>
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品C">
                <p class="mini-product-title">オプション8</p>
            </div>
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品D">
                <p class="mini-product-title">オプション9</p>
            </div>
            <div class="mini-product-card">
                <img src="./img/20200501_noimage.jpg" alt="商品E">
                <p class="mini-product-title">オプション10</p>
            </div>
        </div>
    </section>

    </div>
</main>
<?php include "./component/footer.php"; ?>