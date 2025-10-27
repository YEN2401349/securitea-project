const storageKey = 'mgmt_demo_items_v2';
let items = [];
let page = 1;
let pageSize = 10;
let sortBy = 'createdAt_desc';

const tableBody = document.getElementById('tableBody');
const globalSearch = document.getElementById('globalSearch');
const sortSelect = document.getElementById('sortBy');
const pageSizeSelect = document.getElementById('pageSize');
const pagination = document.getElementById('pagination');
const currentPageEl = document.getElementById('currentPage');
const tableWrapper = document.getElementById('tableWrapper');
const sampleData = [
    {
        id: 'u_1',
        name: '山田太郎',
        email: 'taro@example.com',
        role: 'ベーシック月間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 30,
        device1: 'iPhone16 pro',
        device2: '',
        period: '2025-09-21~2025-10-20',
        price: '300円/月',
        custom: '',
    },
    {
        id: 'u_2',
        name: '鈴木花子',
        email: 'hanako@example.com',
        role: 'エキスパート年間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 25,
        device1: 'PC',
        device2: 'Xperia 10 Ⅵ',
        period: '2025-09-21~2026-09-20',
        price: '7700円/年',
        custom: '',
    },
    {
        id: 'u_3',
        name: '小林誠一',
        email: 'kobayashi@example.com',
        role: 'カスタムプラン',
        status: 'inactive',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 14,
        device1: 'Gaming PC',
        device2: '',
        period: '2025-10-01~2025-10-31',
        price: '610円/月',
        custom: 'オプション1,オプション4,オプション5,オプション7',
    },
    {
        id: 'u_4',
        name: '田中一郎',
        email: 'ichiro@example.com',
        role: 'ベーシック月間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 20,
        device1: 'iPad Pro',
        device2: '',
        period: '2025-09-25~2025-10-24',
        price: '300円/月',
        custom: '',
    },
    {
        id: 'u_5',
        name: '佐藤美咲',
        email: 'misaki@example.com',
        role: 'エキスパート年間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 18,
        device1: 'MacBook Air',
        device2: 'iPhone14',
        period: '2025-09-28~2026-09-27',
        price: '7700円/年',
        custom: '',
    },
    {
        id: 'u_6',
        name: '高橋健',
        email: 'ken@example.com',
        role: 'カスタムプラン',
        status: 'inactive',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 17,
        device1: 'Windows PC',
        device2: 'Android Tablet',
        period: '2025-10-02~2025-11-01',
        price: '610円/月',
        custom: 'オプション2,オプション3',
    },
    {
        id: 'u_7',
        name: '中村愛',
        email: 'ai@example.com',
        role: 'ベーシック月間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 16,
        device1: 'iPhone14',
        device2: '',
        period: '2025-10-03~2025-11-02',
        price: '300円/月',
        custom: '',
    },
    {
        id: 'u_8',
        name: '小川翔',
        email: 'sho@example.com',
        role: 'エキスパート年間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 15,
        device1: 'PC',
        device2: 'iPad',
        period: '2025-10-04~2026-10-03',
        price: '7700円/年',
        custom: '',
    },
    {
        id: 'u_9',
        name: '松本奈々',
        email: 'nana@example.com',
        role: 'カスタムプラン',
        status: 'inactive',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 13,
        device1: 'Laptop',
        device2: '',
        period: '2025-10-05~2025-11-04',
        price: '610円/月',
        custom: 'オプション1,オプション5',
    },
    {
        id: 'u_10',
        name: '木村拓哉',
        email: 'takuya@example.com',
        role: 'ベーシック月間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 12,
        device1: 'iPhone13',
        device2: '',
        period: '2025-10-06~2025-11-05',
        price: '300円/月',
        custom: '',
    },
    {
        id: 'u_11',
        name: '井上陽子',
        email: 'yoko@example.com',
        role: 'エキスパート年間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 11,
        device1: 'MacBook Pro',
        device2: 'iPhone12',
        period: '2025-10-07~2026-10-06',
        price: '7700円/年',
        custom: '',
    },
    {
        id: 'u_12',
        name: '林俊介',
        email: 'shunsuke@example.com',
        role: 'カスタムプラン',
        status: 'inactive',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 10,
        device1: 'Gaming PC',
        device2: 'Tablet',
        period: '2025-10-08~2025-11-07',
        price: '610円/月',
        custom: 'オプション3,オプション6',
    },
    {
        id: 'u_13',
        name: '青木真由美',
        email: 'mayumi@example.com',
        role: 'ベーシック月間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 9,
        device1: 'iPhone15',
        device2: '',
        period: '2025-10-09~2025-11-08',
        price: '300円/月',
        custom: '',
    },
    {
        id: 'u_14',
        name: '加藤大輔',
        email: 'daisuke@example.com',
        role: 'エキスパート年間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 8,
        device1: 'PC',
        device2: 'iPad',
        period: '2025-10-10~2026-10-09',
        price: '7700円/年',
        custom: '',
    },
    {
        id: 'u_15',
        name: '森田悠',
        email: 'haruka@example.com',
        role: 'カスタムプラン',
        status: 'inactive',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 7,
        device1: 'Laptop',
        device2: '',
        period: '2025-10-11~2025-11-10',
        price: '610円/月',
        custom: 'オプション2,オプション5',
    },
    {
        id: 'u_16',
        name: '浜田翔子',
        email: 'shoko@example.com',
        role: 'ベーシック月間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 6,
        device1: 'iPhone16',
        device2: '',
        period: '2025-10-12~2025-11-11',
        price: '300円/月',
        custom: '',
    },
    {
        id: 'u_17',
        name: '藤本直樹',
        email: 'naoki@example.com',
        role: 'エキスパート年間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 5,
        device1: 'MacBook Air',
        device2: 'iPhone14',
        period: '2025-10-13~2026-10-12',
        price: '7700円/年',
        custom: '',
    },
    {
        id: 'u_18',
        name: '長谷川玲子',
        email: 'reiko@example.com',
        role: 'カスタムプラン',
        status: 'inactive',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 4,
        device1: 'PC',
        device2: '',
        period: '2025-10-14~2025-11-13',
        price: '610円/月',
        custom: 'オプション1,オプション7',
    },
    {
        id: 'u_19',
        name: '石井翔太',
        email: 'shota@example.com',
        role: 'ベーシック月間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 3,
        device1: 'iPad',
        device2: '',
        period: '2025-10-15~2025-11-14',
        price: '300円/月',
        custom: '',
    },
    {
        id: 'u_20',
        name: '岡本彩',
        email: 'aya@example.com',
        role: 'エキスパート年間プラン',
        status: 'active',
        createdAt: Date.now() - 1000 * 60 * 60 * 24 * 2,
        device1: 'Laptop',
        device2: 'iPhone13',
        period: '2025-10-16~2026-10-15',
        price: '7700円/年',
        custom: '',
    }
];
// add scroll
function updateTableScroll() {
    if (pageSize >= 20) {
        tableWrapper.classList.add('scrollable');
    } else {
        tableWrapper.classList.remove('scrollable');
    }
}


// Load / save
function load() {
    const raw = localStorage.getItem(storageKey);
    if (raw) items = JSON.parse(raw);
    else {
        items = sampleData;
        localStorage.setItem(storageKey, JSON.stringify(items));
    }
    render();
}

// Query / filter
function queryData() {
    const q = (globalSearch?.value || '').trim().toLowerCase();
    let list = items.filter(it => !it._deleted);
    if (q) {
        list = list.filter(it =>
            (it.name + it.email + it.role + it.period + it.price + it.custom).toLowerCase().includes(q)
        );
    }
    if (sortBy === 'createdAt_desc') list.sort((a, b) => b.createdAt - a.createdAt);
    if (sortBy === 'createdAt_asc') list.sort((a, b) => a.createdAt - b.createdAt);
    const total = list.length;
    const start = (page - 1) * pageSize;
    return { total, list: list.slice(start, start + pageSize) };
}

// Render table + pagination
function render() {
    const { total, list } = queryData();
    tableBody.innerHTML = list.map((it, i) => `
        <tr>
            <td>${(page - 1) * pageSize + i + 1}</td>
            <td>${it.name}</td>
            <td>${it.email}</td>
            <td>${new Date(it.createdAt).toLocaleDateString()}</td>
            <td>${it.period || ''}</td>
            <td>${it.role}</td>
            <td>${it.price || ''}</td>
            <td>${it.custom || ''}</td>
            <td><button data-id="${it.id}" class="deleteBtn border">削除</button></td>
        </tr>
    `).join('');

    document.querySelectorAll('.deleteBtn').forEach(b => b.addEventListener('click', onDelete));

    renderPagination(total);
    updateTableScroll();
}

function renderPagination(total) {
    const totalPages = Math.ceil(total / pageSize);
    pagination.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.textContent = i;
        btn.className = i === page ? 'bg-blue-500 text-white px-2 py-1 rounded' : 'border px-2 py-1 rounded';
        btn.addEventListener('click', () => { page = i; render(); });
        pagination.appendChild(btn);
    }
    currentPageEl.textContent = page;
}

// Delete user
function onDelete(e) {
    if (!confirm('削除してもよろしいですか？')) return;
    items.find(x => x.id === e.target.dataset.id)._deleted = true;
    localStorage.setItem(storageKey, JSON.stringify(items));
    render();
}

// Event listeners
globalSearch.addEventListener('input', () => { page = 1; render(); });
sortSelect.addEventListener('change', () => { sortBy = sortSelect.value; page = 1; render(); });
pageSizeSelect.addEventListener('change', () => { pageSize = parseInt(pageSizeSelect.value); page = 1; render(); });

// Initial load
load();
