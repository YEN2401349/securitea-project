document.addEventListener('DOMContentLoaded', function () {
    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentDetails = document.querySelectorAll('.payment-details');

    const cardNumberInput = document.getElementById('card-number');
    const cardHolderInput = document.getElementById('card-holder');
    const cardExpiryInput = document.getElementById('card-expiry');
    const cardCvcInput = document.getElementById('card-cvc');
    const modifyPay = document.getElementById('modify-pay');
    const creditCardInputs = [cardNumberInput, cardHolderInput, cardExpiryInput, cardCvcInput];

    // 初期状態: クレジットカードが選ばれていれば必須にする
    const initialChecked = document.querySelector('input[name="payment-method"]:checked');
    if (initialChecked && initialChecked.value === 'credit_card') {
        creditCardInputs.forEach(input => input.required = true);
    } else {
        creditCardInputs.forEach(input => input.required = false);
    }

    // ラジオボタン切り替え時の表示・必須制御
    paymentOptions.forEach(option => {
        const radio = option.querySelector('input[name="payment-method"]');
        radio.addEventListener('change', function () {
            // 表示の切り替え
            paymentDetails.forEach(detail => detail.classList.remove('active'));
            const targetId = this.id + '-details';
            const targetDetail = document.getElementById(targetId);
            if (targetDetail) targetDetail.classList.add('active');

            // 必須属性の切り替え
            if (this.value === 'credit_card') {
                creditCardInputs.forEach(input => input.required = true);
            } else {
                creditCardInputs.forEach(input => input.required = false);
            }
        });
    });

    // フォーム送信時の処理
    document.getElementById('payment-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const selectedMethod = document.querySelector('input[name="payment-method"]:checked').value;
        const userIdElem = document.getElementById('user-id'); 
        
        // データの準備
        let data = {
            user_id: userIdElem ? userIdElem.textContent.trim() : '',
            payment_token: generateToken()
        };

        // 支払い方法ごとのデータ設定
        if (selectedMethod === 'credit_card') {
            const selectedCardType = document.querySelector('input[name="card-type"]:checked').value;
            // カード番号の下4桁を取得
            const cardNumVal = cardNumberInput.value.replace(/[^0-9]/g, ''); 
            data.card_brand = selectedCardType;
            data.masked_card_number = cardNumVal.slice(-4);
        } else if (selectedMethod === 'paypal') {
            data.card_brand = '';
            data.masked_card_number = 'PayPal';
        } else if (selectedMethod === 'bank_transfer') {
            data.card_brand = '';
            data.masked_card_number = '銀行引き落とし';
        }

        // サーバーへ送信
        savePackageToServer(data).then(success => {
            alert('支払い情報が変更されました。');

            // 成功したら画面（account.php）の表示を更新する
            const displayArea = document.getElementById('payment-card');
            if (displayArea) {
                if (selectedMethod === 'credit_card') {
                    displayArea.textContent = data.card_brand + ' **** **** **** ' + data.masked_card_number;
                } else {
                    displayArea.textContent = data.masked_card_number;
                }
            }
            
            modifyPay.style.display = 'none';
        }).catch(error => {
            alert('エラーが発生しました: ' + error.message);
            console.error(error);
        });
    });

    document.getElementById('cancel-btn').addEventListener('click', function () {
        modifyPay.style.display = 'none';
    });
});

function generateToken(length = 16) {
    const array = new Uint8Array(length);
    window.crypto.getRandomValues(array);
    return Array.from(array, b => b.toString(16).padStart(2, '0')).join('');
}

async function savePackageToServer(data) {
    // フォルダ構成に合わせてパスを確認してください
    const url = "component/api/update_modify-pay.php";

    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    // レスポンスが空やHTMLエラーでないかチェック
    if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
    }

    const json = await res.json();
    if (!json.success) throw new Error(json.error || '不明なエラー');
    return json.success;
}

const btnPrimary = document.getElementById('btn-primary');
if (btnPrimary) {
    btnPrimary.addEventListener('click', function () {
        document.getElementById('modify-pay').style.display = 'flex';
    });
}