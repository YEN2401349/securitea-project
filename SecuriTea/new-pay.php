<?php
session_start();
require '../common/DBconnect.php';
$stmt = $db->prepare("SELECT card_brand,masked_card_number,payment_token FROM Profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['customer']['user_id']]);
$card = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<?php if (isset($card["payment_token"])): ?>
    <form id="postForm" action="process_payment.php" method="post">
        <input type="radio" id="credit-card" name="payment-method" value="credit_card" checked>
        <input type="hidden" name="use-saved-card" value="1">
    </form>
    <script>
        document.getElementById("postForm").submit();
    </script>';
<?php else: ?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>お支払い方法の選択</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="css/new-pay.css">
    </head>

    <body>

        <div class="payment-container">
            <h2>お支払い方法の選択</h2>
            <p>ご希望のお支払い方法を選択してください。</p>

            <form id="payment-form" action="process_payment.php" method="post">

                <div class="payment-options">

                    <div class="payment-option">
                        <input type="radio" id="credit-card" name="payment-method" value="credit_card" checked>
                        <label for="credit-card">
                            <i class="fas fa-credit-card"></i>
                            <span>クレジットカード</span>
                        </label>
                    </div>

                    <div id="credit-card-details" class="payment-details active">
                        <div class="form-group card-icons">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fab fa-cc-amex"></i>
                            <i class="fab fa-cc-jcb"></i>
                        </div>
                        <div class="form-group">
                            <label for="card-number">カード番号</label>
                            <input type="text" id="card-number" name="card-number" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="form-group">
                            <label for="card-holder">カード名義人</label>
                            <input type="text" id="card-holder" placeholder="TARO YAMADA">
                        </div>
                        <div class="form-row">
                            <div class="form-group-half">
                                <label for="card-expiry">有効期限</label>
                                <input type="text" id="card-expiry" placeholder="MM / YY">
                            </div>
                            <div class="form-group-half">
                                <label for="card-cvc">セキュリティコード</label>
                                <input type="text" id="card-cvc" placeholder="123">
                            </div>
                        </div>
                    </div>

                    <div class="payment-option">
                        <input type="radio" id="paypal" name="payment-method" value="paypal">
                        <label for="paypal">
                            <i class="fab fa-paypal"></i>
                            <span>PayPal</span>
                        </label>
                    </div>

                    <div id="paypal-details" class="payment-details">
                        <p>PayPalアカウントでお支払いいただけます。「支払いを確定する」ボタンを押すと、PayPalの公式サイトに移動します。</p>
                        <div class="paypal-button-placeholder">
                            PayPalボタン
                        </div>
                    </div>

                    <div class="payment-option">
                        <input type="radio" id="bank-transfer" name="payment-method" value="bank_transfer">
                        <label for="bank-transfer">
                            <i class="fas fa-university"></i>
                            <span>銀行引き落とし</span>
                        </label>
                    </div>

                    <div id="bank-transfer-details" class="payment-details">
                        <p>「支払いを確定する」ボタンを押した後、口座情報の登録手続きに進みます。ご利用可能な金融機関をご確認の上、お進みください。</p>
                    </div>

                </div>

                <div class="form-actions">

                    <a href="cart.php" class="submit-btn secondary-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>カートに戻る</span>
                    </a>
                    <button type="submit" class="submit-btn">支払いを確定する</button>
                </div>

            </form>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const paymentOptions = document.querySelectorAll('.payment-option');
                const paymentDetails = document.querySelectorAll('.payment-details');

                const cardNumberInput = document.getElementById('card-number');
                const cardHolderInput = document.getElementById('card-holder');
                const cardExpiryInput = document.getElementById('card-expiry');
                const cardCvcInput = document.getElementById('card-cvc');
                const creditCardInputs = [cardNumberInput, cardHolderInput, cardExpiryInput, cardCvcInput];

                paymentOptions.forEach(option => {
                    const radio = option.querySelector('input[name="payment-method"]');
                    radio.addEventListener('change', function () {
                        paymentDetails.forEach(detail => {
                            detail.classList.remove('active');
                        });
                        const targetId = this.id + '-details';
                        const targetDetail = document.getElementById(targetId);
                        if (targetDetail) {
                            targetDetail.classList.add('active');
                        }
                        if (this.value === 'credit') {
                            creditCardInputs.forEach(input => {
                                input.required = true;
                            });
                        } else {
                            creditCardInputs.forEach(input => {
                                input.required = false;
                            });
                        }
                    });
                });

                creditCardInputs.forEach(input => {
                    input.required = true;
                });
            }); 
        </script>
    </body>

    </html>
<?php endif; ?>