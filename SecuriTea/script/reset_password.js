document.addEventListener("DOMContentLoaded", () => {
    const resetForm = document.getElementById("resetForm");
    const emailInput = document.getElementById("email");
    const newPasswordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("password-confirm");
    const passwordError = document.getElementById("passwordError");
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get("token");
    const email = urlParams.get("email");
    passwordError.style.display = "none";
    emailInput.value = urlParams.get("email") || "";
    resetForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        if (newPasswordInput.value !== confirmPasswordInput.value) {
            passwordError.style.display = "block";
            return;
        } else {
            passwordError.style.display = "none";
        }


        const data = {
            email: emailInput.value,
            newPassword: newPasswordInput.value,
            token: token
        };

        try {
            const response = await fetch("./component/api/reset_password.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                alert("パスワードが再設定されました。ログインしてください。");
                window.location.href = "./login.php";
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error("通信エラー:", error);
            alert("通信エラーが発生しました。もう一度お試しください。");
        }
    });
});
