if (!localStorage.getItem('token')) {
    window.location.href = 'login.php';
}

window.onload = () => window.history.forward();
window.onpageshow = event => event.persisted && window.location.reload();

let saleItems = [];
let salesData = [];

const startDateInput = document.getElementById('startDate');
const endDateInput = document.getElementById('endDate');
const keywordInput = document.getElementById('keyword');
const searchBtn = document.getElementById('searchBtn');
const resetBtn = document.getElementById('resetBtn');
const salesTable = document.getElementById('salesTable');
const totalAmountEl = document.getElementById('totalAmount');
const categorySelect = document.getElementById('category');
const tableWrapper = document.getElementById('tableWrapper');

async function reloadFromServer() {
    try {
        const res = await fetch('component/api/get_sales.php');
        const json = await res.json();
        if (!json.success) throw new Error(json.error);

        const dataArray = Array.isArray(json.data) ? json.data : [];
        saleItems = dataArray.map(p => ({
            order_date: p.order_date,
            product: p.name,
            quantity: parseInt(p.total_quantity),
            price: parseInt(p.price),
            category_id: p.category_id
        }));

        salesData = [...saleItems];
        localStorage.setItem('sales', JSON.stringify(saleItems));
        renderTable(saleItems);
    } catch (err) {
        console.error('Error:', err);
        salesTable.innerHTML = `<tr><td colspan="5" class="has-text-centered">データが取得できませんでした</td></tr>`;
        totalAmountEl.textContent = '¥0';
    }
}

function renderTable(data) {
    salesTable.querySelector('tbody').innerHTML = '';
    console.log(data.length );
    if (data.length > 15) {
        tableWrapper.style.maxHeight = '500px';
        tableWrapper.style.overflowY = 'auto';
    } else {
        tableWrapper.style.maxHeight = 'none';
        tableWrapper.style.overflowY = 'visible';
    }

    if (data.length === 0) {
        salesTable.querySelector('tbody').innerHTML = `<tr><td colspan="5" class="has-text-centered">データがありません</td></tr>`;
        totalAmountEl.textContent = '¥0';
        return;
    }

    let total = 0;
    data.forEach(s => {
        const subtotal = s.price * s.quantity;
        total += subtotal;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${s.order_date}</td>
            <td>${s.product}</td>
            <td class="is-right">${s.quantity}</td>
            <td class="is-right">¥${s.price.toLocaleString()}</td>
            <td class="is-right">¥${subtotal.toLocaleString()}</td>
        `;
        salesTable.querySelector('tbody').appendChild(tr);
    });

    totalAmountEl.textContent = '¥' + total.toLocaleString();
}

function filterSales() {
    const start = startDateInput.value;
    const end = endDateInput.value;
    const keyword = keywordInput.value.trim();
    const categoryId = categorySelect.value;

    const filtered = salesData.filter(s => {
        const matchDate =
            (!start || s.order_date >= start) &&
            (!end || s.order_date <= end + 'T23:59:59');
        const matchKeyword = !keyword || s.product.includes(keyword);
        const matchCategory = categoryId == 0 || s.category_id == categoryId;
        return matchDate && matchKeyword && matchCategory;
    });

    renderTable(filtered);
}

searchBtn.addEventListener('click', filterSales);

resetBtn.addEventListener('click', () => {
    startDateInput.value = '';
    endDateInput.value = '';
    keywordInput.value = '';
    categorySelect.value = '0';
    renderTable(salesData);
});


reloadFromServer();
