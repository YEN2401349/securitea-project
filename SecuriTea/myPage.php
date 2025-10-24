<?php session_start();
include "./component/header.php"; ?>
<link rel="stylesheet" href="./css/myPage.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<div class="main">
    <div class="container">
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
                        <div class="info-label">生年月日</div>
                        <div id="profile-birthday" class="info-value">2005年7月1日</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">性別</div>
                        <div id="profile-gender" class="info-value">男性</div>
                    </div>
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
                    <div class="editBtnDiv">
                        <button id="editProfileBtn" class="editBtn">情報編集</button>
                    </div>
                </div>

            </div>

            <!-- 個人情報編集From -->
            <?php include "./component/editProfileFrom.php"; ?>


            <div class="card">
                <h2>利用状況</h2>
                <div class="info-row">
                    <div class="info-label">利用プラン</div>
                    <div class="info-value">月間カスタムプラン</div>
                </div>
                <div class="info-row">
                    <div class="info-label">料金　/月間</div>
                    <div class="info-value">550円</div>
                </div>
                <div class="info-row">
                    <div class="info-label">契約期間</div>
                    <div class="info-value">2025年3月10日 〜 2025年4月9日</div>
                </div>
                <div class="info-row">
                    <div class="info-label">次回更新日</div>
                    <div class="info-value">2025年4月10日</div>
                </div>
                <div class="info-row">
                    <div class="info-label">お支払い方法</div>
                    <div class="info-value">クレジットカード</div>
                </div>
                <div class="editBtnDiv">
                    <button class="editBtn">お支払いの変更</button>
                </div>
            </div>

            <div class="card">
                <div class="info-row">
                    <h2>基本オプション</h2>
                </div>
                <div class="info-row">
                    <div class="info-label">オプション1</div>
                    <div class="info-value">aaaaaaaaaaaaaaa</div>
                </div>
                <div class="info-row">
                    <div class="info-label">オプション2</div>
                    <div class="info-value">bbbbbbbbbbbbbbb</div>
                </div>
                <div class="info-row">
                    <div class="info-label">オプション5</div>
                    <div class="info-value">ccccccccccccccc</div>
                </div>
                <div class="editBtnDiv">
                    <button class="editBtn">プラン変更</button>
                    <button class="deleteBtn">契約解除</button>
                </div>
            </div>
    </div>
</div>
<script src="./js/myPage.js"></script>
<script src="./component/js/editProfileFrom.js"></script>
<?php include "./component/footer.php"; ?>