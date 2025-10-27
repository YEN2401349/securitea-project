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
fetch('component/get_coustom.php')
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
const customSampleData = [
    { id: 'u_1', name: 'ベーシックプラン', month: '300', year: '3300', permanent: '9300' },
    { id: 'u_2', name: 'スタンダードプラン', month: '500', year: '5000', permanent: '12000' },
    { id: 'u_3', name: 'プレミアムプラン', month: '800', year: '8000', permanent: '18000' },
    { id: 'u_4', name: 'ビジネスプラン', month: '1000', year: '10000', permanent: '22000' },
    { id: 'u_5', name: 'エンタープライズプラン', month: '1500', year: '15000', permanent: '30000' },
    { id: 'u_6', name: 'プロフェッショナルプラン', month: '1200', year: '12000', permanent: '26000' },
    { id: 'u_7', name: 'ライトプラン', month: '200', year: '2000', permanent: '6000' },
    { id: 'u_8', name: 'エコノミープラン', month: '350', year: '3500', permanent: '9000' },
    { id: 'u_9', name: 'アドバンスプラン', month: '900', year: '9000', permanent: '21000' },
    { id: 'u_10', name: 'プラスプラン', month: '600', year: '6000', permanent: '14000' },
    { id: 'u_11', name: 'スマートプラン', month: '450', year: '4500', permanent: '11000' },
    { id: 'u_12', name: 'コンパクトプラン', month: '250', year: '2800', permanent: '7500' },
    { id: 'u_13', name: 'エキスパートプラン', month: '1100', year: '11000', permanent: '24000' },
    { id: 'u_14', name: 'アルティメットプラン', month: '1800', year: '18000', permanent: '40000' },
    { id: 'u_15', name: 'ベーシック年間プラン', month: '320', year: '3500', permanent: '9500' },
    { id: 'u_16', name: 'スタートアッププラン', month: '700', year: '7200', permanent: '17000' },
    { id: 'u_17', name: 'リミテッドプラン', month: '400', year: '4200', permanent: '10000' },
    { id: 'u_18', name: 'シンプルプラン', month: '280', year: '2900', permanent: '7000' },
    { id: 'u_19', name: 'デラックスプラン', month: '950', year: '9500', permanent: '20500' },
    { id: 'u_20', name: 'プレミアム年間プラン', month: '850', year: '8800', permanent: '19000' },
];



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
