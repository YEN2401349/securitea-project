// ------------------------------
// Package Plan Management
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
const cancelBtn = document.getElementById('cancelBtn');
const package_preview = document.getElementById('package_preview');

const package_plan_type = document.getElementById('package_plan_type');
const package_duration_months = document.getElementById('package_duration_months');

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
            package_name: p.name,
            package_price: p.price,
            package_plan_type: p.plan_type,
            package_billing_cycle: p.billing_cycle,
            package_security_features: p.security_features,
            package_eye_catch: p.eye_catch,
            package_duration_months: p.duration_months,
            package_description: p.description,
            image_path: p.image_path
        }));

        localStorage.setItem('packages', JSON.stringify(packageItems));
        renderPackageTable();
    } catch (err) {
        console.error('Error:', err);
    }
}

// ------------------------------
// Save to server (Add / Edit)
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
// Update package_duration_months options
// ------------------------------
function updateDurationPackageOptions() {
    const options = package_duration_months.options;
    for (let i = 0; i < options.length; i++) options[i].disabled = false;

    switch (package_plan_type.value) {
        case 'lifetime':
            for (let o of options)
                if (['1', '6', '12'].includes(o.value)) o.disabled = true;
            package_duration_months.value = '999';
            break;
        case 'yearly':
            for (let o of options)
                if (['1', '6', '999'].includes(o.value)) o.disabled = true;
            package_duration_months.value = '12';
            break;
        default: // monthly
            for (let o of options)
                if (['12', '999'].includes(o.value)) o.disabled = true;
            for (let o of options)
                if (!o.disabled) {
                    package_duration_months.value = o.value;
                    break;
                }
            break;
    }
}

// ------------------------------
// Open / Close Modal
// ------------------------------
function openPackageModel(edit = false, item = null) {
    packageModel.style.display = "flex";
    document.getElementById("modalTitle").textContent = edit ? "編集" : "追加";
    packageForm.package_name.value = item?.package_name || "";
    packageForm.package_price.value = item?.package_price || "";
    packageForm.package_eye_catch.value = item?.package_eye_catch || "";
    packageForm.package_security_features.value = item?.package_security_features || "";
    packageForm.package_plan_type.value = item?.package_billing_cycle || "monthly";
    packageForm.image.value = "";
    packageForm.package_duration_months.value = item?.package_duration_months;
    packageForm.package_description.value = item?.package_description || "";
    package_preview.innerHTML = item?.image_path
        ? `<img src="${item.image_path}" alt="預覽" style="max-width:200px;">`
        : "";
    package_existingImagePath = item?.image_path || null;
    if (!edit) updateDurationPackageOptions();
}

function closePackageModel() {
    packageModel.style.display = "none";
    packageEditingId = null;
}

// ------------------------------
// Form Submission (Add / Edit)
// ------------------------------
packageForm.onsubmit = async e => {
    e.preventDefault();
    const data = {
        package_name: packageForm.package_name.value.trim(),
        package_price: packageForm.package_price.value.trim(),
        package_billing_cycle: packageForm.package_plan_type.value.trim(),
        package_security_features: packageForm.package_security_features.value.trim(),
        package_eye_catch: packageForm.package_eye_catch.value.trim(),
        package_image: packageForm.image.files[0] ? await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = () => reject(new Error("画像の読み込みに失敗しました"));
            reader.readAsDataURL(packageForm.image.files[0]);
        }) : null,
        package_existingImagePath,
        package_duration_months: packageForm.package_duration_months.value.trim(),
        package_description: packageForm.package_description.value.trim()
    };

    try {
        if (packageEditingId) {
            await savePackageToServer({ id: packageEditingId, ...data }, true);
        } else {
            await savePackageToServer(data, false);
        }

        closePackageModel();
        await reloadPackageFromServer();
    } catch (err) {
        alert("保存失敗：" + err.message);
    }
};

// ------------------------------
// Edit / Delete Functions
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
        alert("削除失敗：" + err.message);
    }
}

// ------------------------------
// Table Rendering & Pagination
// ------------------------------
function queryPackageData() {
    const q = (packageSearch?.value || '').trim().toLowerCase();
    let list = packageItems;
    if (q) list = list.filter(i => i.package_name.toLowerCase().includes(q));

    const total = list.length;
    const start = (packagePage - 1) * packagePageSize;
    return { total, list: list.slice(start, start + packagePageSize) };
}

function renderPackageTable() {
    const { total, list } = queryPackageData();

    packageTableBody.innerHTML = list.map(i => `
        <tr>
            <td>${i.id}</td>
            <td>${i.package_name}</td>
            <td>¥${parseInt(i.package_price, 10).toLocaleString()}</td>
            <td>${i.package_plan_type}</td>
            <td>${i.package_description}</td>
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
    updateTableScroll();
}

// ------------------------------
// Pagination Rendering
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

// ------------------------------
// Enable table scroll if page size large
// ------------------------------
function updateTableScroll() {
    if (packagePageSize >= 10) {
        packageTableWrapper.classList.add('scrollable');
    } else {
        packageTableWrapper.classList.remove('scrollable');
    }
}

// ------------------------------
// Event Listeners
// ------------------------------
addPackageBtn.onclick = () => openPackageModel();
cancelBtn.onclick = () => closePackageModel();
packageSearch.addEventListener('input', () => { packagePage = 1; renderPackageTable(); });
packagePageSizeSelect.addEventListener('change', () => {
    packagePageSize = parseInt(packagePageSizeSelect.value);
    packagePage = 1;
    renderPackageTable();
});
package_plan_type.addEventListener('change', updateDurationPackageOptions);

packageForm.image.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        package_preview.innerHTML = `<img src="${reader.result}" alt="預覽" style="max-width:200px;">`;
    };
    reader.readAsDataURL(file);
});

// ------------------------------
// Initial Load
// ------------------------------
reloadPackageFromServer();
updateDurationPackageOptions();