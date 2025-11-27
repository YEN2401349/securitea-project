<?PHP session_start();?>
<?PHP require_once '../common/DBconnect.php'; ?>
<?php
// セッションから既存の選択情報を読み込む
$existing_labels = [];
$existing_cycle = 'monthly'; // デフォルト

if (isset($_SESSION['custom_options']) && is_array($_SESSION['custom_options'])) {
    // [['label' => 'A'], ['label' => 'B']] を ['A', 'B'] の形に変換
    $existing_labels = array_column($_SESSION['custom_options'], 'label');
}
if (isset($_SESSION['custom_billing_cycle'])) {
    $existing_cycle = $_SESSION['custom_billing_cycle'];
}

// 請求サイクルに応じて checked 属性を用意
$monthly_checked = ($existing_cycle === 'monthly') ? 'checked' : '';
$yearly_checked = ($existing_cycle === 'yearly') ? 'checked' : '';
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecuriTea(セキュリティー) - カスタムプラン</title> 
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="css/heder-footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?PHP require 'headerTag.php'; ?>
<div id="selected-options">
        <h2>選択中のオプション</h2>
        
        <div class="option-summary">
            <div class="billing-cycle">
                <input type="radio" name="billing-cycle" value="monthly" id="cycle-monthly" <?php echo $monthly_checked; ?>>
                <label for="cycle-monthly">月間</label>
                <input type="radio" name="billing-cycle" value="yearly" id="cycle-yearly" <?php echo $yearly_checked; ?>>
                <label for="cycle-yearly">年間</label>
            </div>
            <p><strong>金額：</strong><span id="total-price">0</span>円<span id="billing-cycle-label">/月</span></p>
            <p><strong>項目数：</strong><span id="total-count">0</span>項目</p>
            
            <p id="term-display"><strong>期間：</strong> 年 月 日 ～ 年 月 日</p>
            
            <div class="summary-actions">
                <a href="product.php" class="product-btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>戻る</span>
                </a>
                
                <a href="account_check.php" class="product-btn btn-primary" id="confirm-button">
                    <span>確認画面へ</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            </div>

        <ul id="selected-list"></ul>
    </div>


    <main class="main" id="main-content">
        <div class="container">
            <section class="mini-products">
                <div class="mini-products-grid">
                    <?php
                        $data=$db->query("select product_id, name, description, image_path, price FROM Products where category_id = 2 AND product_id != 0");
                        foreach($data as $value){
                                $monthly_price = (int)$value["price"];
                                $yearly_price = $monthly_price * 10; 
                                $name_escaped = htmlspecialchars($value["name"], ENT_QUOTES);
                                $desc_escaped = htmlspecialchars($value["description"], ENT_QUOTES);
                                $checked_attr = in_array($name_escaped, $existing_labels) ? " checked" : "";


                                echo "<div class='mini-product-card'>",
                                        "<input type='checkbox' class='option-check'",
                                               $checked_attr, // ★ 変更 (3/4): checked属性を追加
                                               " data-price-monthly='", $monthly_price, "'",
                                               " data-price-yearly='", $yearly_price, "'",
                                               " data-label='", $name_escaped,"'",
                                               " data-product-id='", $value["product_id"], "'",
                                               ">",
                                        "<div class='tooltip-container'>",
                                            "<i class='fas fa-info-circle info-icon'></i>",
                                            "<span class='tooltip-text'>",$desc_escaped,"</span>",
                                        "</div>",
                                        "<img src='../adminSystem/",$value["image_path"],"' alt='",$name_escaped,"'>", // (画像パスのタイプミス修正済み)
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
        // (変更なし)
        const checkboxes = document.querySelectorAll(".option-check");
        const selectedList = document.getElementById("selected-list");
        const totalPriceEl = document.getElementById("total-price");
        const totalCountEl = document.getElementById("total-count");
        const selectedOptions = document.getElementById("selected-options");
        const mainContent = document.getElementById("main-content");
        const pageFooter = document.querySelector(".footer"); 
        const billingCycleRadios = document.querySelectorAll('input[name="billing-cycle"]');
        const billingCycleLabel = document.getElementById("billing-cycle-label");
        const priceDisplaysMonthly = document.querySelectorAll(".card-price-display.price-monthly");
        const priceDisplaysYearly = document.querySelectorAll(".card-price-display.price-yearly");
        const termDisplayEl = document.getElementById("term-display");
        
        // ★ 修正点 2: 確認ボタンを取得
        const confirmButton = document.getElementById("confirm-button");
        
        const SIDEBAR_WIDTH = 300; 
        const SIDEBAR_MARGIN = 20;
        const SIDEBAR_TOP_OFFSET = 85; 

        // --- 日付フォーマット関数 --- (変更なし)
        function formatDateJP(date) {
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const day = date.getDate().toString().padStart(2, '0');
            const weekDay = ['日', '月', '火', '水', '木', '金', '土'][date.getDay()];
            return `${year}年${month}月${day}日(${weekDay})`;
        }

        // --- レイアウト調整関数 --- (変更なし)
        function adjustLayout() { 
            if (window.innerWidth > 992) {
                const marginValue = (SIDEBAR_WIDTH + SIDEBAR_MARGIN) + "px";
                if (mainContent) mainContent.style.marginRight = marginValue;
            } else {
                 if (mainContent) mainContent.style.marginRight = "0"; 
            }
            const footerHeight = pageFooter ? pageFooter.offsetHeight : 0;
            if (selectedOptions) {
                selectedOptions.style.height = `calc(100vh - ${SIDEBAR_TOP_OFFSET}px - ${footerHeight}px)`;
            }
        }

        // ★ 修正点 3: updateSelection 関数を、計算結果を返すように変更
        function updateSelection() {
            let totalCount = 0;
            let totalPrice = 0;
            
            // ★ 送信用の選択済みオプション配列
            let selectedOptionsData = []; 

            const selectedCycle = document.querySelector('input[name="billing-cycle"]:checked').value;
            const priceAttribute = (selectedCycle === "yearly") ? "data-price-yearly" : "data-price-monthly";
            const label = (selectedCycle === "yearly") ? "/年" : "/月";

            // 2. カード価格表示の切り替え (変更なし)
            if (selectedCycle === "yearly") {
                priceDisplaysMonthly.forEach(el => el.style.display = "none");
                priceDisplaysYearly.forEach(el => el.style.display = "block");
            } else { 
                priceDisplaysMonthly.forEach(el => el.style.display = "block");
                priceDisplaysYearly.forEach(el => el.style.display = "none");
            }

            // 3. 合計金額の計算
            checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
            totalCount++;
            
            // 個別の価格を取得
            const individualPrice = parseFloat(checkbox.getAttribute(priceAttribute)) || 0;
            totalPrice += individualPrice; 
            
            // ★ 送信用データ配列に追加 (account_check.php が期待する形式)
            selectedOptionsData.push({
                id: checkbox.getAttribute("data-product-id"), // ★ 取得する
                label: checkbox.getAttribute("data-label"),
                price: individualPrice // ★ 取得する
            });
        }
    });

            // 4. 合計・項目数の表示更新 (変更なし)
            if (totalCountEl) totalCountEl.textContent = totalCount;
            if (totalPriceEl) totalPriceEl.textContent = totalPrice; 
            if (billingCycleLabel) billingCycleLabel.textContent = label; 
            
            // 5. 期間の計算と表示更新
            const today = new Date();
            const endDate = new Date(today); 
            if (selectedCycle === "yearly") {
                endDate.setFullYear(today.getFullYear() + 1); 
            } else { 
                endDate.setMonth(today.getMonth() + 1);
            }

            const termStartStr = formatDateJP(today);
            const termEndStr = formatDateJP(endDate);

            if (termDisplayEl) {
                termDisplayEl.innerHTML = `<strong>期間：</strong> ${termStartStr} ～ ${termEndStr}`;
            }

            // ★ 修正点 4: 計算結果をオブジェクトとして返す
            return {
                options: selectedOptionsData,
                totalPrice: totalPrice,
                billingCycle: selectedCycle,
                termStart: termStartStr,
                termEnd: termEndStr,
                totalCount: totalCount
            };
        }

        // --- チェックボックスのイベント設定 --- (★ 変更あり)
        checkboxes.forEach(checkbox => {

            // ★ --- ここから追加 (4/4) ---
            // ページ読み込み時（初期化時）に、
            // 既にPHPによって 'checked' 属性がついていた場合に
            // 右側の「選択中のオプション」リスト (ul) にも追加する
            if (checkbox.checked) {
                const label = checkbox.getAttribute("data-label");
                if (label) {
                    const li = document.createElement("li");
                    li.textContent = label;
                    li.setAttribute("data-option", label); 
                    selectedList.appendChild(li);
                }
            }
            // ★ --- ここまで追加 (4/4) ---

            // (↓ 変更なし: クリック時のイベントリスナーはそのまま)
            checkbox.addEventListener("change", function() {
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
                updateSelection(); // ★ updateSelectionの戻り値はここでは使わない
            });
        });
        
        // --- ラジオボタンのイベント設定 --- (変更なし)
        billingCycleRadios.forEach(radio => {
            radio.addEventListener("change", updateSelection); // ★ 同上
        });

        // --- 初期ロード・リサイズ時のレイアウト調整 --- (変更なし)
        adjustLayout(); 
        window.addEventListener("resize", adjustLayout); 
        
        // --- 初期ロード時の計算実行 --- (変更なし)
        // (★ 上の (4/4) の処理が実行された後でこれが呼ばれるため、
        //   合計金額や期間も正しく初期表示されます)
        updateSelection();

        // ★ 修正点 5: 確認ボタンクリック時の処理を丸ごと追加
        confirmButton.addEventListener("click", function(event) {
            // 1. デフォルトのリンク遷移を止める
            event.preventDefault(); 

            // 2. 現在の選択内容を取得
            // (updateSelectionは画面更新も行うが、最新データを取得するためにここで呼ぶ)
            const selectionData = updateSelection();

            // 3. 選択項目が0件の場合はアラート（ここでアラートを出すのが適切）
            if (selectionData.totalCount === 0) {
                alert("オプションが選択されていません。");
                return; // 処理を中断
            }

            // 4. データをセッションに保存するためにサーバーに送信 (Fetch API を使用)
            fetch('save_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                // bodyには updateSelection が返したオブジェクトをJSON文字列にして渡す
                body: JSON.stringify(selectionData)
            })
            .then(response => {
                if (!response.ok) {
                    // サーバー側でエラーが起きた場合 (save_session.php が 400 を返した場合など)
                    throw new Error('セッションの保存に失敗しました。');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // 5. セッション保存成功後、account_check.php へ遷移
                    window.location.href = 'account_check.php';
                } else {
                    alert('エラーが発生しました。もう一度お試しください。');
                }
            })
            .catch(error => {
                // 通信自体が失敗した場合
                console.error('Error:', error);
                alert('通信エラーが発生しました。' + error.message);
            });
        });

    });
    </script>
</body>
</html>