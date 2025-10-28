// ------------------------------
// Custom Plan Management
// ------------------------------

// State variables
let customItems = [];
let customEditingId = null;
let customPage = 1;
let customPageSize = 5;

// DOM elements
const customTableBody = document.getElementById('customTableBody');
const customSearch = document.getElementById('customSearch');
const customPageSizeSelect = document.getElementById('customPageSize');
const customPagination = document.getElementById('customPagination');
const customCurrentPageEl = document.getElementById('customCurrentPage');
const customTableWrapper = document.getElementById('customTableWrapper');

const customModel = document.getElementById('customModel');
const customForm = document.getElementById('customForm');
const addCustomBtn = document.getElementById('addCustomBtn');
const cancelBtn = document.getElementById('cancelBtn');

const plan_type = document.getElementById('plan_type');
const duration_months = document.getElementById('duration_months');

// ------------------------------
// Fetch data from server
// ------------------------------
async function reloadFromServer() {
    try {
        const res = await fetch('component/api/get_coustom.php');
        const json = await res.json();
        if (!json.success) throw new Error(json.error);

        customItems = json.data.map(p => ({
            id: p.product_id || p.id,
            name: p.name,
            price: p.price,
            plan_type: p.plan_type,
            billing_cycle: p.billing_cycle,
            duration_months: p.duration_months,
            description: p.description
        }));

        localStorage.setItem('products', JSON.stringify(customItems));
        renderCustomTable();
    } catch (err) {
        console.error('Error:', err);
    }
}

// ------------------------------
// Save to server (Add / Edit)
// ------------------------------
async function saveToServer(data, isEdit = false) {
    const url = isEdit
        ? 'component/api/update_custom.php'
        : 'component/api/add_custom.php';

    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    const json = await res.json();
    if (!json.success) throw new Error(json.error);
    return json.data;
}

// ------------------------------
// Initialization
// ------------------------------
function customLoad() {
    const raw = localStorage.getItem('products');
    if (raw) customItems = JSON.parse(raw);
    renderCustomTable();
}

// ------------------------------
// Update duration_months options
// ------------------------------
function updateDurationOptions() {
    const options = duration_months.options;
    for (let i = 0; i < options.length; i++) options[i].disabled = false;

    switch (plan_type.value) {
        case 'lifetime':
            for (let o of options)
                if (['1', '6', '12'].includes(o.value)) o.disabled = true;
            duration_months.value = '999';
            break;
        case 'yearly':
            for (let o of options)
                if (['1', '6', '999'].includes(o.value)) o.disabled = true;
            duration_months.value = '12';
            break;
        default: // monthly
            for (let o of options)
                if (['12', '999'].includes(o.value)) o.disabled = true;
            for (let o of options)
                if (!o.disabled) {
                    duration_months.value = o.value;
                    break;
                }
            break;
    }
}

// ------------------------------
// Open / Close Modal
// ------------------------------
function openCustomModel(edit = false, item = null) {
    customModel.style.display = "flex";
    document.getElementById("modalTitle").textContent = edit ? "編集" : "追加";
    console.log(item?.billing_cycle);
    customForm.name.value = item?.name || "";
    customForm.price.value = item?.price || "";
    customForm.plan_type.value = item?.billing_cycle || "monthly";
    customForm.duration_months.value = item?.duration_months || "1";
    customForm.description.value = item?.description || "";

    updateDurationOptions();
}

function closeCustomModel() {
    customModel.style.display = "none";
    customEditingId = null;
}

// ------------------------------
// Form Submission (Add / Edit)
// ------------------------------
customForm.onsubmit = async e => {
    e.preventDefault();

    const data = {
        name: customForm.name.value.trim(),
        price: customForm.price.value.trim(),
        billing_cycle: customForm.plan_type.value.trim(),
        duration_months: customForm.duration_months.value.trim(),
        description: customForm.description.value.trim()
    };

    try {
        if (customEditingId) {
            await saveToServer({ id: customEditingId, ...data }, true);
        } else {
            await saveToServer(data, false);
        }

        closeCustomModel();
        await reloadFromServer(); 
    } catch (err) {
        alert("保存失敗：" + err.message);
    }
};

// ------------------------------
// Edit / Delete Functions
// ------------------------------
async function editCustoms(id) {
    const item = customItems.find(i => i.id == id);
    if (!item) return;
    customEditingId = id;
    openCustomModel(true, item);
}

async function deleteCustoms(id) {
    if (!confirm("削除する？")) return;
    try {
        const res = await fetch('component/api/delete_custom.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.error);
        await reloadFromServer();
    } catch (err) {
        alert("削除失敗：" + err.message);
    }
}

// ------------------------------
// Table Rendering & Pagination
// ------------------------------
function queryCustomData() {
    const q = (customSearch?.value || '').trim().toLowerCase();
    let list = customItems;
    if (q) list = list.filter(i => i.name.toLowerCase().includes(q));

    const total = list.length;
    const start = (customPage - 1) * customPageSize;
    return { total, list: list.slice(start, start + customPageSize) };
}

function renderCustomTable() {
    const { total, list } = queryCustomData();

    customTableBody.innerHTML = list.map(i => `
        <tr>
            <td>${i.id}</td>
            <td>${i.name}</td>
            <td>¥${Number(i.price)}</td>
            <td>${i.plan_type}</td>
            <td>${i.description}</td>
            <td>
                <button class="customEditBtn" data-product-id="${i.id}">編集</button>
                <button class="customDeleteBtn" data-product-id="${i.id}">削除</button>
            </td>
        </tr>
    `).join('');

    document.querySelectorAll('.customEditBtn').forEach(b =>
        b.onclick = e => editCustoms(e.target.dataset.productId)
    );
    document.querySelectorAll('.customDeleteBtn').forEach(b =>
        b.onclick = e => deleteCustoms(e.target.dataset.productId)
    );

    renderCustomPagination(total);
    updateTableScroll();
}

// ------------------------------
// Pagination Rendering
// ------------------------------
function renderCustomPagination(total) {
    const totalPages = Math.ceil(total / customPageSize);
    customPagination.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = i === customPage ? 'bg-blue text-white px-2 py-1 rounded' : 'border px-2 py-1 rounded';
        btn.onclick = () => { customPage = i; renderCustomTable(); };
        customPagination.appendChild(btn);
    }
    customCurrentPageEl.textContent = customPage;
}

// ------------------------------
// Enable table scroll if page size large
// ------------------------------
function updateTableScroll() {
    if (customPageSize >= 10) {
        customTableWrapper.classList.add('scrollable');
    } else {
        customTableWrapper.classList.remove('scrollable');
    }
}

// ------------------------------
// Event Listeners
// ------------------------------
addCustomBtn.onclick = () => openCustomModel();
cancelBtn.onclick = () => closeCustomModel();
customSearch.addEventListener('input', () => { customPage = 1; renderCustomTable(); });
customPageSizeSelect.addEventListener('change', () => {
    customPageSize = parseInt(customPageSizeSelect.value);
    customPage = 1;
    renderCustomTable();
});
plan_type.addEventListener('change', updateDurationOptions);

// ------------------------------
// Initial Load
// ------------------------------
reloadFromServer();
updateDurationOptions();
