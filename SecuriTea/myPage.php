<?php session_start(); include "./component/header.php"; ?>
<link rel="stylesheet" href="./css/myPage.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<div class="main">
    <div class="container">
        <!-- サイドバー -->
        <aside class="sidebar">
            <ul>
                <li class="active" data-target="personal">個人情報</li>
                <li data-target="status">ご利用状態</li>
                <li>お支払い方法</li>
                <li>Q&A・お問い合わせ</li>
            </ul>
        </aside>

        <!-- メインコンテンツ -->
        <main class="content">
            <!-- 個人情報 -->
            <div class="tab-content" id="personal">
                <!-- 個人情報 -->
                <div class="card">
                    <h2>個人情報</h2>
                    <div class="info-row">
                        <div class="info-label">名前</div>
                        <div id="profile-name" class="info-value">〇〇　◇◇</div>
                    </div>
                    <div class="info-row">
                        <div  class="info-label">生年月日</div>
                        <div id="profile-birthday" class="info-value">2005年7月1日</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">性別</div>
                        <div id="profile-gender" class="info-value">男性</div>
                    </div>
                </div>

                <!-- 連絡先情報 -->
                <div class="card">
                    <h2>連絡先情報</h2>
                    <div class="info-row">
                        <div class="info-label">メール</div>
                        <div id="profile-email" class="info-value">
                            aaa1234@gmail.com
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">電話</div>
                        <div id="profile-phone" class="info-value">090-1234-5678</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">住所</div>
                        <div class="info-value">〇〇県〇〇市〇〇町</div>
                    </div>
                </div>
                <button id="editProfileBtn">情報編集</button>
            </div>

            <!-- 個人情報編集From -->
            <?php include "./component/editProfileFrom.php"; ?>


            <!-- ご利用状態 -->
            <div class="tab-content" id="status" style="display:none;">
                <!-- 契約プラン -->
                <div class="card">
                    <div class="info-row">
                        <div class="info-label">現在のプラン</div>
                        <div class="info-value">年間エキスパート</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">料金　/年間</div>
                        <div class="info-value">9980円</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">契約期間</div>
                        <div class="info-value">2025年1月1日 〜 2025年12月31日</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">次回更新日</div>
                        <div class="info-value">2026年1月1日</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">商品説明</div>
                        <div class="info-value">aaaaaaaaaaaaaaa<br>bbbbbbbbbbbb<br>cccccccccc</div>
                    </div>
                </div>

                <!-- 利用状況 -->
                <div class="card">
                    <h2>クラウド管理(月間ベーシック)</h2>
                    <div class="info-row">
                        <div class="info-label">料金　/月間</div>
                        <div class="info-value">490円</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">契約期間</div>
                        <div class="info-value">2025年4月24日 〜 2025年5月23日</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">次回更新日</div>
                        <div class="info-value">2056年5月24日</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">管理データ量</div>
                        <div class="info-value">940GB/2TB</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">商品説明</div>
                        <div class="info-value">2TBのクラウドテータの管理</div>
                    </div>
                </div>

                <div class="card">
                    <h2>テータバックアップサービス(年間ベーシック)</h2>
                    <div class="info-row">
                        <div class="info-label">料金　/年間</div>
                        <div class="info-value">2080円</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">契約期間</div>
                        <div class="info-value">2025年1月1日 〜 2025年12月31日</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">次回更新日</div>
                        <div class="info-value">2026年1月1日</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">管理データ量</div>
                        <div class="info-value">523GB/1TB</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">商品説明</div>
                        <div class="info-value">1TBまでPCバックアップデータの保管を行う</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="./js/myPage.js"></script>
<script src="./component/js/editProfileFrom.js"></script>
<?php include "./component/footer.php"; ?>
