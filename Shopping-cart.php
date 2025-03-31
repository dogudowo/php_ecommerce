<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="icon" href="./imgs/webimg.ico" />
    <title>歐印精品-購物車</title>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
        <h1>購物車</h1>
    </div>

    <div class="container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" class="text-nowrap">#</th>
                    <th scope="col" class="text-nowrap">商品名稱</th>
                    <th scope="col" class="text-nowrap">單價</th>
                    <th scope="col" class="text-nowrap">數量</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">1</th>
                    <td>6021-70/29吋升級快閃經典銀紅(WR)</td>
                    <td>$7,680</td>
                    <td>1</td> <!-- 做動態增加減少 -->
                </tr>
                <tr>
                    <th scope="row">2</th> <!--數量增加-->
                    <td>6021-70/29吋升級快閃經典銀紅(WR)</td>
                    <td>$7,680</td>
                    <td>1</td> <!-- 做動態增加減少 -->
                </tr>
            </tbody>
            <caption>
                <p class="text-end h5">
                    預計總價：$15,360
                </p>
            </caption>
        </table>
    </div>

    <div class="container justify-content-center">
        <form method="POST" action="order.php">
            <div class="row mb-3">
                <label for="colFormLabel" class="col-sm-2 col-form-label text-nowrap">優惠碼</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="colFormLabel" placeholder="請輸入優惠碼" name="cupid">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" src="order.php">結帳</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>