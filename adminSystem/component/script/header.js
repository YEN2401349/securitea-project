const admin_name = document.getElementById("admin_name");
const token = localStorage.getItem('token');
const logout_btn = document.getElementById("logout_btn");

logout_btn.addEventListener("click", () => {
    localStorage.clear();
    window.location.href = './login.php';
});
if (token) {
    const decoded = jwt_decode(token);
    admin_name.textContent = "管理者:" + decoded.full_name;
} else {
    console.log('No token found');
}