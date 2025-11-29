<link rel="stylesheet" href="./component/css/modify-pay.css">
<div id="modify-pay" class="modify-pay">
    <div class="payment-container">
        <h2>お支払い方法の選択</h2>
        <p>ご希望のお支払い方法を選択してください。</p>

        <form id="payment-form" method="post">
            <div class="payment-option">
                <input type="radio" id="credit-card" name="payment-method" value="credit_card" checked>
                <label for="credit-card"><i class="fas fa-credit-card"></i><span>クレジットカード</span></label>
            </div>
            <div id="credit-card-details" class="payment-details active">
                <div class="form-group card-icons">
                    <label>
                        <input type="radio" name="card-type" value="VISA" checked>
                        <i class="fab fa-cc-visa"></i>
                    </label>
                    <label>
                        <input type="radio" name="card-type" value="MASTERCARD">
                        <i class="fab fa-cc-mastercard"></i>
                    </label>
                    <label>
                        <input type="radio" name="card-type" value="AMEX">
                        <i class="fab fa-cc-amex"></i>
                    </label>
                    <label>
                        <input type="radio" name="card-type" value="JCB">
                        <i class="fab fa-cc-jcb"></i>
                    </label>
                </div>

                <div class="form-group">
                    <label for="card-number">カード番号</label>
                    <input type="text" id="card-number" placeholder="1234 5678 9012 3456">
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
                <label for="paypal"><i class="fab fa-paypal"></i><span>PayPal</span></label>
            </div>
            <div id="paypal-details" class="payment-details">
                <p>PayPalアカウントでお支払いいただけます。「支払いを確定する」ボタンを押すと、PayPalの公式サイトに移動します。</p>
                <div class="paypal-button-placeholder">PayPalボタン</div>
            </div>

            <div class="payment-option">
                <input type="radio" id="bank-transfer" name="payment-method" value="bank_transfer">
                <label for="bank-transfer"><i class="fas fa-university"></i><span>銀行引き落とし</span></label>
            </div>
            <div id="bank-transfer-details" class="payment-details">
                <p>「支払いを確定する」ボタンを押した後、口座情報の登録手続きに進みます。ご利用可能な金融機関をご確認の上、お進みください。</p>
            </div>

            <div class="form-actions">
                <button type="button" class="submit-btn secondary-btn" id="cancel-btn"><i class="fas fa-arrow-left"></i> 戻る</button>
                <button type="submit" class="submit-btn">支払いを確定する</button>
            </div>
        </form>
    </div>
</div>
<script src="./component/js/modify-pay.js"></script>