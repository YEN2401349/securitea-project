<?php
session_start();
require "../common/DBconnect.php";

// 1. ログインしているか確認
if (isset($_SESSION['customer']['user_id'])) {
    $user_id = $_SESSION['customer']['user_id'];

    // 2. カートテーブル(Cart)を取得
    $sql_cart = $db->prepare("SELECT cart_id, total_amount FROM Cart WHERE user_id = ?");
    $sql_cart->execute([$user_id]);
    $cart = $sql_cart->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        $cart_id = $cart['cart_id'];
        $db_total_price = (int)$cart['total_amount'];

        // 3. カート明細(Cart_Items)と商品(Products)を結合して取得
        $sql_items = $db->prepare("
            SELECT ci.product_id, ci.price as item_price, p.name, p.category_id, p.price as base_price 
            FROM Cart_Items ci
            LEFT JOIN Products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?
        ");
        $sql_items->execute([$cart_id]);
        $items = $sql_items->fetchAll(PDO::FETCH_ASSOC);

        // 4. データの中身を見て「パッケージ」か「カスタム」か判定し、セッションを再構築
        if (!empty($items)) {
            $is_package = false;
            $package_item = null;
            $custom_options = [];
            $custom_base_total = 0; // 期間係数をかける前の合計

            foreach ($items as $item) {
                if ($item['category_id'] == 1) {
                    // カテゴリ1はパッケージ
                    $is_package = true;
                    $package_item = $item;
                } elseif ($item['category_id'] == 2) {
                    // カテゴリ2はカスタムオプション
                    $custom_options[] = [
                        'id' => $item['product_id'],
                        'label' => $item['name'],
                        'price' => (int)$item['base_price'] // 基本単価
                    ];
                    $custom_base_total += (int)$item['base_price'];
                }
            }

            // --- A. パッケージプランの場合の復元 ---
            if ($is_package && $package_item) {
                // 期間(monthly/yearly/triennially)を金額から逆算判定
                $base = (int)$package_item['base_price'];
                $total = $db_total_price;
                $plan_type = 'monthly';
                $term_label = '/月';

                if ($base > 0) {
                    if ($total >= $base * 20) { // おおよそ3年(25倍)
                        $plan_type = 'triennially';
                        $term_label = '/3年';
                    } elseif ($total >= $base * 9) { // おおよそ1年(10倍)
                        $plan_type = 'yearly';
                        $term_label = '/年';
                    }
                }

                // 日付の再計算
                $start_dt = new DateTime();
                $end_dt = new DateTime();
                if ($plan_type === 'yearly') $end_dt->modify('+1 year');
                elseif ($plan_type === 'triennially') $end_dt->modify('+3 years');
                else $end_dt->modify('+1 month');

                // セッションに書き戻す (これで次のページも動く)
                $_SESSION['package_plan'] = [
                    'product_id' => $package_item['product_id'],
                    'product_name' => $package_item['name'],
                    'plan_type' => $plan_type,
                    'plan_name' => ($plan_type == 'monthly' ? '月間' : ($plan_type == 'yearly' ? '年間' : '3年')) . 'プラン',
                    'totalPrice' => $total,
                    'termLabel' => $term_label,
                    'termStart' => $start_dt->format('Y年m月d日'), // 曜日は省略
                    'termEnd' => $end_dt->format('Y年m月d日')
                ];
                // 競合するカスタムプランは消す
                unset($_SESSION['custom_options']);
            }
            
            // --- B. カスタムプランの場合の復元 ---
            elseif (!empty($custom_options)) {
                // 期間を金額から逆算
                // DBのtotal_amount が custom_base_total の何倍かで判定
                $cycle = 'monthly';
                if ($custom_base_total > 0 && $db_total_price >= $custom_base_total * 9) {
                    $cycle = 'yearly';
                }

                // 日付の再計算
                $start_dt = new DateTime();
                $end_dt = new DateTime();
                if ($cycle === 'yearly') $end_dt->modify('+1 year');
                else $end_dt->modify('+1 month');

                // セッションに書き戻す
                $_SESSION['custom_options'] = $custom_options;
                $_SESSION['custom_total_price'] = $db_total_price;
                $_SESSION['custom_billing_cycle'] = $cycle;
                $_SESSION['custom_term_start'] = $start_dt->format('Y年m月d日');
                $_SESSION['custom_term_end'] = $end_dt->format('Y年m月d日');
                
                // 競合するパッケージプランは消す
                unset($_SESSION['package_plan']);
            }
        }
    }
}

// -------------------------------------------------------------------
// ここから表示ロジック (セッション変数を使うが、上で復元済みなので安心)
// -------------------------------------------------------------------

// (A) カスタムプランのチェック
$hasCustomPlan = isset($_SESSION['custom_options']) && !empty($_SESSION['custom_options']);

// (B) パッケージプランのチェック
$hasPackagePlan = isset($_SESSION['package_plan']);

// 変数の初期化
$planName = "";
$totalCount = 0;
$options = [];
$termStart = "";
$termEnd = "";
$totalPrice = 0;
$cycleLabel = "";
$plan = [];

if ($hasCustomPlan) {
    $options = $_SESSION['custom_options'];
    $totalPrice = $_SESSION['custom_total_price'];
    $billingCycle = $_SESSION['custom_billing_cycle'];
    $termStart = $_SESSION['custom_term_start'];
    $termEnd = $_SESSION['custom_term_end'];
    
    $totalCount = count($options);
    $cycleText = ($billingCycle === 'yearly') ? '年間' : '月間';
    $cycleLabel = ($billingCycle === 'yearly') ? '/年' : '/月';
    $planName = $cycleText . "カスタムプラン";
    
} elseif ($hasPackagePlan) {
    $plan = $_SESSION['package_plan'];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecuriTea(セキュリティー) - カート</title>
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="component/css/chatBot.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php require "headerTag.php";?>

    <section id="selected-options"> 

        <?php if ($hasCustomPlan): ?>
            <h2>カートの内容</h2>
            
            <h3><?php echo htmlspecialchars($planName, ENT_QUOTES); ?></h3>
            <h3><?php echo htmlspecialchars($totalCount, ENT_QUOTES); ?>項目</h3>
            
            <?php foreach ($options as $option): ?>
                <h3>オプション: <?php echo htmlspecialchars($option['label'], ENT_QUOTES); ?></h3>
            <?php endforeach; ?>

            <h3>期間：<?php echo htmlspecialchars($termStart, ENT_QUOTES); ?> ～ <?php echo htmlspecialchars($termEnd, ENT_QUOTES); ?></h3>
            <h3>金額：<?php echo number_format($totalPrice); ?>円<?php echo htmlspecialchars($cycleLabel, ENT_QUOTES); ?></h3>

            <div class="cart-actions">
                <a href="custom.php" class="product-btn secondary-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>プランを変更する</span>
                </a>
                <?php $next_link = isset($_SESSION['customer']['user_id']) ? "new-pay.php" : "account_check.php"; ?>
                <a href="<?php echo $next_link; ?>" class="product-btn">
                    <span>購入手続きへ</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
        <?php elseif ($hasPackagePlan): ?>
            <h2>カートの内容</h2>
            
            <h3><?php echo htmlspecialchars($plan['product_name'], ENT_QUOTES); ?></h3>
            <h3>プラン: <?php echo htmlspecialchars($plan['plan_name'], ENT_QUOTES); ?></h3>

            <h3>期間：<?php echo htmlspecialchars($plan['termStart'], ENT_QUOTES); ?> ～ <?php echo htmlspecialchars($plan['termEnd'], ENT_QUOTES); ?></h3>
            <h3>金額：<?php echo number_format($plan['totalPrice']); ?>円 (<?php echo htmlspecialchars($plan['termLabel'], ENT_QUOTES); ?>)</h3>

            <div class="cart-actions">
                <a href="pack.php?id=<?php echo htmlspecialchars($plan['product_id'], ENT_QUOTES); ?>" class="product-btn secondary-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>プランを変更する</span>
                </a>
                
                <?php 
                    // デフォルトは決済画面へ
                    $next_action = "new-pay.php";
                    
                    // ログインしていないならログイン確認へ
                    if (!isset($_SESSION['customer']['user_id'])) {
                        $next_action = "login.php";
                    }
                ?>
                <a href="new-pay.php" class="product-btn">
                    <span>購入手続きへ</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

        <?php else: ?>
            <br><h2>カートは空です。</h2>
            <p style="margin-top: 1rem; text-align: center;">プランを選択してください。</p>
            <div class="cart-actions">
                <a href="product.php" class="product-btn secondary-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>商品一覧へ</span>
                </a>
                <a href="custom.php" class="product-btn">
                    <span>カスタムプラン選択へ</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

        <?php endif; ?>
        
    </section>

    <main class="main">
        <div class="container"></div>
    </main>
    
    <?php require "footer.php";?>

</body>
</html>