<?php
// 接收產品ID
$product_id = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 1;

// 直接調用API函數獲取產品數據
require_once __DIR__ . '/php/APIs.php';
$product = getProductById($product_id);

// 檢查API響應是否成功
if (!$product) {
    // 包含404頁面並結束當前腳本執行
    include __DIR__ . '/404.php';
    exit;
}

// 確保產品有 success 鍵
$product['success'] = true;
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
    <title>歐印精品-<?php echo $product['ProductName']; ?></title>
</head>

<body>
    <?php include "header.php"; ?>
    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" img-fluid>
        <h1 class="h1"><?php echo $product['ProductName']; ?></h1>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-none">
                <img src="<?php echo $product['mainImage']; ?>" alt="product image" class="img-fluid">
            </div>
            <div class="col-lg-6 col-12">
                <div class="container mb-4">
                    <p><?php echo $product['Description']; ?></p>
                </div>
                <div class="container">
                    <div class="mb-4">
                        <p class="fw-bold mb-1">尺寸</p>
                        <div>
                            <?php foreach ($product['sizeOptions'] as $sizeOption): ?>
                                <button class="btn btn-outline-dark me-1 mb-1"><?php echo $sizeOption['size']; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="fw-bold mb-1">顏色</p>
                        <div>
                            <?php foreach ($product['sizeOptions'][0]['colors'] as $colorOption): ?>
                                <button class="btn btn-outline-dark me-1 mb-1"><?php echo $colorOption['color']; ?></button>
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
                <?php if (isset($product['galleryImages']) && is_array($product['galleryImages'])): ?>
                    <?php foreach ($product['galleryImages'] as $img): ?>
                        <div class="col-12 col-lg-9 mx-auto">
                            <img src="<?php echo $img['url']; ?>" alt="產品圖片" class="img-fluid">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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