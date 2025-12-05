<?php
function handleAutoRenewal($userId)
{
    global $db;

    $db->beginTransaction();

    try {

        // 購読データを取得
        $sql = "
            SELECT subscription_id, end_date, start_date, status_id
            FROM Subscription
            WHERE user_id = ? AND status_id IN (1,5,6)
            ORDER BY status_id ASC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 購読なし → 自動更新しない
        if (count($subs) == 0) {
            $db->rollBack();
            return false;
        }

        // ====================================================
        //  CASE 1 ————「1件のみ」→ 自動更新を行う
        // ====================================================
        if (count($subs) == 1) {
            $subscription = $subs[0];
            $shouldRenew = true;   // 自動更新する
        }

        // ====================================================
        //  CASE 2 ————「2件ある」→ 整理のみ行い、自動更新しない
        // ====================================================
        else if (count($subs) == 2) {

            foreach ($subs as $s) {
                if ($s['status_id'] == 1) {
                    $deleteSub = $s;
                } else {
                    $keepSub = $s;
                }
            }

            // status = 1 のレコードを削除
            $stmt = $db->prepare("DELETE FROM Subscription WHERE subscription_id = ?");
            $stmt->execute([$deleteSub['subscription_id']]);

            // 残す購読のステータスを更新
            if ($keepSub['status_id'] == 5) {
                $finalStatus = 1; // 1,5 → 5 を残して → 1 に変更
            } else if ($keepSub['status_id'] == 6) {
                $finalStatus = 2; // 1,6 → 6 を残して → 2 に変更
            } else {
                throw new Exception("Unexpected status combination");
            }

            $stmt = $db->prepare("UPDATE Subscription SET status_id = ? WHERE subscription_id = ?");
            $stmt->execute([$finalStatus, $keepSub['subscription_id']]);

            // 整理後の購読データに更新
            $subscription = $keepSub;
            $subscription['status_id'] = $finalStatus;

            // ⭐★ 重要：2件の場合 **自動更新は行わない**
            $db->commit();
            return false;  // 整理のみ、自動更新なし
        }

        // ====================================================
        //   ⭐ count=1 の場合のみここから自動更新処理を実行
        // ====================================================

        $today = new DateTime();
        $expirationDate = new DateTime($subscription['end_date']);
        $startDate = new DateTime($subscription['start_date']);
        $days = $startDate->diff($expirationDate)->days + 1;

        // 有効期限前 → 自動更新しない
        if ($expirationDate >= $today) {
            return false;
        }


        // 商品データを取得
        $stmt = $db->prepare("
            SELECT p.product_id, p.name, p.price
            FROM SubscriptionCustoms c
            LEFT JOIN Products p ON c.product_id = p.product_id
            WHERE c.subscription_id = ?
        ");
        $stmt->execute([$subscription['subscription_id']]);
        $customs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($customs)) {
            $db->rollBack();
            return false;
        }

        $totalAmount = array_sum(array_column($customs, 'price'));

        // 支払い情報を取得
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

        // 注文を作成
        $stmt = $db->prepare("
            INSERT INTO Orders (user_id, total_amount, status)
            VALUES (?, ?, 'paid')
        ");
        $stmt->execute([$userId, $totalAmount]);
        $orderId = $db->lastInsertId();

        // 支払い記録を作成
        $stmt = $db->prepare("
            INSERT INTO Payments (order_id, amount, payment_method, status)
            VALUES (?, ?, ?, 'success')
        ");
        $stmt->execute([
            $orderId,
            $totalAmount,
            $payment['card_brand'] . ' ' . $payment['masked_card_number']
        ]);

        // Order_Items を作成
        $stmt = $db->prepare("
            INSERT INTO Order_Items (order_id, product_name, price, quantity)
            VALUES (?, ?, ?, 1)
        ");
        foreach ($customs as $custom) {
            $stmt->execute([
                $orderId,
                $custom['name'],
                $custom['price']
            ]);
        }

        // サブスクリプション期間を延長
        $newExpirationDate = (clone $expirationDate)->modify("+{$days} days")->format('Y-m-d');
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

        $db->commit();
        return true; // 自動更新成功

    } catch (Exception $e) {
        $db->rollBack();
        error_log("Auto Renewal Error: " . $e->getMessage());
        return false;
    }
}
?>