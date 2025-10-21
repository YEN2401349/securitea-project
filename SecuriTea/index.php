<?php session_start();
include './component/header.php'; ?>
<?php include './component/background.php'; ?>
<link rel="stylesheet" href="./css/index.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<main class="main">
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        <span class="hero-title-main">セキュリティの未来を</span>
                        <span class="hero-title-sub">あなたの手に</span>
                    </h1>
                    <p class="hero-description">
                        最先端のセキュリティソフトが、あなたのパソコンを守ります
                    </p>
                    <div class="hero-stats">
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
                    </div>
                    <div class="hero-actions">
                        <button class="btn-primary">
                            <i class="fas fa-download"></i>
                            ダウンロード
                        </button>
                        <button class="btn-secondary">
                            <i class="fas fa-play"></i>
                            デモを見る
                        </button>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="security-shield">
                        <div class="shield-icon">
                            <img src="./img/ロゴ盾だけ2.png" class="shield-image" alt="Shield Icon">
                        </div>
                        <div class="protection-ring"></div>
                        <div class="threat-indicators">
                            <div class="threat threat-1">
                                <i class="fas fa-virus"></i>
                            </div>
                            <div class="threat threat-2">
                                <i class="fas fa-bug"></i>
                            </div>
                            <div class="threat threat-3">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <section class="products">
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
    </div>
</main>

<?php include './component/footer.php'; ?>