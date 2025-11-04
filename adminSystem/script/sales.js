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