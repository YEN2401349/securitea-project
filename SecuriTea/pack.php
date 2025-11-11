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
    $sql = "SELECT name, description, price FROM Products WHERE product_id = ? AND category_id = 1";
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

    // --- ▼ 説明文のパース処理 (ここから変更) ---
    $raw_description = $product['description'];
    
    // 変数の初期化
    $subtitle = '';
    $features_array = [];
    $description_text = '';
    $recommend_text = '';

    // 正規表現でパース
    // 例: お試し...[項目1,項目2]"商品説明"{おすすめ文}
    // { は全角 ｛ の可能性も考慮
    $pattern = '/^([^\[]+)\[([^\]]+)\]"([^"]+)"[｛\{]([^\}｝]+)[\}｝]$/u';
    
    if (preg_match($pattern, $raw_description, $matches)) {
        // パース成功時
        
        // 1. サブタイトル
        $subtitle = htmlspecialchars(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        
        // 2. 機能リスト (カンマ区切りを配列に)
        $features_string = $matches[2];
        $raw_features = explode(',', $features_string); // カンマで分割
        foreach ($raw_features as $feature) {
            $trimmed_feature = trim($feature);
            if (!empty($trimmed_feature)) { // 空の項目を無視
                $features_array[] = htmlspecialchars($trimmed_feature, ENT_QUOTES, 'UTF-8');
            }
        }
        
        // 3. 商品説明
        $description_text = nl2br(htmlspecialchars(trim($matches[3]), ENT_QUOTES, 'UTF-8'));
        
        // 4. おすすめ
        $recommend_text = nl2br(htmlspecialchars(trim($matches[4]), ENT_QUOTES, 'UTF-8'));
        
    } else {
        // パース失敗した場合 (想定外のフォーマット)
        // 元の説明文全体を「商品説明」に入れておく
        $description_text = nl2br(htmlspecialchars($raw_description, ENT_QUOTES, 'UTF-8'));
        // (他は空のまま)
    }
    // --- ▲ 説明文のパース処理 終わり ---
    
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
// --- ▲ ここまで追加 ---
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
                    
                    <p class="details-subtitle"><?php echo $subtitle; ?></p>

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
                                </label>
                            </div>
                        </fieldset>
                        <button type="submit" class="purchase-btn">カートに追加する</button>
                    </form>
                </div>

                <aside class="product-sidebar">
                    <h2 class="sidebar-title">プランの主な機能</h2>
                    
                    <ul class="feature-list">
                        <?php if (!empty($features_array)): ?>
                            <?php foreach ($features_array as $feature): ?>
                                <li><i class="fas fa-check-circle"></i> <?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><i class="fas fa-info-circle"></i> 機能の詳細は商品説明をご覧ください。</li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="sidebar-info">
                        <h3><i class="fas fa-file-alt"></i> 商品説明</h3>
                        <p><?php echo $description_text; ?></p>
                    </div>
                    <div class="sidebar-info">
                        <h3><i class="fas fa-info-circle"></i> こんな方におすすめ</h3>
                        <p><?php echo $recommend_text; ?></p>
                    </div>
                </aside>
            </div>
        </div>
    </main>
    <?php require "footer.php";?>
    <?php include './component/chatBot.php'; ?>
</body>

</html>