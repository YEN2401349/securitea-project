<?php
session_start();
require "../common/DBconnect.php";

// ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['customer']['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_mode'])) {
    $selected_data = json_decode($_POST['selected_mode'], true);
    if ($selected_data) {
        $_SESSION['change_info'] = [
            'mode'             => $selected_data['mode'],
            'amount'           => $selected_data['amount'],
            'current_sub_id'   => $_POST['current_sub_id'],
            'current_end_date' => $_POST['current_end_date']
        ];
        header("Location: paymentChange.php");
        exit;
    }
}

$hasCustomPlan = isset($_SESSION['custom_options']) && !empty($_SESSION['custom_options']);
$hasPackagePlan = isset($_SESSION['package_plan']);

if (!$hasCustomPlan && !$hasPackagePlan) {
    header('Location: product.php');
    exit;
}

$back_url = "product.php";
if ($hasCustomPlan) {
    $back_url = "custom.php";
} elseif ($hasPackagePlan) {
    $pid = $_SESSION['package_plan']['product_id'];
    $back_url = "pack.php?id=" . $pid;
}

try {
    $sql = "SELECT * FROM Subscription 
            WHERE user_id = ? 
            AND status_id IN (1, 2, 5, 6)  
            AND end_date >= NOW() 
            ORDER BY start_date ASC";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id]);
    $all_subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentSub = null;
    $today_str = date('Y-m-d');

    if ($all_subs) {
        foreach ($all_subs as $sub) {
            if ($sub['start_date'] <= $today_str && $sub['end_date'] >= $today_str) {
                $currentSub = $sub;
                break; 
            }
        }
        if (!$currentSub) {
            $currentSub = $all_subs[0];
        }
    }

    if (!$currentSub) {
        header('Location: cart_register.php'); 
        exit;
    }

    // 予約があるか
    $resSql = "SELECT COUNT(*) FROM Subscription WHERE user_id = ? AND start_date > NOW() AND status_id IN (5, 6)";
    $resStmt = $db->prepare($resSql);
    $resStmt->execute([$user_id]);
    $reservedCount = $resStmt->fetchColumn();


    // 現在の契約詳細
    $curr_start = new DateTime($currentSub['start_date']);
    $curr_end   = new DateTime($currentSub['end_date']);
    
    $curr_diff  = $curr_start->diff($curr_end)->days;
    $current_duration_type = 'monthly';
    if ($curr_diff > 1000) $current_duration_type = 'triennially';
    elseif ($curr_diff > 300) $current_duration_type = 'yearly';

    // 価格計算
    $current_is_custom = ($currentSub['product_id'] == 0);
    $base_price = 0;
    $current_plan_name = "";

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

    $multiplier = 1;
    if ($current_duration_type === 'triennially') $multiplier = 25;
    elseif ($current_duration_type === 'yearly') $multiplier = 10;
    $current_total_price = $base_price * $multiplier;

    $new_total_price = 0;
    $new_plan_name = "";
    $new_duration_type = 'monthly';
    $new_is_custom = false;
    $new_package_id = 0;

    if ($hasCustomPlan) {
        $new_is_custom = true;
        $new_total_price = (int)$_SESSION['custom_total_price'];
        $new_plan_name = "カスタムプラン (変更後)";
        $new_duration_type = $_SESSION['custom_billing_cycle'] ?? 'monthly';

    } elseif ($hasPackagePlan) {
        $new_package_id = $_SESSION['package_plan']['product_id'];
        $new_total_price = (int)$_SESSION['package_plan']['totalPrice'];
        $new_plan_name = $_SESSION['package_plan']['product_name'];
        $new_duration_type = $_SESSION['package_plan']['plan_type'];
    }

    $is_same_content = false;

    if ($current_is_custom && $new_is_custom) {
        $chkSql = $db->prepare("SELECT product_id FROM SubscriptionCustoms WHERE subscription_id = ?");
        $chkSql->execute([$currentSub['subscription_id']]);
        $current_opt_ids = $chkSql->fetchAll(PDO::FETCH_COLUMN); 
        $new_opt_ids = array_column($_SESSION['custom_options'], 'id');

        $current_opt_ids = array_map('intval', $current_opt_ids);
        $new_opt_ids     = array_map('intval', $new_opt_ids);
        sort($current_opt_ids);
        sort($new_opt_ids);

        if ($current_opt_ids === $new_opt_ids) {
            $is_same_content = true;
        }

    } elseif (!$current_is_custom && !$new_is_custom) {
        if ($currentSub['product_id'] == $new_package_id) {
            $is_same_content = true;
        }
    }

    $options = [];

    // アップグレード
    if ($new_total_price > $current_total_price && 
        $current_duration_type === $new_duration_type && 
        !$is_same_content) {
        
        $diff = $new_total_price - $current_total_price;

        $upgrade_desc = '終了日は変わらず、機能だけをアップグレードします。<br>差額のみのお支払いです。';
        
        if ($reservedCount > 0) {
            $upgrade_desc .= '<br><strong style="color:#d32f2f;">【重要】このオプションを選ぶと、現在予約中のプランはキャンセルされ、返金は行われません。</strong>';
        }

        $options[] = [
            'id' => 'upgrade',
            'mode' => 'upgrade',
            'title' => '今すぐ適用（期間引継ぎ）',
            'price' => $diff,
            'desc' => $upgrade_desc,
            'end_date_label' => $curr_end->format('Y年m月d日') . ' (変更なし)',
            'badge' => 'おすすめ'
        ];
    }

    // スイッチ
    $is_cancelled = in_array($currentSub['status_id'], [2, 6]);

    if (!$is_same_content || $is_cancelled) {
        $new_end_date_calc = new DateTime();
        if ($new_duration_type === 'triennially') $new_end_date_calc->modify('+3 years');
        elseif ($new_duration_type === 'yearly') $new_end_date_calc->modify('+1 year');
        else $new_end_date_calc->modify('+1 month');

        $switch_desc = '現在の契約を終了し、今日から新しい期間で契約し直します。<br>※旧契約の残期間分の返金はありません。';
        
        if ($reservedCount > 0) {
            $switch_desc .= '<br><strong style="color:#d32f2f;">【重要】このオプションを選ぶと、現在予約中のプランはキャンセルされ、返金は行われません。</strong>';
        }

        $options[] = [
            'id' => 'switch',
            'mode' => 'switch',
            'title' => '今すぐ適用（期間リセット）',
            'price' => $new_total_price,
            'desc' => $switch_desc,
            'end_date_label' => $new_end_date_calc->format('Y年m月d日') . ' (本日より開始)',
            'badge' => ''
        ];
    }

    // 予約
    if ($reservedCount == 0) {
        $reserve_start = clone $curr_end;
        $reserve_start->modify('+1 day');
        
        $reserve_end = clone $reserve_start;
        if ($new_duration_type === 'triennially') $reserve_end->modify('+3 years');
        elseif ($new_duration_type === 'yearly') $reserve_end->modify('+1 year');
        else $reserve_end->modify('+1 month');

        $options[] = [
            'id' => 'reserve',
            'mode' => 'reserve',
            'title' => '自動更新日に切替',
            'price' => $new_total_price,
            'desc' => '現在の契約期間が終了した後、自動的に切り替わります。<br>お支払いは先行して行われます。',
            'end_date_label' => $reserve_end->format('Y年m月d日') . ' (次回更新後)',
            'badge' => '予約'
        ];
    }

    if (empty($options)) {
        echo "<script>alert('現在と同じプラン内容です。変更の必要はありません。'); location.href='product.php';</script>";
        exit;
    }

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
    <title>プラン変更方法の選択 - SecuriTea</title>
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php require "headerTag.php";?>

    <main class="main">
        <div class="container" style="max-width: 800px;">
            <h2>プラン変更の方法を選択</h2>
            <p style="text-align:center; margin-bottom: 1.5rem;">ご希望の変更タイミングとお支払い方法を選択してください。</p>

            <div class="current-info">
                現在のプラン: <strong><?php echo htmlspecialchars($current_plan_name); ?></strong><br>
                現在の契約終了日: <strong><?php echo $curr_end->format('Y年m月d日'); ?></strong>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="current_sub_id" value="<?php echo $currentSub['subscription_id']; ?>">
                <input type="hidden" name="current_end_date" value="<?php echo $currentSub['end_date']; ?>">

                <?php if (empty($options)): ?>
                    <p style="text-align: center; color: red;">変更可能なプランが見つかりませんでした。</p>
                <?php else: ?>
                    <?php foreach ($options as $index => $opt): ?>
                        <label class="option-label">
                            <input type="radio" name="selected_mode" class="option-radio"
                                   value='<?php echo json_encode(["mode" => $opt['mode'], "amount" => $opt['price']]); ?>'
                                   <?php echo ($index === 0) ? 'checked' : ''; ?>>
                            
                            <div class="option-card">
                                <div class="check-circle"><i class="fas fa-check"></i></div>
                                <div class="option-content">
                                    <div class="option-header">
                                        <div class="option-title">
                                            <?php echo htmlspecialchars($opt['title']); ?>
                                            <?php if($opt['badge']): ?><span class="badge"><?php echo $opt['badge']; ?></span><?php endif; ?>
                                        </div>
                                        <div class="option-price">¥<?php echo number_format($opt['price']); ?></div>
                                    </div>
                                    <div class="option-desc"><?php echo $opt['desc']; ?></div>
                                    <div class="option-date">
                                        <i class="far fa-calendar-alt"></i> 終了予定日: <?php echo htmlspecialchars($opt['end_date_label']); ?>
                                    </div>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="cart-actions submit-area">
                    <a href="<?php echo htmlspecialchars($back_url); ?>" class="product-btn secondary-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>戻る</span>
                    </a>
                    
                    <?php if (!empty($options)): ?>
                    <button type="submit" class="product-btn">
                        <span>次へ進む</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </main>
    
    <?php require "footer.php";?>
</body>
</html>