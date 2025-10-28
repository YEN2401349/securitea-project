// ------------------------------
// Package Option Management (Server-based)
// ------------------------------

// State variables
let packageItems = [];
let packageEditingId = null;
let packagePage = 1;
let packagePageSize = 5;

// DOM elements
const packageTableBody = document.getElementById('packageTableBody');
const packageSearch = document.getElementById('packageSearch');
const packagePageSizeSelect = document.getElementById('packagePageSize');
const packagePagination = document.getElementById('packagePagination');
const packageCurrentPageEl = document.getElementById('packageCurrentPage');
const packageTableWrapper = document.getElementById('packageTableWrapper');

const packageModel = document.getElementById('packageModel');
const packageForm = document.getElementById('packageForm');
const addPackageBtn = document.getElementById('addPackageBtn');
const packgeModalCancelBtn = document.getElementById('packgeModalCancelBtn'); // ✅ Matches HTML id

// ------------------------------
// Fetch data from server
// ------------------------------
async function reloadPackageFromServer() {
    try {
        const res = await fetch('component/api/get_package.php');
        const json = await res.json();
        if (!json.success) throw new Error(json.error);

        packageItems = json.data.map(p => ({
            id: p.product_id || p.id,
            name: p.name,
            price: p.price,
            plan_type: p.plan_type,
            billing_cycle: p.billing_cycle,
            duration_months: p.duration_months,
            description: p.description
        }));

        localStorage.setItem('packages', JSON.stringify(packageItems));
        renderPackageTable();
    } catch (err) {
        console.error('Failed to fetch package data:', err);
    }
}

// ------------------------------
// Add / Edit data (save to server)
// ------------------------------
async function savePackageToServer(data, isEdit = false) {
    const url = isEdit
        ? 'component/api/update_package.php'
        : 'component/api/add_package.php';

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
function packageLoad() {
    const raw = localStorage.getItem('packages');
    if (raw) packageItems = JSON.parse(raw);
    renderPackageTable();
}

// ------------------------------
// Open / Close Modal
// ------------------------------
function openPackageModel(edit = false, item = null) {
    packageModel.style.display = "flex";
    document.getElementById("packageModalTitle").textContent = edit ? "編集" : "追加";

    packageForm.name.value = item?.name || "";
    packageForm.price.value = item?.price || "";
    packageForm.plan_type.value = item?.billing_cycle || "monthly";
    packageForm.duration_months.value = item?.duration_months || "1";
    packageForm.description.value = item?.description || "";

    updateDurationOptions();
}

function closePackageModel() {
    packageModel.style.display = "none";
    packageEditingId = null;
}

// ------------------------------
// Update duration options based on plan_type
// ------------------------------
function updateDurationOptions() {
    const planTypeEl = document.getElementById("package_plan_type");
    const durationEl = document.getElementById("package_duration_months");

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
packageForm.onsubmit = async e => {
    e.preventDefault();

    const data = {
        name: packageForm.name.value.trim(),
        price: packageForm.price.value.trim(),
        billing_cycle: packageForm.plan_type.value.trim(),
        duration_months: packageForm.duration_months.value.trim(),
        description: packageForm.description.value.trim()
    };

    try {
        if (packageEditingId) {
            await savePackageToServer({ id: packageEditingId, ...data }, true);
        } else {
            await savePackageToServer(data, false);
        }

        closePackageModel();
        await reloadPackageFromServer(); // Reload updated data
    } catch (err) {
        alert("Save failed: " + err.message);
    }
};

// ------------------------------
// Edit / Delete
// ------------------------------
async function editPackage(id) {
    const item = packageItems.find(i => i.id == id);
    if (!item) return;
    packageEditingId = id;
    openPackageModel(true, item);
}

async function deletePackage(id) {
    if (!confirm("削除する？")) return;
    try {
        const res = await fetch('component/api/delete_package.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const json = await res.json();
        if (!json.success) throw new Error(json.error);
        await reloadPackageFromServer();
    } catch (err) {
        alert("Delete failed: " + err.message);
    }
}

// ------------------------------
// Table rendering & pagination
// ------------------------------
function queryPackageData() {
    const q = (packageSearch?.value || '').trim().toLowerCase();
    let list = packageItems;
    if (q) list = list.filter(i => i.name.toLowerCase().includes(q));

    const total = list.length;
    const start = (packagePage - 1) * packagePageSize;
    return { total, list: list.slice(start, start + packagePageSize) };
}

function renderPackageTable() {
    const { total, list } = queryPackageData();

    packageTableBody.innerHTML = list.map(i => `
        <tr>
            <td>${i.id}</td>
            <td>${i.name}</td>
            <td>¥${Number(i.price)}</td>
            <td>${i.plan_type}</td>
            <td>${i.description}</td>
            <td>
                <button class="packageEditBtn" data-package-id="${i.id}">編集</button>
                <button class="packageDeleteBtn" data-package-id="${i.id}">削除</button>
            </td>
        </tr>
    `).join('');

    document.querySelectorAll('.packageEditBtn').forEach(b =>
        b.onclick = e => editPackage(e.target.dataset.packageId)
    );
    document.querySelectorAll('.packageDeleteBtn').forEach(b =>
        b.onclick = e => deletePackage(e.target.dataset.packageId)
    );

    renderPackagePagination(total);
    updatePackageTableScroll();
}

// ------------------------------
// Pagination rendering
// ------------------------------
function renderPackagePagination(total) {
    const totalPages = Math.ceil(total / packagePageSize);
    packagePagination.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = i === packagePage ? 'bg-blue text-white px-2 py-1 rounded' : 'border px-2 py-1 rounded';
        btn.onclick = () => { packagePage = i; renderPackageTable(); };
        packagePagination.appendChild(btn);
    }
    packageCurrentPageEl.textContent = packagePage;
}

function updatePackageTableScroll() {
    if (packagePageSize >= 10) {
        packageTableWrapper.classList.add('scrollable');
    } else {
        packageTableWrapper.classList.remove('scrollable');
    }
}

// ------------------------------
// Event listeners
// ------------------------------
addPackageBtn.onclick = () => openPackageModel();
packgeModalCancelBtn.onclick = () => closePackageModel();
packageSearch.addEventListener('input', () => { packagePage = 1; renderPackageTable(); });
packagePageSizeSelect.addEventListener('change', () => {
    packagePageSize = parseInt(packagePageSizeSelect.value);
    packagePage = 1;
    renderPackageTable();
});
document.getElementById('plan_type').addEventListener('change', updateDurationOptions);

// ------------------------------
// Initial load
// ------------------------------
reloadPackageFromServer();
updateDurationOptions();
