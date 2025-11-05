if (localStorage.getItem('token') == null) {
    window.location.href = 'login.php'
}
window.onload = function () {
    window.history.forward();
};
window.onpageshow = function (event) {
    if (event.persisted) {
        window.location.reload();
    }
};
const userCountEl = document.getElementById('userCount');
async function reloadFromServer() {
    try {
        const res = await fetch('component/api/get_userCount.php');
        const json = await res.json();
        if (!json.success) throw new Error(json.error);

        const userCount = json.data.user_count;

        localStorage.setItem('userCount', JSON.stringify(userCount));
        render();
    } catch (err) {
        console.error('Error:', err);
    }
    userCountEl.textContent = localStorage.getItem('userCount')+"人";
}

reloadFromServer();