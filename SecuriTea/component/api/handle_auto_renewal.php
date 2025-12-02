<?php
function handleAutoRenewal($userId)
{
    global $db;

    // トランザクション開始（途中でエラーが発生したら全てロールバック）
    $db->beginTransaction();

    try {
        // サブスクリプション情報を取得
        $sql_check = "SELECT true AS auto_renew_enabled, subscription_id, end_date, start_date
                      FROM Subscription
                      WHERE user_id = ? AND status_id = 1
                      LIMIT 1";

        $stmt = $db->prepare($sql_check);
        $stmt->execute([$userId]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        // 自動更新が無効またはサブスクリプションが存在しない場合は処理を中止
        if (!$subscription || !$subscription['auto_renew_enabled']) {
            $db->rollBack();
            return false;
        }

        $today = new DateTime();
        $expirationDate = new DateTime($subscription['end_date']);
        $startDate = new DateTime($subscription['start_date']);
        $days = $startDate->diff($expirationDate)->days+1;

        // まだ期限切れでない場合は自動更新しない
        if ($expirationDate >= $today) {
            $db->rollBack();
            return false;
        }

        // サブスクリプションに紐づく商品を取得
        $stmt = $db->prepare("
            SELECT p.product_id, p.name, p.price
            FROM SubscriptionCustoms c
            LEFT JOIN Products p ON c.product_id = p.product_id
            WHERE c.subscription_id = ?
        ");
        $stmt->execute([$subscription['subscription_id']]);
        $customs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 商品が存在しない場合は処理を中止
        if (empty($customs)) {
            $db->rollBack();
            return false;
        }

        // 商品総額を計算
        $totalAmount = array_sum(array_column($customs, 'price'));

        // 支払い情報を取得（Profilesテーブルの payment_token を使用）
        $stmt = $db->prepare("
            SELECT card_brand, masked_card_number, payment_token
            FROM Profiles
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            $db->rollBack();
            return false;
        }



        // Orders テーブルに注文を作成
        $stmt = $db->prepare("
            INSERT INTO Orders (user_id, total_amount, status)
            VALUES (?, ?, 'paid')
        ");
        $stmt->execute([$userId, $totalAmount]);
        $orderId = $db->lastInsertId();


        // Payments テーブルに支払い情報を登録
        $stmt = $db->prepare("
            INSERT INTO Payments ( order_id, amount, payment_method, status)
            VALUES (?, ?, ?, 'success')
        ");
        $stmt->execute([
            $orderId,
            $totalAmount,
            $payment['card_brand'] . ' ' . $payment['masked_card_number']
        ]);

        // Order_Items に商品を登録
        $stmt = $db->prepare("
            INSERT INTO Order_Items (order_id, product_name, price, quantity)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($customs as $custom) {
            $stmt->execute([
                $orderId,
                $custom['name'],
                $custom['price'],
                1
            ]);
        }

        // サブスクリプション期間を延長
        $newExpirationDate = $expirationDate->modify("+$days days")->format('Y-m-d');
        $stmt = $db->prepare("
            UPDATE Subscription
            SET end_date = ?, start_date = ?
            WHERE subscription_id = ?
        ");
        $stmt->execute([
            $newExpirationDate,
            $today->format('Y-m-d'),
            $subscription['subscription_id']
        ]);

        // 全て成功 → コミット
        $db->commit();
        return true;

    } catch (Exception $e) {
        // どこかでエラーが発生した場合は全てロールバック
        $db->rollBack();
        error_log("Auto Renewal Error: " . $e->getMessage());
        return false; // フロントには文字出力しない
    }
}
?>