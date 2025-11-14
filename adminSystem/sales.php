<?php include("component/header.php"); ?>
<link rel="stylesheet" href="css/sales.css">
<section class="section">
    <div class="container">
        <h1 class="title">📊 売上照会</h1>

        <div class="box">
            <div class="form-grid">
                <div class="form-item">
                    <label for="startDate">開始日</label>
                    <input type="date" class="input" id="startDate">
                </div>
                <div class="form-item">
                    <label for="endDate">終了日</label>
                    <input type="date" class="input" id="endDate">
                </div>
                <div class="form-item">
                    <label for="category">商品分類</label>
                    <select name="category" class="select" id="category">
                        <option value="0">全て</option>
                        <option value="1">プラン</option>
                        <option value="2">カスタムオプション</option>
                    </select>
                </div>
                <div class="form-item">
                    <label for="keyword">商品名</label>
                    <input type="text" class="input" placeholder="商品名を入力" id="keyword">
                </div>
                <div class="form-item">
                    <div class="button-group">
                        <button class="btn btn-primary" id="searchBtn">検索</button>
                        <button class="btn btn-light" id="resetBtn">リセット</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="box table-container">
            <div class="table-wrapper" id="tableWrapper">
                <table class="table" id="salesTable">
                    <thead>
                        <tr>
                            <th>日期</th>
                            <th>商品</th>
                            <th>數量</th>
                            <th>單價</th>
                            <th>小計</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="total-amount">
                <strong>合計金額：</strong> <span id="totalAmount">¥0</span>
            </div>
        </div>
    </div>
</section>

<script src="script/sales.js"></script>
<?php include("component/footer.php"); ?>