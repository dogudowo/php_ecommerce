<?php
session_start();
require_once __DIR__ . '/php/APIs.php';

// 檢查用戶是否登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$memberId = $_SESSION['user_id'];
$cartItems = getCartItems($memberId);
$total = 0;

// 計算總金額
foreach ($cartItems as $item) {
    $total += $item['Price'] * $item['Quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>購物車 - 歐印精品</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/webStruct.css">
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container my-5  flex-grow-1 pt-5">
        <h2 class="mb-4">我的購物車</h2>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info">您的購物車是空的</div>
            <a href="index.php" class="btn btn-primary">繼續購物</a>
        <?php else: ?>
            <div class="d-none d-md-block">
                <!-- 桌面版表格 -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 80px; vertical-align: middle;">商品圖片</th>
                                <th style="width: 450px; white-space: normal; vertical-align: middle;">商品名稱</th>
                                <th style="min-width: 100px; vertical-align: middle;">規格</th>
                                <th style="min-width: 80px; vertical-align: middle;">單價</th>
                                <th style="min-width: 150px; vertical-align: middle;">數量</th>
                                <th style="min-width: 80px; vertical-align: middle;">小計</th>
                                <th style="width: 80px; vertical-align: middle;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td style="vertical-align: middle;">
                                        <div style="width: 50px; height: 50px; overflow: hidden;">
                                            <img src="<?php echo $item['image']; ?>" alt="商品圖片"
                                                style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                                        </div>
                                    </td>
                                    <td
                                        style="max-width: 450px; white-space: normal; word-break: break-all; vertical-align: middle;">
                                        <?php echo htmlspecialchars($item['ProductName']); ?>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div class="d-flex flex-column">
                                            <span><?= htmlspecialchars($item['Color']) ?></span>
                                            <?php if ($item['Size'] != -1): ?>
                                                <span class="text-muted"><?= htmlspecialchars($item['SizeDescription']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">$<?= number_format($item['Price']) ?></td>
                                    <td style="vertical-align: middle;">
                                        <div class="input-group" style="max-width: 150px;">
                                            <button class="btn btn-outline-success btn-sm decrease-qty" type="button"
                                                data-option-id="<?= $item['OptionID'] ?>">-</button>
                                            <input type="number"
                                                class="form-control form-control-sm text-center border-success quantity-input"
                                                value="<?= $item['Quantity'] ?>" min="1"
                                                data-option-id="<?= $item['OptionID'] ?>">
                                            <button class="btn btn-outline-success btn-sm increase-qty" type="button"
                                                data-option-id="<?= $item['OptionID'] ?>">+</button>
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        $<?= number_format($item['Price'] * $item['Quantity']) ?></td>
                                    <td style="vertical-align: middle;">
                                        <button class="btn btn-danger btn-sm remove-item"
                                            data-option-id="<?= $item['OptionID'] ?>">移除</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 手機版卡片式設計 -->
            <div class="d-md-none">
                <?php foreach ($cartItems as $item): ?>
                    <div class="card mb-3">
                        <div class="row g-0">
                            <div class="col-3">
                                <div style="aspect-ratio: 1; overflow: hidden;">
                                    <img src="<?php echo $item['image']; ?>" alt="商品圖片"
                                        style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="card-body p-2">
                                    <h6 class="card-title mb-1"
                                        style="word-break: break-all; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($item['ProductName']); ?>
                                    </h6>
                                    <p class="card-text mb-1">
                                        <small class="text-muted">
                                            <?= htmlspecialchars($item['Color']) ?>
                                            <?= $item['Size'] != -1 ? ' - ' . htmlspecialchars($item['SizeDescription']) : '' ?>
                                        </small>
                                    </p>
                                    <p class="card-text mb-2">單價：$<?= number_format($item['Price']) ?></p>
                                    <p class="card-text">小計：$<?= number_format($item['Price'] * $item['Quantity']) ?></p>
                                    <div class="input-group input-group-sm mb-2" style="max-width: 120px;">
                                        <button class="btn btn-outline-success btn-sm decrease-qty" type="button"
                                            data-option-id="<?= $item['OptionID'] ?>">-</button>
                                        <input type="number"
                                            class="form-control form-control-sm text-center border-success quantity-input"
                                            value="<?= $item['Quantity'] ?>" min="1" data-option-id="<?= $item['OptionID'] ?>">
                                        <button class="btn btn-outline-success btn-sm increase-qty" type="button"
                                            data-option-id="<?= $item['OptionID'] ?>">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2 d-flex align-items-center justify-content-center">
                                <button class="btn btn-danger btn-sm remove-item"
                                    data-option-id="<?= $item['OptionID'] ?>">移除</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-end mt-4">
                <h4>總金額: $<?= number_format($total) ?></h4>
                <a href="checkout.php" class="btn btn-primary btn-lg mt-3">結帳</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function () {
                const optionId = this.getAttribute('data-option-id');

                fetch('php/APIs.php?action=removeFromCart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        memberId: <?php echo $_SESSION['user_id']; ?>,
                        optionId: optionId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();  // 確保這裡有強制刷新
                        } else {
                            alert('移除商品失敗: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('發生錯誤，請稍後再試');
                    });
            });
        });

        // 統一處理數量變更功能
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function () {
                const optionId = this.getAttribute('data-option-id');
                const quantity = parseInt(this.value);
                let price, subtotalElement;

                if (this.closest('tr')) { // 桌面版
                    price = parseFloat(this.closest('tr').querySelector('td:nth-child(4)').textContent.replace('$', '').replace(',', ''));
                    subtotalElement = this.closest('tr').querySelector('td:nth-child(6)');
                    subtotalElement.textContent = '$' + (price * quantity).toLocaleString();
                } else { // 手機版
                    const card = this.closest('.card');
                    price = parseFloat(card.querySelector('p.card-text.mb-2').textContent.replace('單價：$', '').replace(',', ''));
                    subtotalElement = card.querySelector('p.card-text:not(.mb-1):not(.mb-2)');
                    subtotalElement.textContent = '小計：$' + (price * quantity).toLocaleString();
                }

                fetch('php/APIs.php?action=updateCartQuantity', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        memberId: <?php echo $_SESSION['user_id']; ?>,
                        optionId: optionId,
                        quantity: quantity
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert('更新數量失敗: ' + data.message);
                            location.reload();
                        } else {
                            updateTotalPrice();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('發生錯誤，請稍後再試');
                    });
            });
        });

        // 統一處理加減按鈕
        document.querySelectorAll('.decrease-qty, .increase-qty').forEach(button => {
            button.addEventListener('click', function () {
                const input = this.classList.contains('decrease-qty')
                    ? this.nextElementSibling
                    : this.previousElementSibling;
                let value = parseInt(input.value);

                if (this.classList.contains('decrease-qty') && value > 1) {
                    input.value = value - 1;
                    input.dispatchEvent(new Event('change'));
                } else if (this.classList.contains('increase-qty')) {
                    input.value = value + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        });

        // 更新總價函數
        function updateTotalPrice() {
            let total = 0;

            // 桌面版
            document.querySelectorAll('td:nth-child(6)').forEach(td => {
                total += parseFloat(td.textContent.replace('$', '').replace(',', ''));
            });

            // 手機版
            document.querySelectorAll('p.card-text:not(.mb-1):not(.mb-2)').forEach(p => {
                const text = p.textContent.replace('小計：$', '');
                total += parseFloat(text.replace(',', ''));
            });

            document.querySelector('h4').textContent = '總金額: $' + total.toLocaleString();
        }
    </script>
</body>

</html>