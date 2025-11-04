import { SignJWT } from 'https://cdn.skypack.dev/jose';

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
        console.log(data.data);
        if (data.data) {
            const user = data.data;

            const secret = new TextEncoder().encode('mySuperSecretKey'); 
            const token = await new SignJWT({ email: user.user_email, full_name: user.full_name ,role: user.role}) 
                .setProtectedHeader({ alg: 'HS256' })
                .setIssuedAt() 
                .setExpirationTime('1h') 
                .sign(secret); 


            localStorage.setItem('token', token);


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