<?php include("component/header.php"); ?>
<link rel="stylesheet" href="./css/mail.css">




            <header class="flex  mb-4">
                <h1>ダッシュボード</h1>
            </header>

            <div class="mail-container">

                <div class="mailbox">
                    <div class="mail-toolbar">
                        <input type="checkbox" id="select-all">
                        <button class="btn-tool">更新</button>
                        <button class="btn-tool">削除</button>
                    </div>

                    <ul class="mail-list">
                        <li class="mail-item unread">
                            <div class="mail-select"><input type="checkbox"></div>
                            <div class="mail-sender">株式会社A</div>
                            <div class="mail-subject"><strong>【ご確認】先日の打ち合わせについて</strong><span class="mail-preview"> -
                                    添付資料をご確認いただき...</span></div>
                            <div class="mail-date">10:30</div>
                        </li>
                        <li class="mail-item">
                            <div class="mail-select"><input type="checkbox"></div>
                            <div class="mail-sender">田中 優子</div>
                            <div class="mail-subject"><strong>Re: プロジェクトの進捗報告</strong><span class="mail-preview"> -
                                    承知いたしました...</span></div>
                            <div class="mail-date">9:15</div>
                        </li>
                        <li class="mail-item">
                            <div class="mail-select"><input type="checkbox"></div>
                            <div class="mail-sender">B-Mart オンライン</div>
                            <div class="mail-subject"><strong>【セール】週末限定タイムセールのお知らせ！</strong><span class="mail-preview">
                                    - 人気商品が最大50%OFF！...</span></div>
                            <div class="mail-date">昨日</div>
                        </li>
                        <li class="mail-item unread">
                            <div class="mail-select"><input type="checkbox"></div>
                            <div class="mail-sender">システム通知</div>
                            <div class="mail-subject"><strong>サーバーメンテナンスのお知らせ</strong><span class="mail-preview"> -
                                    下記の日時で...</span></div>
                            <div class="mail-date">昨日</div>
                        </li>
                        <li class="mail-item">
                            <div class="mail-select"><input type="checkbox"></div>
                            <div class="mail-sender">鈴木 一郎</div>
                            <div class="mail-subject"><strong>来週の予定について</strong><span class="mail-preview"> -
                                    ご都合いかがでしょうか...</span></div>
                            <div class="mail-date">10月8日</div>
                        </li>
                    </ul>
                </div>

                <div class="mail-detail" id="mail-detail-view">
                    <div class="detail-placeholder">
                        <p>メールを選択して詳細を表示します</p>
                    </div>
                </div>

            </div>
        </main>
    <script src="./script/mail.js"></script>
</body>

</html>

<?php include("component/footer.php"); ?>