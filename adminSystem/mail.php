<?php include("component/header.php"); ?>
<link rel="stylesheet" href="./css/mail.css">

<header class="flex mb-4" style="align-items:center; justify-content:space-between;">
    <h1>📨 お問い合わせ管理</h1>
    <button id="refreshBtn" class="button is-small is-link">更新</button>
    <label for="showUnhandled" style="cursor: pointer; margin-left: 5px;">
        <input type="checkbox" id="showUnhandled" name="show_unhandled">
        未対応のみ表示
    </label>

</header>

<div class="mail-container">

    <div class="mailbox card">
        <div class="mail-header">
            <h2 class="title is-6">受信ボックス</h2>
        </div>
        <div class="mail-list-wrapper" id="inquiryListWrapper">
            <ul class="mail-list" id="inquiryList">
                <li class="mail-item">
                    <div class="mail-sender">読み込み中...</div>
                </li>
            </ul>
        </div>
    </div>

    <div class="mail-detail card" id="mail-detail-view">
        <div class="detail-placeholder">
            <p>📬 お問い合わせを選択して詳細を表示します</p>
        </div>
    </div>

</div>

<script src="./script/mail.js"></script>

<?php include("component/footer.php"); ?>