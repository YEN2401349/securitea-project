<?php include("component/header.php"); ?>
<link rel="stylesheet" href="./css/users.css">



    <section class="section">
        <header class="flex items-center justify-between mb-4">
            <h1>利用者確認</h1>
            <div class="flex items-center gap-3">
                <input id="globalSearch" type="search" placeholder="名前／メール..." />
            </div>
        </header>

        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <select id="pageSize">
                    <option value="5">1ページ 5件</option>
                    <option value="10" selected>1ページ 10件</option>
                    <option value="20">1ページ 20件</option>
                </select>
                <button id="addBtn" class="bg-green" hidden>ユーザー追加</button>
            </div>
            <div class="text-sm">並び替え：
                <select id="sortBy">
                    <option value="createdAt_desc">登録日（新しい→古い）</option>
                    <option value="createdAt_asc">登録日（古い→新しい）</option>
                </select>
            </div>
        </div>

        <div class="rounded shadow overflow-x-auto table-wrapper" id="tableWrapper">
            <table>
                <thead>
                    <tr>
                        <th >ID</th>
                        <th>名前</th>
                        <th>メール</th>
                        <th>登録日</th>
                        <th>利用期間</th>
                        <th>プラン／商品</th>
                        <th>料金</th>
                        <th>カスタム設定</th>
                        <!-- <th>操作</th> -->
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between">
            <div id="pagination" class="flex gap-2"></div>
            <div class="text-sm">ページ: <span id="currentPage">1</span></div>
        </div>
    </section>
</main>
</div>

<!-- modal -->
<div id="modal" class="hidden">
    <div>
        <h3 id="modalTitle">ユーザー追加</h3>
        <form id="form">
            <div style="display:flex;flex-wrap:wrap;gap:0.75rem;">
                <div style="flex:1 1 45%;">
                    <label>名前</label>
                    <input name="name" required />
                </div>
                <div style="flex:1 1 45%;">
                    <label>メール</label>
                    <input name="email" type="email" required />
                </div>
                <div style="flex:1 1 45%;">
                    <label>役割</label>
                    <select name="role">
                        <option>Admin</option>
                        <option>Editor</option>
                        <option>Viewer</option>
                    </select>
                </div>
                <div style="flex:1 1 45%;">
                    <label>ステータス</label>
                    <select name="status">
                        <option value="active">有効</option>
                        <option value="inactive">無効</option>
                    </select>
                </div>
            </div>
            <div style="margin-top:1rem; text-align:right;">
                <button type="button" id="cancelBtn" class="border">キャンセル</button>
                <button type="submit" class="bg-blue">保存</button>
            </div>
        </form>
    </div>
</div>
<script src="./script/users.js"></script>
<?php include("component/footer.php"); ?>
