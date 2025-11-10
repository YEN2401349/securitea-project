if (localStorage.getItem('token') === null) {
    window.location.href = 'login.php';
}

window.onpageshow = function (event) {
    if (event.persisted) {
        window.location.reload();
    }
};


const userCountEl = document.getElementById('userCount');
const totalPriceEl = document.getElementById('totalPrice');
const totalMailEl = document.getElementById('totalMail');

async function reloadDashboard() {
    try {
        const res = await fetch('component/api/get_dashboard.php');
        const json = await res.json();

        if (!json.success) throw new Error(json.error);

        userCountEl.textContent = json.userCount + "人";
        totalPriceEl.textContent = parseInt(json.totalAmount, 10).toLocaleString() + "円";
        totalMailEl.textContent = json.mailCount + "件";
    } catch (err) {
        console.error('Error fetching dashboard data:', err);
        userCountEl.textContent = "0人";
        totalPriceEl.textContent = "0円";
        totalMailEl.textContent = "0件";
    }
}

window.onload = reloadDashboard;
