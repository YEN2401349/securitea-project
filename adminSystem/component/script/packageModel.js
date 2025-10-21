// ------------------------------
// Package Option Management
// ------------------------------

document.addEventListener('DOMContentLoaded', () => {
    // LocalStorage key
    const packageStorageKey = 'packageSampleData';

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
    const cancelPackageBtn = document.getElementById('cancelPackageBtn');

    // Sample data
    const packageSampleData = [
        { id: 'p_1', name: 'カスタムレポート', month: '100', year: '1000' },
        { id: 'p_2', name: '追加ストレージ', month: '200', year: '2000' },
        { id: 'p_3', name: 'チーム共有機能', month: '300', year: '3000' },
        { id: 'p_4', name: '高速バックアップ', month: '150', year: '1500' },
        { id: 'p_5', name: '専用サポート', month: '400', year: '4500' },
    ];

    // ------------------------------
    // Initialization
    // ------------------------------
    function packageLoad() {
        const raw = localStorage.getItem(packageStorageKey);
        if (raw) {
            packageItems = JSON.parse(raw);
        } else {
            packageItems = packageSampleData;
            localStorage.setItem(packageStorageKey, JSON.stringify(packageItems));
        }
        renderPackageTable();
    }

    function savePackage() {
        localStorage.setItem(packageStorageKey, JSON.stringify(packageItems));
    }

    // ------------------------------
    // Open / Close Modal
    // ------------------------------
    function openPackageModel(edit = false, item = null) {
        packageModel.style.display = 'flex';
        document.getElementById('packageModalTitle').textContent = edit ? '編集' : '追加';
        packageForm.name.value = item?.name;
        packageForm.month.value = item?.month;
        packageForm.year.value = item?.year;
    }

    function closePackageModel() {
        packageModel.style.display = 'none';
        packageEditingId = null;
    }

    // ------------------------------
    // Form Submission
    // ------------------------------
    packageForm.onsubmit = e => {
        e.preventDefault();

        const data = {
            name: packageForm.name.value.trim(),
            month: packageForm.month.value.trim(),
            year: packageForm.year.value.trim()
        };

        if (packageEditingId) {
            const item = packageItems.find(i => i.id === packageEditingId);
            if (item) Object.assign(item, data);
        } else {
            packageItems.push({ id: 'p_' + Math.random().toString(36).slice(2, 9), ...data });
        }

        savePackage();
        closePackageModel();
        renderPackageTable();
    };

    // ------------------------------
    // Edit / Delete
    // ------------------------------
    function editPackage(id) {
        const item = packageItems.find(i => i.id === id);
        if (!item) return;
        packageEditingId = id;
        openPackageModel(true, item);
    }

    function deletePackage(id) {
        if (!confirm('Are you sure you want to delete this item?')) return;
        const item = packageItems.find(i => i.id === id);
        if (item) item._deleted = true;
        savePackage();
        renderPackageTable();
    }

    // ------------------------------
    // Table Rendering & Pagination
    // ------------------------------
    function queryPackageData() {
        const q = (packageSearch?.value || '').trim().toLowerCase();
        let list = packageItems.filter(i => !i._deleted);
        if (q) {
            list = list.filter(i => (i.name + i.month + i.year).toLowerCase().includes(q));
        }
        const total = list.length;
        const start = (packagePage - 1) * packagePageSize;
        return { total, list: list.slice(start, start + packagePageSize) };
    }

    function renderPackageTable() {
        const { total, list } = queryPackageData();

        packageTableBody.innerHTML = list.map((i, idx) => `
        <tr>
            <td>${(packagePage - 1) * packagePageSize + idx + 1}</td>
            <td>${i.name}</td>
            <td>¥${i.month}</td>
            <td>¥${i.year}</td>
            <td>
                <button class="packageEditBtn" data-id="${i.id}">編集</button>
                <button class="packageDeleteBtn" data-id="${i.id}">削除</button>
            </td>
        </tr>
    `).join('');

        document.querySelectorAll('.packageEditBtn').forEach(b => b.onclick = e => editPackage(e.target.dataset.id));
        document.querySelectorAll('.packageDeleteBtn').forEach(b => b.onclick = e => deletePackage(e.target.dataset.id));

        renderPackagePagination(total);
        updatePackageTableScroll();
    }

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
    // Event Listeners
    // ------------------------------
    addPackageBtn.onclick = () => openPackageModel();
    cancelPackageBtn.onclick = () => closePackageModel();
    packageSearch.addEventListener('input', () => { packagePage = 1; renderPackageTable(); });
    packagePageSizeSelect.addEventListener('change', () => {
        packagePageSize = parseInt(packagePageSizeSelect.value);
        packagePage = 1;
        renderPackageTable();
    });

    // ------------------------------
    // Initial Load
    // ------------------------------
    packageLoad();
});
