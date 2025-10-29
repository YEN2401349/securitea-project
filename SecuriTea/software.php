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
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="test.php" class="logo">
                    <img src="ロゴ2透過.png" alt="Modern Securitea Logo">
                </a>
            </div>
            <nav class="nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="login.html" class="nav-link">
                            <i class="fas fa-user"></i>
                            ログイン
                        </a>
                    </li>
                     <li class="nav-item">
                        <a href="test.php" class="nav-link">
                            <i class="fas fa-user"></i>
                            ホーム
                        </a>    
                    </li>
                    <li class="nav-item">
                        <a href="account.html" class="nav-link">
                            <i class="fas fa-question-circle"></i>
                            アカウント情報
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="inquiry.html" class="nav-link">
                            <i class="fas fa-question-circle"></i>
                            Q&A
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
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
                <h2 class="section-title">商品一覧</h2>
                <div class="products-grid">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="20200501_noimage.jpg" alt="商品画像1">
                            <div class="product-badge">人気</div>
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">サブスク</h3>
                            <p class="product-description">セキュリティソフトを試してみたいあなたへ</p>
                            <div class="product-tags">
                                <span class="tag">#サブスク</span>
                                <span class="tag">#保護者機能</span>
                            </div>
                            <a href="pack.php" class="product-btn">
                                <span>詳細を見る</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-image">
                            <img src="20200501_noimage.jpg" alt="商品画像2">
                            <div class="product-badge new">おすすめ</div>
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">スタンダード</h3>
                            <p class="product-description">パソコンを使い始めたばかりのあなたへ</p>
                            <div class="product-tags">
                                <span class="tag">#スタンダード</span>
                                <span class="tag">#パスワード管理</span>
                            </div>
                            <a href="pack.php" class="product-btn">
                                <span>詳細を見る</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-image">
                            <img src="20200501_noimage.jpg" alt="商品画像3">
                        </div>
                        <div class="product-content">
                            <h3 class="product-title">エキスパート</h3>
                            <p class="product-description">普段のセキュリティソフトにプラスアルファしたいあなたへ</p>
                            <div class="product-tags">
                                <span class="tag">#エキスパート</span>
                                <span class="tag">#クラウドバックアップ</span>
                            </div>
                            <a href="pack.php" class="product-btn">
                                <span>詳細を見る</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="product-card">
                        <div class="product-image">
                            <img src="20200501_noimage.jpg" alt="商品画像1">
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

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-links">
                    <a href="#" class="footer-link">プライバシーポリシー</a>
                    <a href="#" class="footer-link">利用規約</a>
                    <a href="#" class="footer-link">お問い合わせ</a>
                </div>
                <div class="footer-copyright">
                    <p>&copy; 2025 SecuriTea. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    <?php include './component/chatBot.php'; ?>
</body>

</html>