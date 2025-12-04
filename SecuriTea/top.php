<?php session_start(); ?>
<?php require "../common/DBconnect.php"; ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecuriTea(セキュリティー)</title>
    <link rel="stylesheet" href="css/top-style.css">
    <link rel="stylesheet" href="css/heder-footer.css">
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
                    <span class="hero-title-main">あなたにあった</span>
                    <span class="hero-title-sub">セキュリティソフトを</span>
                </h1>
                <p class="hero-description">
                    最先端のセキュリティソフトが、あなたのパソコンを守ります
                </p>
                <div class="search-section">
                    <div class="search-container"> 
                        <div class="search-bar">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" placeholder="特徴でセキュリティソフトを探す" class="search-input" id="live-search-input" autocomplete="off">
                        </div>
                        <div class="suggestions-container" id="suggestions-container">
                        </div>
                    </div>
                </div>
            </section>
            <section class="products">
                <h2 class="section-title">商品一例</h2>
                <div class="products-grid">
                    <?php
                        $count = 0;
                        $stmt = $db->prepare("SELECT product_id FROM Products WHERE category_id = 1 AND product_id != 0");
                        $stmt->execute();
                        $product_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

                        // Shuffle the IDs and take the first 3
                        shuffle($product_ids);
                        $random_ids = array_slice($product_ids, 0, 3);

                        // Fetch the products for the selected IDs
                        $data = [];
                        if (!empty($random_ids)) {
                            $placeholders = implode(',', array_fill(0, count($random_ids), '?'));
                            $stmt = $db->prepare("SELECT * FROM Products WHERE product_id IN ($placeholders)");
                            $stmt->execute($random_ids);
                            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        }

                        foreach($data as $value){
                                echo "<div class='product-card'>",
                                        "<div class='product-image'>",
                                            "<img src='../adminSystem/",$value["image_path"],"' alt='",$value["name"],"'>",
                                        "</div>",
                                        "<div class='product-content'>",
                                            "<h3 class='product-title'>",$value["name"],"</h3>",
                                            "<p class='product-description'>",$value["eye_catch"],"</p>",
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
    <script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById("live-search-input");
    const suggestionsContainer = document.getElementById("suggestions-container");

    // 1. 入力イベント (キーをタイプするたび)
    searchInput.addEventListener("input", function() {
        const term = this.value.trim();

        // 1文字未満ならサジェストを消す
        if (term.length < 1) {
            hideSuggestions();
            return;
        }

        // 2. live_search.php にキーワードを送信
        fetch(`live_search.php?term=${encodeURIComponent(term)}`)
            .then(response => response.json())
            .then(data => {
                // 3. 結果の表示
                if (data.status === 'success' && data.data.length > 0) {
                    showSuggestions(data.data, term);
                } else {
                    hideSuggestions();
                }
            })
            .catch(error => {
                console.error("検索エラー:", error);
                hideSuggestions();
            });
    });

    // 4. サジェストを表示する関数
    function showSuggestions(items, term) {
        suggestionsContainer.innerHTML = ""; // 中身をクリア
        const ul = document.createElement("ul");
        ul.className = "suggestions-list";

        items.forEach(item => {
            const li = document.createElement("li");
            const a = document.createElement("a");
            a.href = item.url;
            
            // 入力したキーワードを太字にする (簡易版)
            try {
                const regex = new RegExp(`(${escapeRegExp(term)})`, 'gi');
                a.innerHTML = item.name.replace(regex, '<strong>$1</strong>');
            } catch (e) {
                a.textContent = item.name; // 正規表現エラー時はそのまま表示
            }
            
            li.appendChild(a);
            ul.appendChild(li);
        });

        suggestionsContainer.appendChild(ul);
        suggestionsContainer.style.display = "block";
    }

    // 5. サジェストを非表示にする関数
    function hideSuggestions() {
        suggestionsContainer.innerHTML = "";
        suggestionsContainer.style.display = "none";
    }
    
    // 6. 検索欄の外側をクリックしたらサジェストを消す
    document.addEventListener("click", function(event) {
        if (!event.target.closest('.search-container')) {
            hideSuggestions();
        }
    });
    
    // 正規表現のエスケープ用
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
});
</script>
</body>

</html>