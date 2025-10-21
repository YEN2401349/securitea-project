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
                    <label>月間価格</label>
                    <input name="month" type="number" required />
                </div>
                <div style="flex:1 1 45%;">
                    <label>年間プラン</label>
                    <input name="year" type="number" required />
                </div>
                <div style="flex:1 1 45%;">
                    <label>商品説明</label>
                    <textarea name="description" cols="65" rows="10" required></textarea>
                </div>
            </div>
            <div style="margin-top:1rem; text-align:right;">
                <button type="button" id="cancelPackageBtn" class="border">キャンセル</button>
                <button type="submit" class="bg-blue">保存</button>
            </div>
        </form>
    </div>
</div>