document.getElementById('registerForm').addEventListener('submit', async function (event) {
    event.preventDefault();

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const employee_id =["A0110110","A0110120","A0110130","A0110140","A0110150","A0110160","A0110170","A0110180","A0110190"];
    const empIdInput = document.getElementById('employee_id').value.trim();
    const employeeIdError = document.getElementById('employee_idError');
    emailError.style.display = 'none';
    passwordError.style.display = 'none';
    employeeIdError.style.display = 'none';

    // mail check
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i;
    if (!email.match(emailPattern)) {
        emailError.style.display = 'block';
        return;
    }
    // employee ID check
    if (!employee_id.includes(empIdInput)) {
        employeeIdError.style.display = 'block';
        return;
    }

    // password match check
    if (password !== confirmPassword) {
        passwordError.style.display = 'block';
        return;
    }

    // Send registration data to the server
    try {
        const formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('password', password);
        const response = await fetch('./component/api/add_user.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            alert("登録が完了しました。ログインページにリダイレクトします。");
            window.location.href = 'login.php';
        } else {
            alert('登録に失敗しました。もう一度お試しください。');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('登録に失敗しました.');
    }


});
