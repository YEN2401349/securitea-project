<?php session_start();?>
<?php require "DBconnect.php";?>

<?php
// --- ▼ ここから追加 (DBデータ取得処理) ---

// 1. GETパラメータから product_id を取得
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // IDが無効なら商品一覧に戻す
    header('Location: product.php');
    exit;
}
$product_id = (int)$_GET['id'];

// 2. データベースから商品情報を取得 (category_id = 1 を保証)
try {
    // プレースホルダ (?) を使ってSQLインジェクション対策
    // security_features もSELECTに追加
    $sql = "SELECT name, description, price, eye_catch, security_features  FROM Products WHERE product_id = ? AND category_id = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        // 商品が存在しないか、カテゴリが違う場合は一覧に戻す
        header('Location: product.php');
        exit;
    }

    // 3. 取得したデータを変数に格納
    $product_name = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
    
    // ▼▼▼ security_features と description の処理 ▼▼▼
    
    // security_features をコンマで分割して配列にする
    $features_array = [];
    if (!empty($product['security_features'])) {
        // データベースから取得した文字列をコンマ(,)で分割
        $features_array = explode(',', $product['security_features']);
        
        // 分割した各要素（機能名）の前後の空白を削除
        $features_array = array_map('trim', $features_array);
        
        // もし空の要素（例: "a,,b" のようにコンマが連続した場合）があれば削除
        $features_array = array_filter($features_array);
    }

    // description を安全なテキストとして変数に格納
    // nl2br() を使って、データベース内の改行を <br> タグに変換
    $description_text = nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'));
    
    // ▲▲▲ ここまで ▲▲▲

    // 4. 価格計算
    $monthly_price = (int)$product['price'];
    $yearly_price = $monthly_price * 10;
    $tri_price = $monthly_price * 25; // 3年プラン (25倍)

} catch (PDOException $e) {
    // DBエラー時
    echo "エラーが発生しました: " . $e->getMessage();
    // (実際にはエラーページにリダイレクトすることを推奨します)
    exit;
}
// --- ▲ ここまで ---
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product_name; ?> - SecuriTea</title>
    <link rel="stylesheet" href="css/pack.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php require "headerTag.php";?>

    <main class="main">
        <div class="container">
            <div class="details-container">
                <div class="product-details">
                    
                    <h1 class="details-title"><?php echo $product_name; ?></h1>
                    
                    <p class="details-subtitle"><?php echo htmlspecialchars($product['eye_catch'], ENT_QUOTES, 'UTF-8'); ?></p>

                    <form class="plan-form" action="add_pack.php" method="POST">
                        
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="product_name" value="<?php echo $product_name; ?>">
                        <input type="hidden" name="monthly_price" value="<?php echo $monthly_price; ?>">
                        
                        <fieldset class="plan-options">
                            <legend class="options-title">契約期間を選択してください</legend>
                            
                            <div class="option-item">
                                <input type="radio" id="monthly" name="plan_type" value="monthly" checked>
                                <label for="monthly">
                                    <span class="plan-name">月間プラン</span>
                                    <span class="plan-price">¥<?php echo number_format($monthly_price); ?> / 月</span>
                                </label>
                            </div>
                            
                            <div class="option-item">
                                <input type="radio" id="yearly" name="plan_type" value="yearly">
                                <label for="yearly">
                                    <span class="plan-name">年間プラン</span>
                                    <span class="plan-price">¥<?php echo number_format($yearly_price); ?> / 年</span>
                                    <span class="plan-badge">おすすめ</span>
                                </label>
                            </div>

                            <div class="option-item">
                                <input type="radio" id="triennially" name="plan_type" value="triennially">
                                <label for="triennially">
                                    <span class="plan-name">3年プラン</span>
                                    <span class="plan-price">¥<?php echo number_format($tri_price); ?> / 3年</span>
                                    <span class="plan-badge">11ヶ月分お得</span>
                                </label>
                            </div>
                        </fieldset>

                        <div class="form-buttons-container">
                            <a href="javascript:history.back()" class="back-btn">
                                <i class="fas fa-arrow-left"></i> 前のページに戻る
                            </a>
                            
                            <button type="submit" class="purchase-btn">カートに追加する</button>
                        </div>
                        
                    </form>
                </div>

                <aside class="product-sidebar">
                    <h2 class="sidebar-title">プランの主な機能</h2>
                    
                    <ul class="feature-list">
                        <?php if (!empty($features_array)): ?>
                            <?php foreach ($features_array as $feature): ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($feature, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><i class="fas fa-info-circle"></i> 機能の詳細は商品説明をご覧ください。</li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="sidebar-info">
                        <h3><i class="fas fa-file-alt"></i> 商品説明</h3>
                        <p><?php echo $description_text; ?></p>
                    </div>
                </aside>
            </div>
        </div>
    </main>
    <?php require "footer.php";?>
    <?php include './component/chatBot.php'; ?>
</body>

</html>