document.addEventListener('DOMContentLoaded', function () {
    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentDetails = document.querySelectorAll('.payment-details');

    const cardNumberInput = document.getElementById('card-number');
    const cardHolderInput = document.getElementById('card-holder');
    const cardExpiryInput = document.getElementById('card-expiry');
    const cardCvcInput = document.getElementById('card-cvc');
    const modifyPay = document.getElementById('modify-pay');
    const creditCardInputs = [cardNumberInput, cardHolderInput, cardExpiryInput, cardCvcInput];

    creditCardInputs.forEach(input => input.required = true);

    paymentOptions.forEach(option => {
        const radio = option.querySelector('input[name="payment-method"]');
        radio.addEventListener('change', function () {
            paymentDetails.forEach(detail => detail.classList.remove('active'));
            const targetId = this.id + '-details';
            const targetDetail = document.getElementById(targetId);
            if (targetDetail) targetDetail.classList.add('active');

            if (this.value === 'credit_card') {
                creditCardInputs.forEach(input => input.required = true);
            } else {
                creditCardInputs.forEach(input => input.required = false);
            }
        });
    });

    document.getElementById('payment-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const selectedMethod = document.querySelector('input[name="payment-method"]:checked').value;
        const token = generateToken();
        if (selectedMethod === 'credit_card') {
            const selectedCardType = document.querySelector('input[name="card-type"]:checked').value;
            const data = {
                user_id: document.getElementById('user-id').textContent,
                card_brand: selectedCardType,
                masked_card_number: cardNumberInput.value.slice(-4),
                payment_token: token
            };
            const result = savePackageToServer(data);
            result.then(success => {
                alert('支払い情報が保存されました。');
                document.getElementById('payment-card').textContent =  data.card_brand + ' **** **** **** ' + data.masked_card_number ;
                modifyPay.style.display = 'none';
            }).catch(error => {
                alert('エラーが発生しました: ' + error.message);
            });
        }
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
    const url = "component/api/update_modify-pay.php";

    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    const json = await res.json();
    if (!json.success) throw new Error(json.error);
    return json.success;
}

document.getElementById('btn-primary').addEventListener('click', function () {
    document.getElementById('modify-pay').style.display = 'flex';
});