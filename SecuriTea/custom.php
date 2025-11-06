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
            <a href="account_check.php" class="product-btn">
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
                        $data=$db->query("select product_id, name, description, image_path, price FROM Products where category_id = 2");
                        foreach($data as $value){
                                $monthly_price = (int)$value["price"];
                                $yearly_price = $monthly_price * 10; 
                                $name_escaped = htmlspecialchars($value["name"], ENT_QUOTES);
                                $desc_escaped = htmlspecialchars($value["description"], ENT_QUOTES);
                                echo "<div class='mini-product-card'>",
                                        "<input type='checkbox' class='option-check'",
                                               " data-price-monthly='", $monthly_price, "'",
                                               " data-price-yearly='", $yearly_price, "'",
                                               " data-label='", $name_escaped, "'>",
                                        "<div class='tooltip-container'>",
                                            "<i class='fas fa-info-circle info-icon'></i>",
                                            "<span class='tooltip-text'>",$desc_escaped,"</span>",
                                        "</div>",
                                        "<img src='../adminSystem/",$img_path_escaped,"' alt='",$name_escaped,"'>",
                                        "<p class='mini-product-title'>",$name_escaped,"</p>",
                                        "<p class='card-price-display price-monthly'>月/",$monthly_price,"円</p>",
                                        "<p class='card-price-display price-yearly' style='display:none;'>年/",$yearly_price,"円</p>",
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

        const billingCycleRadios = document.querySelectorAll('input[name="billing-cycle"]');
        const billingCycleLabel = document.getElementById("billing-cycle-label");
        
        // ★ 修正点 3: カード内の価格表示Pタグを取得
        const priceDisplaysMonthly = document.querySelectorAll(".card-price-display.price-monthly");
        const priceDisplaysYearly = document.querySelectorAll(".card-price-display.price-yearly");
        
        const SIDEBAR_WIDTH = 300; 
        const SIDEBAR_MARGIN = 20;
        const SIDEBAR_TOP_OFFSET = 85; 

        // --- レイアウト（マージンとサイドバー高）を調整する関数 ---
        function adjustLayout() { 
            if (window.innerWidth > 992) {
                const marginValue = (SIDEBAR_WIDTH + SIDEBAR_MARGIN) + "px";
                if (mainContent) {
                    mainContent.style.marginRight = marginValue;
                }
            } else {
                 if (mainContent) {
                    mainContent.style.marginRight = "0"; 
                }
            }
            
            const footerHeight = pageFooter ? pageFooter.offsetHeight : 0;
            if (selectedOptions) {
                selectedOptions.style.height = `calc(100vh - ${SIDEBAR_TOP_OFFSET}px - ${footerHeight}px)`;
            }
        }

        // --- 選択状態（金額・項目数）を更新する関数 ---
        function updateSelection() {
            let totalCount = 0;
            let totalPrice = 0;
            
            // 1. 選択されている請求サイクル（月間/年間）を取得
            const selectedCycle = document.querySelector('input[name="billing-cycle"]:checked').value;
            // 2. 読み取るべき data-* 属性を決定
            const priceAttribute = (selectedCycle === "yearly") ? "data-price-yearly" : "data-price-monthly";
            const label = (selectedCycle === "yearly") ? "/年" : "/月";

            // ★ 修正点 4: 請求サイクルに合わせてカード内の価格表示を切り替える
            if (selectedCycle === "yearly") {
                priceDisplaysMonthly.forEach(el => el.style.display = "none");
                priceDisplaysYearly.forEach(el => el.style.display = "block");
            } else { // 'monthly'
                priceDisplaysMonthly.forEach(el => el.style.display = "block");
                priceDisplaysYearly.forEach(el => el.style.display = "none");
            }

            // 3. チェックされている全てのチェックボックスをループ
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    totalCount++;
                    // 4. data属性から該当サイクルの価格を取得して加算
                    const price = parseFloat(checkbox.getAttribute(priceAttribute)) || 0;
                    totalPrice += price;
                }
            });

            // 5. 画面表示を更新
            if (totalCountEl) {
                totalCountEl.textContent = totalCount;
            }
            if (totalPriceEl) {
                totalPriceEl.textContent = totalPrice; 
            }
            if (billingCycleLabel) {
                billingCycleLabel.textContent = label; 
            }
        }

        // --- チェックボックスのイベント設定 ---
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", function() {
                // (変更なし)
                const label = this.getAttribute("data-label");
                if (!label) return; 

                if (this.checked) {
                    const li = document.createElement("li");
                    li.textContent = label;
                    li.setAttribute("data-option", label); 
                    selectedList.appendChild(li);
                } else {
                    const li = selectedList.querySelector(`li[data-option='${label}']`);
                    if (li) li.remove();
                }
                
                updateSelection();
            });
        });
        
        // ★ ラジオボタン変更時にも金額計算を呼ぶ
        billingCycleRadios.forEach(radio => {
            radio.addEventListener("change", updateSelection);
        });

        // --- 初期ロード時とウィンドウリサイズ時にもレイアウトを調整
        adjustLayout(); 
        window.addEventListener("resize", adjustLayout); 
        
        // --- 初期ロード時にも金額・項目数を計算 (カード価格の表示切替も実行される)
        updateSelection();
    });
    </script>
</body>
</html>