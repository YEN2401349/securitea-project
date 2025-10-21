<link rel="stylesheet" href="./component/css/editProfileFrom.css">
<div id="editProfileForm" class="editProfileFormHidden">
    <div class="modalContent">
        <form id="customForm">
            <h1 class="modalTitle">プロフィール編集</h1>

            <div class="formSection">
                <h3>個人情報</h3>
                <div class="inputRow">
                    <div class="inputGroup">
                        <label>姓</label>
                        <input name="firstname" placeholder="例: 山田" required />
                    </div>
                    <div class="inputGroup">
                        <label>名</label>
                        <input name="lastname" placeholder="例: 太郎" required />
                    </div>
                </div>
                <div class="inputRow">
                    <div class="inputGroup">
                        <label>生年月日</label>
                        <input name="birthdate" type="date" required />
                    </div>
                    <div class="inputGroup">
                        <label>性別</label>
                        <select name="gender">
                            <option>男性</option>
                            <option>女性</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="formSection">
                <h3>連絡先情報</h3>
                <div class="inputRow">
                    <div class="inputGroup">
                        <label>メール</label>
                        <input name="email" type="email" placeholder="example@mail.com" required />
                    </div>
                    <div class="inputGroup">
                        <label>電話番号</label>
                        <input name="phone" type="tel" placeholder="090-1234-5678" required />
                    </div>
                </div>
            </div>

            <div class="formActions">
                <button type="button" id="cancelBtn" class="btn cancel">キャンセル</button>
                <button type="submit" class="btn save">保存</button>
            </div>
        </form>
    </div>
</div>