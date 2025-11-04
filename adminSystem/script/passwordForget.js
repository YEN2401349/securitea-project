document.getElementById('forgotForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value.trim();
    const emailError = document.getElementById('emailError');
    emailError.style.display = 'none';

    if (email === "") {
        emailError.textContent = "メールアドレスを入力してください。";
        emailError.style.display = 'block';
        return;
    }




    fetch('./component/api/send_reset_link.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.href = './login.php';
            } else {
                if (data.message=="該当する管理者が見つかりません。") {
                    emailError.style.display = 'block';
                }
            }
        });

});
