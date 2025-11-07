<?php session_start();?>
<?php require "DBconnect.php";?>
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

    <?php
    // --- セッションからカスタムプランのデータを取得 ---
    
    // custom.phpから送られたセッションデータがあるか確認
    // (save_session.php で保存したキー)
    $hasCustomPlan = isset($_SESSION['custom_options']) && !empty($_SESSION['custom_options']);
    
    if ($hasCustomPlan) {
        // データを変数に取得
        $options = $_SESSION['custom_options'];
        $totalPrice = $_SESSION['custom_total_price'];
        $billingCycle = $_SESSION['custom_billing_cycle'];
        $termStart = $_SESSION['custom_term_start'];
        $termEnd = $_SESSION['custom_term_end'];
        
        // 表示用に加工
        $totalCount = count($options);
        $cycleText = ($billingCycle === 'yearly') ? '年間' : '月間';
        $cycleLabel = ($billingCycle === 'yearly') ? '/年' : '/月';
        $planName = $cycleText . "カスタムプラン";
    }
    ?>

    <section id="selected-options"> 

        <?php if ($hasCustomPlan): ?>
            <h2>カートの内容</h2>
            
            <h3><?php echo htmlspecialchars($planName, ENT_QUOTES); ?></h3>
            <h3><?php echo htmlspecialchars($totalCount, ENT_QUOTES); ?>項目</h3>
            
            <?php 
            // オプション一覧を表示
            foreach ($options as $option): 
                // $option は ['label' => 'オプション名'] の形式です
            ?>
                <h3>オプション: <?php echo htmlspecialchars($option['label'], ENT_QUOTES); ?></h3>
            <?php endforeach; ?>

            <h3>期間：<?php echo htmlspecialchars($termStart, ENT_QUOTES); ?> ～ <?php echo htmlspecialchars($termEnd, ENT_QUOTES); ?></h3>
            <h3>金額：<?php echo htmlspecialchars($totalPrice, ENT_QUOTES); ?>円<?php echo htmlspecialchars($cycleLabel, ENT_QUOTES); ?></h3>

            <div class="cart-actions">
                <a href="custom.php" class="product-btn secondary-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>プランを変更する</span>
                </a>
                <a href="new-pay.php" class="product-btn">
                    <span>購入手続きへ</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

        <?php else: ?>
            <h2>カートは空です</h2>
            <p style="margin-top: 1rem; text-align: center;">カスタムプランを選択してください。</p>
            <div class="cart-actions">
                <a href="custom.php" class="product-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>カスタムプラン選択へ</span>
                </a>
            </div>

        <?php endif; ?>
        
        </section>

    <main class="main">
        <div class="container">
            </div>
    </main>
    
    <?php require "footer.php"; // footer.php があれば読み込みます ?>
    <?php include 'component/chatBot.php'; // chatBot.php があれば読み込みます ?>

</body>
</html>