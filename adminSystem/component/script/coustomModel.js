// ------------------------------
// Custom Plan Management
// ------------------------------

// LocalStorage key
const customStorageKey = 'customSampleData';

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

fetch('component/get_users.php')
    .then(res => res.json())
    .then(data => {
        // 存到 localStorage
        localStorage.setItem('User', JSON.stringify(data));

        
        if (data.length > 0) {
            console.log('第一個商品名稱:', data[0].name);
        }
    })
    .catch(err => console.error('抓取產品資料失敗:', err));
// Sample data
const customSampleData = localStorage.getItem('User') ? JSON.parse(localStorage.getItem('User')) : [];



// ------------------------------
// Initialization
// ------------------------------
function customLoad() {
    const raw = localStorage.getItem(customStorageKey);
    if (raw) {
        customItems = JSON.parse(raw);
    } else {
        customItems = customSampleData;
        localStorage.setItem(customStorageKey, JSON.stringify(customItems));
    }
    renderCustomTable();
}

// ------------------------------
// Save to LocalStorage
// ------------------------------
function save() {
    localStorage.setItem(customStorageKey, JSON.stringify(customItems));
}

// ------------------------------
// Open / Close Modal
// ------------------------------
function openCustomModel(edit = false, item = null) {
    customModel.style.display = "flex";
    document.getElementById("modalTitle").textContent = edit ? "編集" : "追加";

    // Populate form fields
    customForm.name.value = item?.name || "";
    customForm.month.value = item?.month || "";
    customForm.year.value = item?.year || "";
    customForm.permanent.value = item?.permanent || "";
    customForm.description.value = item?.description || "";
}

function closeCustomModel() {
    customModel.style.display = "none";
    customEditingId = null;
}

// ------------------------------
// Form Submission (Add / Edit)
// ------------------------------
customForm.onsubmit = e => {
    e.preventDefault();

    const data = {
        name: customForm.name.value.trim(),
        month: customForm.month.value.trim(),
        year: customForm.year.value.trim(),
        permanent: customForm.permanent.value.trim(),
        description: customForm.description.value.trim()
    };

    if (customEditingId) {
        // Edit existing item
        const item = customItems.find(i => i.id === customEditingId);
        if (item) Object.assign(item, data);
    } else {
        // Add new item
        customItems.push({ id: "u_" + Math.random().toString(36).slice(2, 9), ...data });
    }

    save();
    closeCustomModel();
    renderCustomTable();
}

// ------------------------------
// Edit / Delete Functions
// ------------------------------
function editCustoms(id) {
    const item = customItems.find(i => i.id === id);
    if (!item) return;
    customEditingId = id;
    openCustomModel(true, item);
}

function deleteCustoms(id) {
    if (!confirm("Are you sure you want to delete this item?")) return;
    const item = customItems.find(i => i.id === id);
    if (item) item._deleted = true;
    save();
    renderCustomTable();
}

// ------------------------------
// Table Rendering & Pagination
// ------------------------------
function queryCustomData() {
    const q = (customSearch?.value || '').trim().toLowerCase();
    let list = customItems.filter(i => !i._deleted);

    if (q) {
        list = list.filter(i => (i.name + i.month + i.year + i.permanent).toLowerCase().includes(q));
    }

    const total = list.length;
    const start = (customPage - 1) * customPageSize;
    return { total, list: list.slice(start, start + customPageSize) };
}

function renderCustomTable() {
    const { total, list } = queryCustomData();

    // Render table rows
    customTableBody.innerHTML = list.map((i, idx) => `
        <tr>
            <td>${(customPage - 1) * customPageSize + idx + 1}</td>
            <td>${i.name}</td>
            <td>¥${i.month}</td>
            <td>¥${i.year}</td>
            <td>¥${i.permanent}</td>
            <td>
                <button class="customEditBtn" data-id="${i.id}">編集</button>
                <button class="customDeleteBtn" data-id="${i.id}">削除</button>
            </td>
        </tr>
    `).join('');

    // Bind buttons
    document.querySelectorAll('.customEditBtn').forEach(b => b.onclick = e => editCustoms(e.target.dataset.id));
    document.querySelectorAll('.customDeleteBtn').forEach(b => b.onclick = e => deleteCustoms(e.target.dataset.id));

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

// ------------------------------
// Initial Load
// ------------------------------
customLoad();
