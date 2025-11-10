// ------------------------------
// Custom Option Management (Server-based)
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
const custom_preview = document.getElementById('custom_preview');
const customModalCancelBtn = document.getElementById('customModalCancelBtn'); // ✅ Matches HTML id

// ------------------------------
// Fetch data from server
// ------------------------------
async function reloadCustomFromServer() {
    try {
        const res = await fetch('component/api/get_custom.php');
        const json = await res.json();
        if (!json.success) throw new Error(json.error);

        customItems = json.data.map(p => ({
            id: p.product_id || p.id,
            custom_name: p.name,
            custom_price: p.price,
            custom_plan_type: p.plan_type,
            custom_billing_cycle: p.billing_cycle,
            custom_duration_months: p.duration_months,
            image_path: p.image_path,
            custom_description: p.description
        }));

        localStorage.setItem('customs', JSON.stringify(customItems));
        renderCustomTable();
    } catch (err) {
        console.error('Failed to fetch custom data:', err);
    }
}

// ------------------------------
// Add / Edit data (save to server)
// ------------------------------
async function saveCustomToServer(data, isEdit = false) {
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
    const raw = localStorage.getItem('customs');
    if (raw) customItems = JSON.parse(raw);
    renderCustomTable();
}

// ------------------------------
// Open / Close Modal
// ------------------------------
function openCustomModel(edit = false, item = null) {
    customModel.style.display = "flex";
    document.getElementById("customModalTitle").textContent = edit ? "編集" : "追加";

    customForm.custom_name.value = item?.custom_name || "";
    customForm.custom_price.value = item?.custom_price || "";
    customForm.custom_plan_type.value = item?.custom_billing_cycle || "monthly";
    customForm.custom_duration_months.value = item?.custom_duration_months || "1";
    customForm.custom_description.value = item?.custom_description || "";
    customForm.image.value = "";
    custom_preview.innerHTML = item?.image_path
        ? `<img src="${item.image_path}" alt="預覽" style="max-width:90px;">`
        : "";
    if (!edit) updateDurationCustomOptions();
}

function closeCustomModel() {
    customModel.style.display = "none";
    customEditingId = null;
}

// ------------------------------
// Update duration options based on custom_plan_type
// ------------------------------
function updateDurationCustomOptions() {
    const planTypeEl = document.getElementById("custom_plan_type");
    const durationEl = document.getElementById("custom_duration_months");

    const options = durationEl.options;
    for (let i = 0; i < options.length; i++) options[i].disabled = false;

    switch (planTypeEl.value) {
        case "lifetime":
            for (let o of options)
                if (["1", "6", "12"].includes(o.value)) o.disabled = true;
            durationEl.value = "999";
            break;
        case "yearly":
            for (let o of options)
                if (["1", "6", "999"].includes(o.value)) o.disabled = true;
            durationEl.value = "12";
            break;
        default: // monthly
            for (let o of options)
                if (["12", "999"].includes(o.value)) o.disabled = true;
            for (let o of options)
                if (!o.disabled) {
                    durationEl.value = o.value;
                    break;
                }
            break;
    }
}

// ------------------------------
// Form submission (Add / Edit)
// ------------------------------
customForm.onsubmit = async e => {
    e.preventDefault();

    const data = {
        custom_name: customForm.custom_name.value.trim(),
        custom_price: customForm.custom_price.value.trim(),
        custom_billing_cycle: customForm.custom_plan_type.value.trim(),
        custom_duration_months: customForm.duration_months.value.trim(),
        custom_image: customForm.image.files[0] ? await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = () => reject(new Error("画像の読み込みに失敗しました"));
            reader.readAsDataURL(customForm.image.files[0]);
        }) : null,
        custom_description: customForm.custom_description.value.trim()
    };

    try {
        if (customEditingId) {
            await saveCustomToServer({ id: customEditingId, ...data }, true);
        } else {
            await saveCustomToServer(data, false);
        }

        closeCustomModel();
        await reloadCustomFromServer(); // Reload updated data
    } catch (err) {
        alert("Save failed: " + err.message);
    }
};

// ------------------------------
// Edit / Delete
// ------------------------------
async function editCustom(id) {
    const item = customItems.find(i => i.id == id);
    if (!item) return;
    customEditingId = id;
    openCustomModel(true, item);
}

async function deleteCustom(id) {
    if (!confirm("削除する？")) return;
    try {
        const res = await fetch('component/api/delete_custom.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.error);
        await reloadCustomFromServer();
    } catch (err) {
        alert("Delete failed: " + err.message);
    }
}

// ------------------------------
// Table rendering & pagination
// ------------------------------
function queryCustomData() {
    const q = (customSearch?.value || '').trim().toLowerCase();
    let list = customItems;
    if (q) list = list.filter(i => i.custom_name.toLowerCase().includes(q));

    const total = list.length;
    const start = (customPage - 1) * customPageSize;
    return { total, list: list.slice(start, start + customPageSize) };
}

function renderCustomTable() {
    const { total, list } = queryCustomData();

    customTableBody.innerHTML = list.map(i => `
        <tr>
            <td>${i.id}</td>
            <td>${i.custom_name}</td>
            <td>¥${Number(i.custom_price)}</td>
            <td>${i.custom_plan_type}</td>
            <td>${i.custom_description}</td>
            <td>
                <button class="customEditBtn" data-custom-id="${i.id}">編集</button>
                <button class="customDeleteBtn" data-custom-id="${i.id}">削除</button>
            </td>
        </tr>
    `).join('');

    document.querySelectorAll('.customEditBtn').forEach(b =>
        b.onclick = e => editCustom(e.target.dataset.customId)
    );
    document.querySelectorAll('.customDeleteBtn').forEach(b =>
        b.onclick = e => deleteCustom(e.target.dataset.customId)
    );

    renderCustomPagination(total);
    updateCustomTableScroll();
}

// ------------------------------
// Pagination rendering
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

function updateCustomTableScroll() {
    if (customPageSize >= 10) {
        customTableWrapper.classList.add('scrollable');
    } else {
        customTableWrapper.classList.remove('scrollable');
    }
}

// ------------------------------
// Event listeners
// ------------------------------
addCustomBtn.onclick = () => openCustomModel();
customModalCancelBtn.onclick = () => closeCustomModel();
customSearch.addEventListener('input', () => { customPage = 1; renderCustomTable(); });
customPageSizeSelect.addEventListener('change', () => {
    customPageSize = parseInt(customPageSizeSelect.value);
    customPage = 1;
    renderCustomTable();
});
document.getElementById('custom_plan_type').addEventListener('change', updateDurationCustomOptions);


customForm.image.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        custom_preview.innerHTML = `<img src="${reader.result}" alt="預覽" style="max-width:90px;">`;
    };
    reader.readAsDataURL(file);
});
// ------------------------------
// Initial load
// ------------------------------
reloadCustomFromServer();
updateDurationCustomOptions();
