<?php session_start(); ?>
<?php require_once 'DBconnect.php'; ?>
<?php
// ▼▼▼ ここから追加 ▼▼▼
// URLから検索キーワードを取得
$search_term = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    // XSS対策のためエスケープ
    $search_term = htmlspecialchars(trim($_GET['search']), ENT_QUOTES, 'UTF-8');
}
// ▲▲▲ ここまで追加 ▲▲▲
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecuriTea(セキュリティー)</title>
    <link rel="stylesheet" href="css/pack.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php require 'headerTag.php' ?>
    <main class="main">
        <div class="container">
                <div class="search-section">
                    <div class="search-container">
                        
                        <div class="search-bar">
                            <i class="fas fa-search search-icon"></i>
                            
                            <input type="text" placeholder="特徴でセキュリティソフトを探す" class="search-input" id="live-search-input" autocomplete="off" value="<?php echo $search_term; ?>">
                            
                            <button class="search-btn" id="live-search-button">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        
                        <div class="suggestions-container" id="suggestions-container"></div>
                        </div>
                </div>
            <section class="products">
                <h2 class="section-title">商品一覧</h2>
                <div class="products-grid">

                    <?php
                        // ▼▼▼ 以下のSQLクエリを変更 ▼▼▼
                        
                        // (変更前)
                        // $data=$db->query("select * FROM Products where category_id = 1");

                        // (変更後)
                        $sql = "SELECT * FROM Products WHERE category_id = 1";
                        $params = []; // SQLに渡すパラメータ配列

                        // 検索キーワードがある場合
                        if ($search_term !== '') {
                            $like_term = '%' . $search_term . '%';
                            // name または security_features で絞り込み
                            $sql .= " AND (name LIKE ? OR security_features LIKE ?)"; 
                            $params[] = $like_term;
                            $params[] = $like_term;
                        }
                        
                        // SQL実行 (安全なプリペアドステートメントを使用)
                        $stmt = $db->prepare($sql);
                        $stmt->execute($params);
                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // ▲▲▲ ここまで変更 ▲▲▲

                        foreach($data as $value){
                                echo "<div class='product-card'>",
                                        "<div class='product-image'>",
                                            "<img src='../adminSystem/",$value["image_path"],"' alt='",$value["name"],"'>",
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
                    <div class="product-card">
                        <div class="product-image">
                            <img src="images/CostomIcon/costomIcon.png" alt="カスタムアイコン">
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

   <script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("live-search-input");
        const suggestionsContainer = document.getElementById("suggestions-container");

        // 1. 入力イベント (キーをタイプするたび)
        searchInput.addEventListener("input", function() {
            const term = this.value.trim();

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

        // ▼▼▼ 検索ボタンの処理 (product.php専用) ▼▼▼
        // 検索ボタンは、product.php 自身をリロードして絞り込む
        document.getElementById("live-search-button").addEventListener("click", function() {
            const term = searchInput.value.trim();
            
            if (term.length > 0) {
                // 検索語をURLパラメータに付けてリロード
                window.location.href = `product.php?search=${encodeURIComponent(term)}`;
            } else {
                // 検索語が空なら、絞り込みを解除して全件表示
                window.location.href = 'product.php';
            }
        });

        // Enterキーでも検索ボタンと同じ動作
        searchInput.addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                event.preventDefault(); // フォーム送信をキャンセル
                document.getElementById("live-search-button").click();
            }
        });
    });
    </script>
    </body>

</html>