<?php include("component/header.php"); ?>
<link rel="stylesheet" href="./css/products.css">
<main class="main-content">
    <header class="main-header">
        <div class="user-info"><span>管理者: 山田太郎</span><a href="#">ログアウト</a></div>
    </header>
    <section>
        <header class="flex items-center justify-between mb-4">
            <h1>基本プラン一覧</h1>
            <div class="flex items-center gap-3">
                <input id="packageSearch" type="search" placeholder="商品名" />
            </div>
        </header>

        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <select id="packagePageSize">
                    <option value="5" selected>1ページ 5件</option>
                    <option value="10">1ページ 10件</option>
                    <option value="20">1ページ 20件</option>
                </select>
                <button id="addPackageBtn" class="bg-green">プラン一追加</button>
            </div>
        </div>

        <div class="rounded shadow overflow-x-auto" id="packageTableWrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>商品名</th>
                        <th>価格</th>
                        <th>サイクル</th>
                        <th>説明</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="packageTableBody"></tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div id="packagePagination" class="flex gap-2"></div>
            <div class="text-sm">ページ: <span id="packageCurrentPage">1</span></div>
        </div>
    </section>

    <section>
        <header class="flex items-center justify-between mb-4">
            <h1>カスタムオプション一覧</h1>
            <div class="flex items-center gap-3">
                <input id="customSearch" type="search" placeholder="商品名" />
            </div>
        </header>

        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <select id="customPageSize">
                    <option value="5" selected>1ページ 5件</option>
                    <option value="10">1ページ 10件</option>
                    <option value="20">1ページ 20件</option>
                </select>
                <button id="addCustomBtn" class="bg-green">オプション一追加</button>
            </div>
        </div>

        <div class="rounded shadow overflow-x-auto" id="customTableWrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>商品名</th>
                        <th>価格</th>
                        <th>サイクル</th>
                        <th>説明</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody id="customTableBody"></tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div id="customPagination" class="flex gap-2"></div>
            <div class="text-sm">ページ: <span id="customCurrentPage">1</span></div>
        </div>
    </section>
</main>
</div>
<?php include("component/customModel.php"); ?>
<?php include("component/packageModel.php"); ?>
<script src="component/script/customModel.js"></script>
<script src="component/script/packageModel.js"></script>
<?php include("component/footer.php"); ?>
