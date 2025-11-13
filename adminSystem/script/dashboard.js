const yearSelect = document.getElementById('yearSelect');
const userCountEl = document.getElementById('userCount');
const totalPriceEl = document.getElementById('totalPrice');
const totalMailEl = document.getElementById('totalMail');

if (!localStorage.getItem('token')) {
    window.location.href = 'login.php';
}

window.onpageshow = function (event) {
    if (event.persisted) window.location.reload();
};

async function reloadDashboard() {
    try {
        const res = await fetch('component/api/get_dashboard.php');
        const json = await res.json();
        if (!json.success) throw new Error(json.error);

        userCountEl.textContent = json.userCount + "人";
        totalPriceEl.textContent = parseInt(json.totalAmount, 10).toLocaleString() + "円";
        totalMailEl.textContent = json.mailCount + "件";
    } catch (err) {
        console.error(err);
        userCountEl.textContent = "0人";
        totalPriceEl.textContent = "0円";
        totalMailEl.textContent = "0件";
    }
}

const ctx = document.getElementById('salesChart').getContext('2d');
const labels = ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"];
const chart = new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ label: '売上額 (円)', data: Array(12).fill(0), borderColor: "#4e79ff", backgroundColor: "rgba(78,121,255,0.2)", fill: true, tension: 0.3, pointRadius: 5, pointBackgroundColor: "#4e79ff" }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, title: { display: true, text: "売上額 (円)" } }, x: { title: { display: true, text: "月" } } }, plugins: { tooltip: { callbacks: { label: (ctx) => `¥${ctx.formattedValue}` } } } }
});

async function fetchSales(year) {
    const res = await fetch(`./component/api/get_Graph.php?year=${year}`);
    const data = await res.json();
    chart.data.datasets[0].data = data.sales;
    chart.update();
}

document.addEventListener('DOMContentLoaded', () => {
    reloadDashboard();
    console.log("1");
    const startYear = 2023;
    const currentYear = new Date().getFullYear();

    for (let y = startYear; y <= currentYear; y++) {
        const option = document.createElement('option');
        option.value = y;
        option.textContent = y + '年';
        if (y === currentYear) option.selected = true;
        yearSelect.appendChild(option);
    }

    fetchSales(currentYear);

    yearSelect.addEventListener('change', () => {
        fetchSales(yearSelect.value);
    });
});
