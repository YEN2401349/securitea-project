<?php
// データベース接続を読み込む
require_once 'DBconnect.php';

// 出力形式をJSONに指定
header('Content-Type: application/json; charset=UTF-8');

$results = ['status' => 'empty', 'data' => []];
$term = '';

// 1. キーワードの受け取り
if (isset($_GET['term'])) {
    $term = trim($_GET['term']);
}

// キーワードが空の場合は何も返さず終了
if (empty($term)) {
    echo json_encode($results);
    exit;
}

try {
    // 2. SQLインジェクション対策 (LIKE検索用のキーワード準備)
    $like_term = '%' . $term . '%';

    // 3. データベース検索 (要件通りのSQL)
    //    - name または security_features の部分一致
    //    - product_id の昇順
    //    - 最大10件
    $sql = "SELECT product_id, name, category_id 
            FROM Products 
            WHERE (name LIKE ? OR security_features LIKE ?) 
            ORDER BY product_id ASC 
            LIMIT 10";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$like_term, $like_term]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. 結果を整形
    $suggestions = [];
    foreach ($products as $product) {
        
        // 5. category_id に応じて遷移先URLを決定
        $url = '';
        if ($product['category_id'] == 1) {
            // category_id 1 -> pack.php
            $url = 'pack.php?id=' . (int)$product['product_id'];
        } else if ($product['category_id'] == 2) {
            // category_id 2 -> custom.php (ID不要)
            $url = 'custom.php';
        } else {
            // 念のため、それ以外は商品一覧へ
            $url = 'product.php'; 
        }

        $suggestions[] = [
            'name' => htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'),
            'url'  => $url
        ];
    }
    
    if (count($suggestions) > 0) {
        $results['status'] = 'success';
        $results['data'] = $suggestions;
    }

    // 6. JSON形式で出力
    echo json_encode($results);

} catch (PDOException $e) {
    // エラーハンドリング
    $results['status'] = 'error';
    $results['message'] = $e->getMessage();
    echo json_encode($results);
}
?>