<div id="packageModel" class="packageModelHidden">
    <div>
        <h3 id="packageModalTitle">オプション追加</h3>
        <form id="packageForm">
            <div class="formContainer">
                <div style="flex:1 1 45%;">
                    <label>商品名</label>
                    <input name="name" required />
                </div>
                <div style="flex:1 1 45%;">
                    <label>価格</label>
                    <input name="price" type="number" required />
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
                    <label>説明</label>
                    <textarea name="description" cols="70" rows="10" required></textarea>
                </div>
            </div>
            <div style="margin-top:1rem; text-align:right;">
                <button type="button" id="packgeModalCancelBtn" class="border">キャンセル</button>
                <button type="submit" class="bg-blue">保存</button>
            </div>
        </form>
    </div>
</div>