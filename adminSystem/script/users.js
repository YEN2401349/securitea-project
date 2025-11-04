if (localStorage.getItem('token') == null) {
    window.location.href = 'login.php'
}
window.onload = function () {
    window.history.forward();
};
window.onpageshow = function (event) {
    if (event.persisted) {
        window.location.reload();
    }
};
let userItems = [];
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

// Reload data from server
async function reloadFromServer() {
    try {
        const res = await fetch('component/api/get_users.php');
        const json = await res.json();
        if (!json.success) throw new Error(json.error);

        userItems = json.data.map(p => ({
            name: p.full_name,
            email: p.user_email,
            update_date: p.update_date,
        }));

        localStorage.setItem('users', JSON.stringify(userItems));
        render();
    } catch (err) {
        console.error('Error:', err);
    }
}
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
    const raw = localStorage.getItem('users');
    if (raw) usersItems = JSON.parse(raw);
    render();
}

// Query / filter
function queryData() {
    const q = (globalSearch?.value || '').trim().toLowerCase();
    let list = userItems;
    if (q) {
        list = list.filter(it =>
            (it.name + it.email).toLowerCase().includes(q)
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
    console.log('Rendering', list);
    tableBody.innerHTML = list.map((it, i) => `
        <tr>
            <td>${(page - 1) * pageSize + i + 1}</td>
            <td>${it.name}</td>
            <td>${it.email}</td>
            <td>${new Date(it.update_date).toLocaleDateString()}</td>
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
    userItems.find(x => x.id === e.target.dataset.id)._deleted = true;
    localStorage.setItem(storageKey, JSON.stringify(userItems));
    render();
}

// Event listeners
globalSearch.addEventListener('input', () => { page = 1; render(); });
sortSelect.addEventListener('change', () => { sortBy = sortSelect.value; page = 1; render(); });
pageSizeSelect.addEventListener('change', () => { pageSize = parseInt(pageSizeSelect.value); page = 1; render(); });

// Initial load
reloadFromServer();
