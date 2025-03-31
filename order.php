<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="icon" href="./imgs/webimg.ico" />
    <title>歐印精品-訂購基本資料</title>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
        <h1>訂購基本資料</h1>
    </div>

    <div class="container mb-5">
        <!-- 可以依需求設定 action 與 method -->
        <form action="process-order.php" method="post">
            <!-- 訂購人資訊 -->
            <h4 class="mb-3">訂購人資訊</h4>
            <div class="container border border-secondary p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="buyerName" class="form-label">訂購人姓名 (必填)</label>
                        <input type="text" class="form-control" id="buyerName" name="buyerName" required />
                    </div>
                    <div class="col-md-6">
                        <label for="buyerPhone" class="form-label">聯絡電話 (必填)</label>
                        <input type="tel" class="form-control" id="buyerPhone" name="buyerPhone" required />
                    </div>
                </div>
            </div>


            <!-- 收件人資訊 -->
            <h4 class="my-4">收件人資訊</h4>
            <div class="container border border-secondary p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="recipientName" class="form-label">收件人姓名 (必填)</label>
                        <input type="text" class="form-control" id="recipientName" name="recipientName" required />
                    </div>
                    <div class="col-md-6">
                        <label for="recipientPhone" class="form-label">聯絡電話 (必填)</label>
                        <input type="tel" class="form-control" id="recipientPhone" name="recipientPhone" required />
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="recipientEmail" class="form-label">信箱</label>
                        <input type="email" class="form-control" id="recipientEmail" name="recipientEmail" />
                    </div>
                    <div class="col-md-6">
                        <label for="recipientAddress" class="form-label">地址</label>
                        <input type="text" class="form-control" id="recipientAddress" name="recipientAddress" />
                    </div>
                </div>
            </div>


            <!-- 付款資訊 -->
            <h4 class="my-4">付款資訊</h4>
            <div class="container border border-secondary p-4">
                <div class="mb-3">
                    <label for="paymentMethod" class="form-label">付款方式</label>
                    <select class="form-select" id="paymentMethod" name="paymentMethod" required>
                        <option value="ATM">ATM 轉帳</option>
                        <option value="CreditCard">線上刷卡</option>
                        <option value="LinePay">LINE Pay</option>
                    </select>
                </div>
                <p class="text-muted">
                    寄出訂單後會提供匯款帳號
                </p>
            </div>

            <!-- 商品資訊 (可改成動態生成) -->
            <h4 class="my-4">商品內容及數量</h4>
            <div class="container border border-secondary p-4">
                <div class="mb-3">
                    <p>6021-70/299 舒柔枕 觸感柔軟 (W/R) x 1</p>
                    <p>運費：150 元</p>
                    <p>商品金額：4738 元</p>
                </div>
            </div>

            <!-- 其它資訊 -->

            <h4 class="my-4">其它資訊</h4>
            <div class="container border border-secondary p-4">
                <div class="mb-3">
                    <label for="comments" class="form-label">備註</label>
                    <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                </div>
            </div>

            <!-- 提交按鈕 -->
            <div class="mt-4 justify-content-center text-center">
                <button type="" class="btn btn-primary">
                    完成，送出訂單
                </button>
            </div>
        </form>
    </div>


    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>