<?php
session_start();
require "DBconnect.php";

// 1. ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['customer']['user_id'];

// 2. カートの中身チェック
$hasCustomPlan = isset($_SESSION['custom_options']) && !empty($_SESSION['custom_options']);
$hasPackagePlan = isset($_SESSION['package_plan']);

if (!$hasCustomPlan && !$hasPackagePlan) {
    header('Location: product.php');
    exit;
}

// 戻り先URLの決定
$back_url = "product.php";
if ($hasCustomPlan) {
    $back_url = "custom.php";
} elseif ($hasPackagePlan) {
    $pid = $_SESSION['package_plan']['product_id'];
    $back_url = "pack.php?id=" . $pid;
}

try {
    // --------------------------------------------------
    // A. 現在の契約情報を取得
    // --------------------------------------------------
    $sql = "SELECT * FROM Subscription 
            WHERE user_id = ? 
            AND status_id IN (1, 2)  
            AND end_date >= NOW() 
            ORDER BY end_date DESC LIMIT 1";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id]);
    $currentSub = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentSub) {
        header('Location: cart_register.php'); 
        exit;
    }

    // --------------------------------------------------
    // 現在の契約コース（期間タイプ）を判定
    // --------------------------------------------------
    $curr_start = new DateTime($currentSub['start_date']);
    $curr_end   = new DateTime($currentSub['end_date']);
    $curr_diff  = $curr_start->diff($curr_end)->days;

    $current_duration_type = 'monthly'; 
    if ($curr_diff > 1000) {
        $current_duration_type = 'triennially';
    } elseif ($curr_diff > 300) {
        $current_duration_type = 'yearly';
    }

    // --------------------------------------------------
    // A-1. 現在のプラン詳細 & 定価総額の計算
    // --------------------------------------------------
    $current_is_custom = ($currentSub['product_id'] == 0);
    $current_total_price = 0;   // ★定価総額（計算用）
    $current_monthly_price = 0; // 表示用（月額換算）
    $current_plan_name = "";
    $is_cancelled = ($currentSub['status_id'] == 2);
    
    // ベースとなる月額価格を取得
    $base_price = 0;

    if ($current_is_custom) {
        $cSql = "SELECT SUM(p.price) as total, COUNT(*) as count 
                 FROM SubscriptionCustoms sc
                 JOIN Products p ON sc.product_id = p.product_id
                 WHERE sc.subscription_id = ?";
        $cStmt = $db->prepare($cSql);
        $cStmt->execute([$currentSub['subscription_id']]);
        $cData = $cStmt->fetch(PDO::FETCH_ASSOC);
        
        $base_price = (int)$cData['total'];
        $current_plan_name = "カスタムプラン (" . $cData['count'] . "項目)";
    } else {
        $pSql = "SELECT name, price FROM Products WHERE product_id = ?";
        $pStmt = $db->prepare($pSql);
        $pStmt->execute([$currentSub['product_id']]);
        $pData = $pStmt->fetch(PDO::FETCH_ASSOC);
        
        $base_price = (int)$pData['price'];
        $current_plan_name = $pData['name'];
    }

    // ★重要: 定価総額（current_total_price）を期間タイプから算出
    // (add_pack.phpなどのロジックと合わせる: 年額=10倍, 3年=25倍)
    if ($current_duration_type === 'triennially') {
        $current_total_price = $base_price * 25;
    } elseif ($current_duration_type === 'yearly') {
        $current_total_price = $base_price * 10;
    } else {
        $current_total_price = $base_price; // 月額
    }
    
    // 表示用の月額換算はベース価格そのまま
    $current_monthly_price = $base_price;


    // --------------------------------------------------
    // B. 新しいプラン（カート）の情報を整理 & 月額換算
    // --------------------------------------------------
    $new_total_price = 0; 
    $new_monthly_price = 0; 
    $new_plan_name = "";
    $new_is_custom = false;
    $new_product_id = 0; 
    $new_duration_type = 'monthly'; 

    if ($hasCustomPlan) {
        $new_is_custom = true;
        $new_total_price = (int)$_SESSION['custom_total_price'];
        $new_plan_name = "カスタムプラン (変更後)";
        $new_duration_type = $_SESSION['custom_billing_cycle'] ?? 'monthly';
        
        if ($new_duration_type === 'yearly') {
            $new_monthly_price = round($new_total_price / 12);
        } else {
            $new_monthly_price = $new_total_price;
        }

    } elseif ($hasPackagePlan) {
        $new_is_custom = false;
        $new_total_price = (int)$_SESSION['package_plan']['totalPrice'];
        $new_plan_name = $_SESSION['package_plan']['product_name'];
        $new_product_id = (int)$_SESSION['package_plan']['product_id'];
        $new_duration_type = $_SESSION['package_plan']['plan_type']; 

        if ($new_duration_type === 'yearly') {
            $new_monthly_price = round($new_total_price / 12);
        } elseif ($new_duration_type === 'triennially') {
            $new_monthly_price = round($new_total_price / 36);
        } else {
            $new_monthly_price = $new_total_price;
        }
    }

    // --------------------------------------------------
    // C. パターン判定
    // --------------------------------------------------
    $today = new DateTime();
    $endDate = new DateTime($currentSub['end_date']);
    
    // (日割り計算はしなくなりましたが、表示用に日数は一応残しておきます)
    $interval_remaining = $today->diff($endDate);
    $days_remaining = max(0, $interval_remaining->days);

    $change_mode = ""; 
    $pay_amount = 0;
    $message = "";
    $note = "";

    // ★判定ロジック

    // 1. 同一パッケージプランのチェック
    if (!$current_is_custom && !$new_is_custom && ($currentSub['product_id'] == $new_product_id)) {
        
        // 全く同じコースの場合
        if ($current_duration_type === $new_duration_type) {
            echo "<script>alert('現在ご契約中のプラン・期間と同じ内容です。変更の必要はありません。'); location.href='product.php';</script>";
            exit;
        }

        // 期間変更 (Reserve)
        $change_mode = "reserve";
        $pay_amount = $new_total_price;
        $message = "契約更新の予約";
        $note = "現在の契約期間（{$endDate->format('Y/m/d')}）が終了した後、自動的に新しい期間の契約が開始されます。";

    } elseif ($current_is_custom === $new_is_custom) {
        // 同系統 (Custom/Custom) -> Upgrade
        $change_mode = "upgrade";
        
        // ★修正: シンプルな差額計算 (新定価 - 旧定価)
        $diff_price = $new_total_price - $current_total_price; 
        
        if ($diff_price > 0) {
            // ★日割りを廃止し、差額そのまま
            $pay_amount = $diff_price;
            
            $message = "プランのアップグレード";
            $note = "現在の契約期間を引き継ぎ、プラン変更による差額をお支払いいただきます。";
        } else {
            $pay_amount = 0;
            $message = "プランの変更";
            $note = "現在の契約期間を引き継いでプランを変更します。追加費用は発生しません。";
        }
        if ($is_cancelled) { $message .= " (再開)"; }

    } else {
        // 異系統 (Switch)
        $change_mode = "switch";
        $pay_amount = $new_total_price;
        $message = "プランの乗り換え";
        $note = "現在の契約を終了し、本日から新しいプランで契約を開始します。";
    }

    // --------------------------------------------------
    // 予約重複チェック
    // --------------------------------------------------
    $resSql = "SELECT COUNT(*) FROM Subscription WHERE user_id = ? AND start_date > NOW() AND status_id = 1";
    $resStmt = $db->prepare($resSql);
    $resStmt->execute([$user_id]);
    $reservedCount = $resStmt->fetchColumn();

    $warning_script = ""; 

    if ($reservedCount > 0) {
        if ($change_mode === 'reserve') {
            echo "<script>alert('既に次回のプラン変更が予約されています。これ以上の変更予約はできません。'); location.href='product.php';</script>";
            exit;
        } else {
            $warning_msg = "【重要】\\n現在、次回の契約更新予約（プラン予約）が入っています。\\n\\nこのままプラン変更を行うと、予約済みのプランはキャンセルされ、返金は行われません。\\n\\n本当によろしいですか？";
            $warning_script = "return confirm('$warning_msg');";
        }
    }

    // セッション保存
    $_SESSION['change_info'] = [
        'mode' => $change_mode,          
        'amount' => $pay_amount,         
        'current_sub_id' => $currentSub['subscription_id'],
        'current_end_date' => $currentSub['end_date'] 
    ];

} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>契約変更の確認 - SecuriTea</title>
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .comparison-container { display: flex; justify-content: center; gap: 2rem; margin: 2rem 0; flex-wrap: wrap; }
        .plan-card { border: 1px solid #ddd; padding: 2rem; border-radius: 8px; width: 300px; background: #fff; text-align: center; }
        .plan-card.new { border: 2px solid #4CAF50; background: #f9fff9; position: relative; }
        .plan-card.new::after { content: "NEW"; position: absolute; top: -10px; right: -10px; background: #4CAF50; color: white; padding: 5px 10px; border-radius: 20px; font-weight: bold; font-size: 0.8rem; }
        .arrow-icon { align-self: center; font-size: 2rem; color: #888; }
        .payment-summary { background: #f8f9fa; padding: 2rem; border-radius: 8px; text-align: center; margin-top: 2rem; border: 1px solid #ddd; }
        .price-highlight { font-size: 2.5rem; color: #d32f2f; font-weight: bold; margin: 10px 0; }
        .badge-reserve { background:#ff9800; color:white; padding:4px 8px; border-radius:4px; font-size:0.8rem; vertical-align:middle; }
    </style>
</head>

<body>
    <?php require "headerTag.php";?>

    <main class="main">
        <div class="container">
            <h2>
                <?php echo htmlspecialchars($message); ?>
                <?php if($change_mode === 'reserve'): ?>
                    <span class="badge-reserve">次回予約</span>
                <?php endif; ?>
            </h2>
            <p style="text-align:center;">以下の内容で契約を変更します。</p>

            <div class="comparison-container">
                <div class="plan-card">
                    <h3>現在のプラン</h3>
                    <p><strong><?php echo htmlspecialchars($current_plan_name); ?></strong></p>
                    <p>月額換算: ¥<?php echo number_format($current_monthly_price); ?></p>
                    <p style="font-size:0.9rem; color:#666;">終了日: <?php echo $endDate->format('Y年m月d日'); ?></p>
                    <?php if($is_cancelled): ?>
                        <p style="color:red; font-size:0.8rem;">(解約予約済み)</p>
                    <?php endif; ?>
                </div>

                <div class="arrow-icon"><i class="fas fa-arrow-right"></i></div>

                <div class="plan-card new">
                    <h3>変更後のプラン</h3>
                    <p><strong><?php echo htmlspecialchars($new_plan_name); ?></strong></p>
                    <p>月額換算: ¥<?php echo number_format($new_monthly_price); ?></p>
                    
                    <?php if($change_mode === 'reserve'): ?>
                        <p style="color:#e65100; font-weight:bold; font-size:0.9rem;">
                            開始日: <?php echo $endDate->modify('+1 day')->format('Y年m月d日'); ?>
                        </p>
                    <?php elseif($change_mode === 'upgrade'): ?>
                        <p style="color:#666; font-size:0.9rem;">※契約期間は現在のまま引き継がれます</p>
                    <?php else: ?>
                        <p style="color:#666; font-size:0.9rem;">※本日から新しい期間で契約開始となります</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="payment-summary">
                <h3>今回のお支払い金額</h3>
                <?php if ($change_mode === 'upgrade'): ?>
                    <p>アップグレードに伴う差額をお支払いいただきます。</p>
                <?php elseif ($change_mode === 'reserve'): ?>
                    <p>次回契約期間分の料金を先行してお支払いいただきます。</p>
                <?php else: ?>
                    <p>プラン切り替えのため、新規契約として料金をお支払いいただきます。</p>
                <?php endif; ?>
                
                <div class="price-highlight">¥<?php echo number_format($pay_amount); ?></div>

                <div class="cart-actions" style="justify-content: center;">
                    <a href="<?php echo htmlspecialchars($back_url); ?>" class="product-btn secondary-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>戻る</span> 
                    </a>
                    
                    <a href="new-pay.php" class="product-btn" onclick="<?php echo $warning_script; ?>">
                        <span>支払い手続きへ</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <p style="text-align:center; color:#666; margin-top:10px; font-size:0.9rem;"><?php echo $note; ?></p>
        </div>
    </main>
    
    <?php require "footer.php";?>
</body>
</html>