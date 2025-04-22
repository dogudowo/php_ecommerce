<?php
// 接收產品ID
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : 'T6927';

// 產品數據 (在實際應用中，這些數據可能來自資料庫)
$products = [
    'T6927' => [
        'title' => 'Haugas All-in 箱',
        'description' => 'Haugas All-in 箱 #歐印箱 #全能箱，只有你沒想到的沒有我們做不到的！所有功能齊備一箱 1️⃣秤重⋯ 2️⃣煞車⋯ 3️⃣杯架⋯ 4️⃣密碼鎖⋯ 5️⃣細鋁框⋯ 6️⃣日乃本飛機輪⋯ 7️⃣乾濕分離收納⋯⋯⋯⋯ 8️⃣還有最重要的保固 #我知道你缺一個全能萬用箱',
        'sizes' => ['20吋', '24吋', '28吋'],
        'colors' => ['黑色', '銀色', '金色', '玫瑰金'],
        'main_img' => 'imgs/products/T6927/main.jpg',
        'gallery' => [
            'imgs/products/T6927/T6927-1.jpg',
            'imgs/products/T6927/T6927-2.jpg',
            'imgs/products/T6927/T6927-3.jpg',
            'imgs/products/T6927/T6927-4.jpg',
            'imgs/products/T6927/T6927-5.jpg'
        ]
    ]
];

// 檢查產品是否存在
if (!array_key_exists($product_id, $products)) {
    $product_id = 'T6927'; // 如果不存在，使用默認產品
}

// 獲取當前產品數據
$product = $products[$product_id];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
    <style>
        html,
        body {
            overflow-x: hidden;
            overflow-y: scroll;
            /* 防止橫向滾動條 */
        }
    </style>
    <title>歐印精品-<?php echo $product['title']; ?></title>
</head>

<body>
    <?php include "header.php"; ?>
    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" img-fluid>
        <h1 class="h1"><?php echo $product_id . ' ' . $product['title']; ?></h1>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-none">
                <img src="<?php echo $product['main_img']; ?>" alt="product image" class="img-fluid">
            </div>
            <div class="col-lg-6 col-12">
                <div class="container mb-4">
                    <p><?php echo $product['description']; ?></p>
                </div>
                <div class="container">
                    <div class="mb-4">
                        <p class="fw-bold mb-1">尺寸</p>
                        <div>
                            <?php foreach ($product['sizes'] as $size): ?>
                                <button class="btn btn-outline-dark me-1 mb-1"><?php echo $size; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="fw-bold mb-1">顏色</p>
                        <div>
                            <?php foreach ($product['colors'] as $color): ?>
                                <button class="btn btn-outline-dark me-1 mb-1"><?php echo $color; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="fw-bold mb-1">數量</p>
                        <div class="input-group" style="max-width: 150px;">
                            <button class="btn btn-outline-dark" type="button" id="decrease-qty">-</button>
                            <input type="number" class="form-control text-center" value="1" min="1" id="quantity">
                            <button class="btn btn-outline-dark" type="button" id="increase-qty">+</button>
                        </div>
                    </div>

                    <button class="btn btn-primary mt-3">加入購物車</button>
                </div>
            </div>

            <div class="row mt-5 col-12 col-lg-9 mx-auto">
                <?php foreach ($product['gallery'] as $img): ?>
                    <div class="d-flex-column text-center">
                        <img src="<?php echo $img; ?>" alt="" class="img-fluid rounded">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ"
        crossorigin="anonymous"></script>
    <script>
        // 數量加減按鈕功能
        document.getElementById('decrease-qty').addEventListener('click', function () {
            var input = document.getElementById('quantity');
            var value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        });

        document.getElementById('increase-qty').addEventListener('click', function () {
            var input = document.getElementById('quantity');
            var value = parseInt(input.value);
            input.value = value + 1;
        });
    </script>
</body>

</html>