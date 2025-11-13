<div id="packageModel" class="packageModelHidden">
    <div>
        <h3 id="modalTitle">ユーザー追加</h3>
        <div class="formScroll">
            <form id="packageForm">
                <div class="formContainer">
                    <div style="flex:1 1 45%;">
                        <label>商品名</label>
                        <input name="package_name" required />
                    </div>
                    <div style="flex:1 1 45%;">
                        <label>価格</label>
                        <input name="package_price" type="number" required />
                    </div>
                    <div style="flex:1 1 45%;">
                        <label>サイクル</label>
                        <select id="package_plan_type" name="plan_type" required>
                            <option value="monthly">月付</option>
                            <option value="yearly">年付</option>
                            <option value="lifetime">買い切り</option>
                        </select>
                    </div>
                    <div style="flex:1 1 45%;">
                        <label>期間（月数）</label>
                        <select id="package_duration_months" name="duration_months" required>
                            <option value=1>1</option>
                            <option value=6>6</option>
                            <option value=12>12</option>
                            <option value=999>買い切り</option>
                        </select>
                    </div>
                    <div style="flex:1 1 45%;">
                        <label>商品画像</label>
                        <input type="file" name="image" accept="image/*"><br>
                        <div id="package_preview">
                        </div>
                    </div>
                    <div style="flex:1 1 45%;">
                        <label>アイキャッチ</label><br>
                        <textarea name="package_eye_catch" cols="70" rows="3" maxlength="255"></textarea>
                    </div>
                    <div style="flex:1 1 45%;">
                        <label>機能</label><br>
                        <textarea name="package_security_features" cols="70" rows="10" maxlength="255"></textarea>
                    </div>
                    <div style="flex:1 1 45%;">
                        <label>説明</label><br>
                        <textarea name="package_description" cols="70" rows="10" required></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div style="margin-top:1rem; text-align:right;" class="modalFooter">
            <button type="button" id="cancelBtn" class="border">キャンセル</button>
            <button type="submit" class="bg-blue" form="packageForm">保存</button>
        </div>
    </div>
</div>