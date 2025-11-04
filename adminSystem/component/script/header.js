const admin_name = document.getElementById("admin_name");
const token = localStorage.getItem('token');

if (token) {
    const decoded = jwt_decode(token);
    admin_name.textContent = "管理者:" + decoded.full_name;
} else {
    console.log('No token found');
}