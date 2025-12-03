<?php
session_start();
require "../common/DBconnect.php";

// 1. ログインチェック
if (!isset($_SESSION['customer']['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['customer']['user_id'];

// =================================================================
// 2. フォーム送信処理
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_mode'])) {
    $selected_data = json_decode($_POST['selected_mode'], true);
    if ($selected_data) {
        $_SESSION['change_info'] = [
            'mode'             => $selected_data['mode'],
            'amount'           => $selected_data['amount'],
            'current_sub_id'   => $_POST['current_sub_id'],
            'current_end_date' => $_POST['current_end_date']
        ];
        header("Location: new-pay.php");
        exit;
    }
}

// 3. カートの中身チェック
$hasCustomPlan = isset($_SESSION['custom_options']) && !empty($_SESSION['custom_options']);
$hasPackagePlan = isset($_SESSION['package_plan']);

if (!$hasCustomPlan && !$hasPackagePlan) {
    header('Location: product.php');
    exit;
}

// 戻り先URL
$back_url = "product.php";
if ($hasCustomPlan) {
    $back_url = "custom.php";
} elseif ($hasPackagePlan) {
    $pid = $_SESSION['package_plan']['product_id'];
    $back_url = "pack.php?id=" . $pid;
}

try {
    // --------------------------------------------------
    // A. 現在の契約情報を取得 (ロジック修正)
    // --------------------------------------------------
    // 以前は「一番終了日が遅いもの(ORDER BY end_date DESC)」を取っていましたが、
    // それだと未来の予約プランを拾ってしまう可能性があります。
    // ここでは「現在有効なプラン(今日が期間内)」を最優先で探します。

    $sql = "SELECT * FROM Subscription 
            WHERE user_id = ? 
            AND status_id IN (1, 2)  
            AND end_date >= NOW() 
            ORDER BY start_date ASC"; // 開始日が早い順に全件取得
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$user_id]);
    $all_subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentSub = null;
    $today = date('Y-m-d');

    if ($all_subs) {
        // 1. まず「今日現在、利用中のプラン」を探す
        foreach ($all_subs as $sub) {
            if ($sub['start_date'] <= $today && $sub['end_date'] >= $today) {
                $currentSub = $sub;
                break; // 見つかったらループ終了
            }
        }

        // 2. もし「利用中のプラン」がなければ（未来の予約しかない場合など）、
        //    仕方ないので直近のプランを採用する
        if (!$currentSub) {
            $currentSub = $all_subs[0];
        }
    }

    if (!$currentSub) {
        header('Location: cart_register.php'); 
        exit;
    }
    

    // 現在の契約詳細
    $curr_start = new DateTime($currentSub['start_date']);
    $curr_end   = new DateTime($currentSub['end_date']);
    
    // 期間タイプ判定
    $curr_diff  = $curr_start->diff($curr_end)->days;
    $current_duration_type = 'monthly';
    if ($curr_diff > 1000) $current_duration_type = 'triennially';
    elseif ($curr_diff > 300) $current_duration_type = 'yearly';

    // 価格計算
    $current_is_custom = ($currentSub['product_id'] == 0);
    $base_price = 0;
    $current_plan_name = "";

    // ★追加: 月額換算の比較用変数
    $current_monthly_base = 0;

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
        $current_monthly_base = $base_price; // カスタムはDB価格が月額ベース

    } else {
        $pSql = "SELECT name, price FROM Products WHERE product_id = ?";
        $pStmt = $db->prepare($pSql);
        $pStmt->execute([$currentSub['product_id']]);
        $pData = $pStmt->fetch(PDO::FETCH_ASSOC);
        
        $base_price = (int)$pData['price'];
        $current_plan_name = $pData['name'];
        $current_monthly_base = $base_price; // パッケージもDB価格が月額ベース
    }

    $multiplier = 1;
    if ($current_duration_type === 'triennially') $multiplier = 25;
    elseif ($current_duration_type === 'yearly') $multiplier = 10;
    $current_total_price = $base_price * $multiplier;


    // --------------------------------------------------
    // B. 新しいプラン（カート）の情報
    // --------------------------------------------------
    $new_total_price = 0;
    $new_plan_name = "";
    $new_duration_type = 'monthly';
    $new_monthly_base = 0; // ★追加: 比較用
    $new_is_custom = false;
    $new_package_id = 0;

    if ($hasCustomPlan) {
        $new_is_custom = true;
        $new_total_price = (int)$_SESSION['custom_total_price'];
        $new_plan_name = "カスタムプラン (変更後)";
        $new_duration_type = $_SESSION['custom_billing_cycle'] ?? 'monthly';
        
        // カスタムの月額ベースを逆算
        if ($new_duration_type === 'yearly') {
            $new_monthly_base = round($new_total_price / 10); // 年額は10倍設定なので10で割る
        } else {
            $new_monthly_base = $new_total_price;
        }

    } elseif ($hasPackagePlan) {
        $new_package_id = $_SESSION['package_plan']['product_id'];
        $new_total_price = (int)$_SESSION['package_plan']['totalPrice'];
        $new_plan_name = $_SESSION['package_plan']['product_name'];
        $new_duration_type = $_SESSION['package_plan']['plan_type'];
        
        // パッケージの月額ベースを逆算
        if ($new_duration_type === 'triennially') {
            $new_monthly_base = round($new_total_price / 25);
        } elseif ($new_duration_type === 'yearly') {
            $new_monthly_base = round($new_total_price / 10);
        } else {
            $new_monthly_base = $new_total_price;
        }
    }


    // --------------------------------------------------
    // ★判定: 全く同じ商品内容か？（期間違いなだけか？）
    // --------------------------------------------------
    $is_same_content = false;
    if ($current_is_custom && $new_is_custom) {
        // カスタム同士: 月額ベースの金額が同じなら「同じ構成」とみなす
        // (厳密には項目チェックが必要ですが、簡易判定として金額一致を使用)
        if ($current_monthly_base == $new_monthly_base) {
            $is_same_content = true;
        }
    } elseif (!$current_is_custom && !$new_is_custom) {
        // パッケージ同士: IDが同じなら同じ商品
        if ($currentSub['product_id'] == $new_package_id) {
            $is_same_content = true;
        }
    }


    // --------------------------------------------------
    // C. 選択肢（オプション）の生成
    // --------------------------------------------------
    $options = [];

    // 1. 【アップグレード】
    if ($new_total_price > $current_total_price && $current_duration_type === $new_duration_type) {
        $diff = $new_total_price - $current_total_price;
        $options[] = [
            'id' => 'upgrade',
            'mode' => 'upgrade',
            'title' => '今すぐ適用（期間引継ぎ）',
            'price' => $diff,
            'desc' => '終了日は変わらず、機能だけをアップグレードします。<br>差額のみのお支払いです。',
            'end_date_label' => $curr_end->format('Y年m月d日') . ' (変更なし)',
            'badge' => 'おすすめ'
        ];
    }

    // 2. 【スイッチ】 (即時リセット)
    // ★変更点: 「中身が同じ」ならスイッチは出さない (予約のみにする)
    if (!$is_same_content) {
        $new_end_date_calc = new DateTime();
        if ($new_duration_type === 'triennially') $new_end_date_calc->modify('+3 years');
        elseif ($new_duration_type === 'yearly') $new_end_date_calc->modify('+1 year');
        else $new_end_date_calc->modify('+1 month');

        $options[] = [
            'id' => 'switch',
            'mode' => 'switch',
            'title' => '今すぐ適用（期間リセット）',
            'price' => $new_total_price,
            'desc' => '現在の契約を終了し、今日から新しい期間で契約し直します。<br>※旧契約の残期間分の返金はありません。',
            'end_date_label' => $new_end_date_calc->format('Y年m月d日') . ' (本日より開始)',
            'badge' => ''
        ];
    }

    // 3. 【予約】 (次回更新日に切替)
    $resSql = "SELECT COUNT(*) FROM Subscription WHERE user_id = ? AND start_date > NOW() AND status_id = 1";
    $resStmt = $db->prepare($resSql);
    $resStmt->execute([$user_id]);
    $reservedCount = $resStmt->fetchColumn();

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

    // もし選択肢が空になってしまった場合（同じ商品・同じ期間を選んだ場合など）
    if (empty($options)) {
        // 同じ商品で期間も同じなら、そもそも product.php 等で弾くべきですが、
        // 万が一ここに来たら「変更なし」のアラートを出して戻す
        echo "<script>alert('現在と同じプラン内容です。'); location.href='product.php';</script>";
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
    <style>
        .option-label { display: block; cursor: pointer; margin-bottom: 1.5rem; position: relative; }
        .option-card {
            border: 2px solid #e0e0e0; border-radius: 12px; padding: 1.5rem;
            display: flex; align-items: center; background: #fff; transition: all 0.2s;
        }
        .option-radio { display: none; }
        .option-radio:checked + .option-card {
            border-color: #4CAF50; background-color: #f9fff9;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
        }
        .check-circle {
            width: 24px; height: 24px; border-radius: 50%; border: 2px solid #ccc;
            display: flex; align-items: center; justify-content: center; color: white; margin-right: 15px; flex-shrink: 0;
        }
        .option-radio:checked + .option-card .check-circle { background: #4CAF50; border-color: #4CAF50; }
        
        .option-content { flex: 1; }
        .option-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .option-title { font-weight: bold; font-size: 1.1rem; }
        .option-price { font-weight: bold; font-size: 1.3rem; color: #333; }
        .option-desc { font-size: 0.9rem; color: #666; margin-bottom: 0.5rem; line-height: 1.5; }
        .option-date { font-size: 0.85rem; color: #2e7d32; font-weight: 600; }
        .badge { background: #FF9800; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; margin-left: 10px; vertical-align: middle; }
        
        .current-info { background: #f5f5f5; padding: 10px; border-radius: 8px; text-align: center; margin-bottom: 20px; color: #555; font-size: 0.9rem; }
        .submit-area { text-align: center; margin-top: 2rem; }
    </style>
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