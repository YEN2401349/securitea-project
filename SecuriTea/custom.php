<?PHP session_start();?>
<?PHP require_once 'DBconnect.php'; ?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecuriTea(セキュリティー) - カスタムプラン</title> 
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?PHP require 'headerTag.php'; ?>
<div id="selected-options">
        <h2>選択中のオプション</h2>
        
        <div class="option-summary">
            <div class="billing-cycle">
                <input type="radio" name="billing-cycle" value="monthly" id="cycle-monthly" checked>
                <label for="cycle-monthly">月間</label>
                <input type="radio" name="billing-cycle" value="yearly" id="cycle-yearly">
                <label for="cycle-yearly">年間</label>
            </div>
            <p><strong>金額：</strong><span id="total-price">0</span>円<span id="billing-cycle-label">/月</span></p>
            <p><strong>項目数：</strong><span id="total-count">0</span>項目</p>
            <p><strong>期間：</strong> 年 月 日 ～ 年 月 日</p>
            <a href="cart.html" class="product-btn">
                <span>確認画面へ</span>
            <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <ul id="selected-list"></ul>
    </div>


    <main class="main" id="main-content">
        <div class="container">
            <section class="mini-products">
                <div class="mini-products-grid">
                    <?php
                        $data=$db->query("select * FROM Products where category_id = 2");
                        foreach($data as $value){
                                echo "<div class='mini-product-card'>",
                                        "<input type='checkbox' class='option-check'>",
                                        "<div class='tooltip-container'>",
                                            "<i class='fas fa-info-circle info-icon'></i>",
                                            "<span class='tooltip-text'>",$value["description"],"</span>",
                                        "</div>",
                                        "<img src='../adminSystem/",$value["image_path"],"' alt='",$value["name"],"'>",
                                            "<p class='mini-product-title'>",$value["name"],"</p>",
                                     "</div>";
                        }
                    ?>
                    
                </div>
            </section>
        </div>
    </main>
    <?php require 'footer.php'; ?>
    <?php include 'component/chatBot.php'; ?>

   <script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- 要素を取得 ---
        const checkboxes = document.querySelectorAll(".option-check");
        const selectedList = document.getElementById("selected-list");
        const totalPriceEl = document.getElementById("total-price");
        const totalCountEl = document.getElementById("total-count");
        const selectedOptions = document.getElementById("selected-options");
        const mainContent = document.getElementById("main-content");
        const pageFooter = document.querySelector(".footer"); 

        // ★ ラジオボタン関連の要素を取得
        const billingCycleRadios = document.querySelectorAll('input[name="billing-cycle"]');
        const billingCycleLabel = document.getElementById("billing-cycle-label");

        // --- 設定値 ---
        // ★ 項目ごとの金額を仮設定
        const OPTION_PRICE_MONTHLY = 250; // 1項目あたりの月額
        const OPTION_PRICE_YEARLY = 2500; // 1項目あたりの年額 (月額の10ヶ月分など)
        
        const SIDEBAR_WIDTH = 300; 
        const SIDEBAR_MARGIN = 20;
        const SIDEBAR_TOP_OFFSET = 85; 

        // --- レイアウト（マージンとサイドバー高）を調整する関数 ---
        function adjustLayout() { 
            // 992px より広い画面でのみサイドバー領域（マージン）を確保
            if (window.innerWidth > 992) {
                const marginValue = (SIDEBAR_WIDTH + SIDEBAR_MARGIN) + "px";
                if (mainContent) {
                    mainContent.style.marginRight = marginValue;
                }
            } else {
                 if (mainContent) {
                    mainContent.style.marginRight = "0"; // 狭い画面ではマージンをリセット
                }
            }
            
            const footerHeight = pageFooter ? pageFooter.offsetHeight : 0;
            if (selectedOptions) {
                selectedOptions.style.height = `calc(100vh - ${SIDEBAR_TOP_OFFSET}px - ${footerHeight}px)`;
            }
        }

        // --- ★ 選択状態（金額・項目数）を更新する関数（ロジック変更） ---
        function updateSelection() {
            // 1. チェックされた項目数をカウント
            const count = selectedList.children.length;
            
            // 2. 選択されている請求サイクル（月間/年間）を取得
            const selectedCycle = document.querySelector('input[name="billing-cycle"]:checked').value;

            let price = 0;
            let label = "";

            // 3. サイクルに応じて金額とラベルを決定
            if (selectedCycle === "yearly") {
                price = count * OPTION_PRICE_YEARLY;
                label = "/年";
            } else { // "monthly"
                price = count * OPTION_PRICE_MONTHLY;
                label = "/月";
            }

            // 4. 画面表示を更新
            if (totalCountEl) {
                totalCountEl.textContent = count;
            }
            if (totalPriceEl) {
                totalPriceEl.textContent = price;
            }
            if (billingCycleLabel) {
                billingCycleLabel.textContent = label;
            }
        }

        // --- チェックボックスにイベントを設定 ---
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", function() {
                const titleEl = this.parentElement.querySelector(".mini-product-title");
                if (!titleEl) return; 

                const label = titleEl.textContent.trim();

                if (this.checked) {
                    const li = document.createElement("li");
                    li.textContent = label;
                    li.setAttribute("data-option", label);
                    selectedList.appendChild(li);
                } else {
                    const li = selectedList.querySelector(`li[data-option='${label}']`);
                    if (li) li.remove();
                }
                
                // ★ チェックボックス変更時も金額計算を呼ぶ
                updateSelection();
            });
        });
        
        // ★ ラジオボタン変更時にも金額計算を呼ぶ
        billingCycleRadios.forEach(radio => {
            radio.addEventListener("change", updateSelection);
        });

        // --- 初期ロード時とウィンドウリサイズ時にもレイアウトを調整 ---
        adjustLayout(); 
        window.addEventListener("resize", adjustLayout); 
        
        // --- 初期ロード時にも金額・項目数を計算 ---
        updateSelection();
    });
    </script>
</body>
</html>