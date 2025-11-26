<?php
session_start();
require "../common/DBconnect.php";

// --- 1. pack.php からのPOSTデータを受け取る ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id']) || !isset($_POST['plan_type'])) {
    header('Location: product.php');
    exit;
}

// --- 2. (重要) 反対のカート(カスタムプラン)をクリア ---
unset($_SESSION['custom_options']);
unset($_SESSION['custom_total_price']);
unset($_SESSION['custom_billing_cycle']);
unset($_SESSION['custom_term_start']);
unset($_SESSION['custom_term_end']);

// --- 3. データを取得し、価格と期間を計算 ---
$product_id = (int)$_POST['product_id'];
$product_name = htmlspecialchars($_POST['product_name'], ENT_QUOTES);
$plan_type = $_POST['plan_type'];
$monthly_price = (int)$_POST['monthly_price'];

// ... (価格計算、日付計算のロジックは変更なし) ...
$total_price = 0;
$plan_name = "";
$term_label = "";
$start_date = new DateTime();
$end_date = new DateTime();
switch ($plan_type) {
    case 'yearly':
        $total_price = $monthly_price * 10;
        $plan_name = "年間プラン";
        $end_date->modify('+1 year');
        $term_label = "/年";
        break;
    case 'triennially':
        $total_price = $monthly_price * 25;
        $plan_name = "3年プラン";
        $end_date->modify('+3 years');
        $term_label = "/3年";
        break;
    case 'monthly':
    default:
        $total_price = $monthly_price;
        $plan_name = "月間プラン";
        $end_date->modify('+1 month');
        $term_label = "/月";
        break;
}
function formatDateJP($date) {
    $weekMap = ['日', '月', '火', '水', '木', '金', '土'];
    $weekDay = $weekMap[$date->format('w')];
    return $date->format('Y年m月d日') . '(' . $weekDay . ')';
}
// ... (ここまで変更なし) ...

// --- 4. セッションに保存 ('package_plan' という新しいキー) ---
$_SESSION['package_plan'] = [
    'product_id' => $product_id,
    'product_name' => $product_name,
    'plan_type' => $plan_type,
    'plan_name' => $plan_name,
    'totalPrice' => $total_price,
    'termLabel' => $term_label,
    'termStart' => formatDateJP($start_date),
    'termEnd' => formatDateJP($end_date)
];

// --- 5. ★★★ 変更点 ★★★ ---
// cart.php へ直接行かず、account_check.php (ログインチェック) へリダイレクト
header('Location: account_check.php');
exit;
?>