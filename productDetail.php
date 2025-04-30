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
    <link rel="stylesheet" href="css/webStruct.css">
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
    <div class="container text-center border-bottom border-black my-3  flex-grow-1 pt-5">
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
                                <button
                                    class="btn btn-outline-success me-1 mb-1 size-option"><?php echo $sizeOption['sizeDescription']; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="fw-bold mb-1">顏色</p>
                        <div>
                            <?php foreach ($product['sizeOptions'][0]['colors'] as $colorOption): ?>
                                <button
                                    class="btn btn-outline-success me-1 mb-1 color-option"><?php echo $colorOption['color']; ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="fw-bold mb-1">數量</p>
                        <div class="input-group" style="max-width: 150px;">
                            <button class="btn btn-outline-success" type="button" id="decrease-qty">-</button>
                            <input type="number" class="form-control text-center border-success" value="1" min="1"
                                id="quantity">
                            <button class="btn btn-outline-success" type="button" id="increase-qty">+</button>
                        </div>
                    </div>

                    <!-- 新增價格區塊 -->
                    <div class="mb-4">
                        <p class="fw-bold mb-1">價格</p>
                        <div>
                            <span class="fs-6 text-decoration-line-through" id="original-price">
                                NT$ <?php echo number_format($product['sizeOptions'][0]['price'] * 1.2, 0); ?>
                            </span>
                            <div class="h3 text-danger fw-bold" id="product-price">
                                NT$ <?php echo number_format($product['sizeOptions'][0]['price'], 0); ?>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary mt-3" id="add-to-cart">加入購物車</button>
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

        // 自動選中第一個尺寸和顏色
        document.addEventListener('DOMContentLoaded', function () {
            const sizeOptions = <?php echo json_encode($product['sizeOptions']); ?>;
            const firstSize = document.querySelector('.size-option:not(:disabled)');
            if (firstSize) {
                firstSize.classList.remove('btn-outline-success');
                firstSize.classList.add('btn-success');
            }

            const firstColor = document.querySelector('.color-option:not(:disabled)');
            if (firstColor) {
                firstColor.classList.remove('btn-outline-success');
                firstColor.classList.add('btn-success');
            }
        });

        // 尺寸和顏色選擇功能
        document.querySelectorAll('.size-option').forEach(button => {
            button.addEventListener('click', function () {
                document.querySelectorAll('.size-option').forEach(btn => {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-success');
                });
                this.classList.remove('btn-outline-success');
                this.classList.add('btn-success');

                // 根據選擇的尺寸更新顏色選項
                const sizeIndex = Array.from(document.querySelectorAll('.size-option')).indexOf(this);
                const sizeOptions = <?php echo json_encode($product['sizeOptions']); ?>;
                const colorButtonsContainer = document.querySelector('.color-option').parentNode; // 只獲取按鈕容器
                colorButtonsContainer.innerHTML = ''; // 只清空按鈕

                // 更新價格顯示
                const currentPrice = sizeOptions[sizeIndex].price;
                const originalPrice = currentPrice * 1.2;
                document.getElementById('product-price').textContent = 'NT$ ' + currentPrice.toLocaleString('en-US', { maximumFractionDigits: 0 });
                document.getElementById('original-price').textContent = 'NT$ ' + originalPrice.toLocaleString('en-US', { maximumFractionDigits: 0 });

                sizeOptions[sizeIndex].colors.forEach(colorOption => {
                    const colorBtn = document.createElement('button');
                    colorBtn.className = 'btn btn-outline-success me-1 mb-1 color-option';
                    colorBtn.textContent = colorOption.color;

                    // 如果庫存為0，設置為disabled
                    if (colorOption.stock <= 0) {
                        colorBtn.disabled = true;
                        colorBtn.classList.add('disabled');
                    }

                    colorBtn.addEventListener('click', function () {
                        document.querySelectorAll('.color-option').forEach(btn => {
                            btn.classList.remove('btn-success');
                            btn.classList.add('btn-outline-success');
                        });
                        this.classList.remove('btn-outline-success');
                        this.classList.add('btn-success');
                    });

                    colorButtonsContainer.appendChild(colorBtn);
                });

                // 自動選中第一個可用的顏色
                const firstAvailableColor = colorButtonsContainer.querySelector('.color-option:not(:disabled)');
                if (firstAvailableColor) {
                    firstAvailableColor.classList.remove('btn-outline-success');
                    firstAvailableColor.classList.add('btn-success');
                }
            });
        });

        document.querySelectorAll('.color-option').forEach(button => {
            button.addEventListener('click', function () {
                document.querySelectorAll('.color-option').forEach(btn => {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-success');
                });
                this.classList.remove('btn-outline-success');
                this.classList.add('btn-success');
            });
        });

        document.getElementById('add-to-cart').addEventListener('click', function () {
            // 檢查是否已登入
            const memberId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; ?>;
            if (memberId === 0) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-warning alert-dismissible fade show position-fixed top-50 start-50 translate-middle text-center';
                alertDiv.setAttribute('role', 'alert');
                alertDiv.style.zIndex = '9999';
                alertDiv.style.minWidth = '200px';
                alertDiv.style.maxWidth = '80%';
                alertDiv.style.width = 'auto';
                alertDiv.style.padding = '15px 30px';
                alertDiv.innerHTML = `
                    <div class="d-flex justify-content-center align-items-center">
                        請先登入後再進行購物
                    </div>
                `;
                document.body.appendChild(alertDiv);
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alertDiv);
                    bsAlert.close();
                }, 1500);
                return;
            }

            const quantity = parseInt(document.getElementById('quantity').value);
            const selectedSize = document.querySelector('.size-option.btn-success');
            const selectedColor = document.querySelector('.color-option.btn-success');

            // 獲取選中的尺寸和顏色對應的optionID
            const sizeIndex = Array.from(document.querySelectorAll('.size-option')).indexOf(selectedSize);
            const colorIndex = Array.from(document.querySelectorAll('.color-option')).indexOf(selectedColor);
            const sizeOptions = <?php echo json_encode($product['sizeOptions']); ?>;
            const optionId = sizeOptions[sizeIndex].colors[colorIndex].optionId;

            fetch('php/APIs.php?action=addToCart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    memberId: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; ?>,
                    optionId: optionId,
                    quantity: quantity
                })
            })
                .then(response => response.json())
                .then(data => {
                    // 創建Bootstrap alert元素
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-50 start-50 translate-middle text-center';
                    alertDiv.setAttribute('role', 'alert');
                    alertDiv.style.zIndex = '9999';
                    alertDiv.style.minWidth = '200px';
                    alertDiv.style.maxWidth = '80%';
                    alertDiv.style.width = 'auto';
                    alertDiv.style.padding = '15px 30px';
                    alertDiv.innerHTML = `
                        <div class="d-flex justify-content-center align-items-center">
                            ${data.success ? '已加入購物車' : '加入購物車失敗: ' + data.message}
                        </div>
                    `;
                    // 添加到body
                    document.body.appendChild(alertDiv);
                    // 1.5秒後自動消失
                    setTimeout(() => {
                        const bsAlert = new bootstrap.Alert(alertDiv);
                        bsAlert.close();
                    }, 1500);
                })
                .catch(error => {
                    console.error('Error:', error);
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-50 start-50 translate-middle text-center';
                    alertDiv.setAttribute('role', 'alert');
                    alertDiv.style.zIndex = '9999';
                    alertDiv.style.minWidth = '200px';
                    alertDiv.style.maxWidth = '80%';
                    alertDiv.style.width = 'auto';
                    alertDiv.style.padding = '15px 30px';
                    alertDiv.innerHTML = `
                        <div class="d-flex justify-content-center align-items-center">
                            發生錯誤，請稍後再試
                        </div>
                    `;
                    document.body.appendChild(alertDiv);
                    setTimeout(() => {
                        const bsAlert = new bootstrap.Alert(alertDiv);
                        bsAlert.close();
                    }, 1500);
                });
        });
    </script>
</body>

</html>