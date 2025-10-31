document.getElementById('loginForm').addEventListener('submit', async function (event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const emailError = document.getElementById('emailError');

    emailError.style.display = 'none';

    try {
        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);

        const response = await fetch('component/api/check_login.php', {
            method: 'POST',
            body: formData,
        });


        const data = await response.json();
        console.log(data);
        if (data.data) {
            window.location.href = 'dashboard.php';
        } else {

            emailError.style.display = 'block';
        }
    } catch (error) {
        console.error('ログインエラー:', error);
        emailError.textContent = 'サーバーエラーが発生しました。';
        emailError.style.display = 'block';
    }
});
