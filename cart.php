<?php
session_start();

// 檢查用戶是否登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$memberId = $_SESSION['user_id'];

// 獲取購物車數據
$cartItems = [];
try {
    $response = file_get_contents("http://localhost/new_test/api/cart.php?action=getCartItems&memberId=$memberId");
    $data = json_decode($response, true);
    if ($data['success'] && !empty($data['data'])) {
        $cartItems = $data['data'];
    }
} catch (Exception $e) {
    error_log("獲取購物車數據失敗: " . $e->getMessage());
}

include __DIR__ . "/fixedFile/header.php";
?>

<div class="container mt-3">
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
                                            value="<?= $item['Quantity'] ?>" min="1" data-option-id="<?= $item['OptionID'] ?>">
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
            <a href="checkout.php" class="btn btn-primary mt-3">結帳</a>
        </div>
    <?php endif; ?>
</div>

<?php include "fixedFile/footer.php"; ?>
<script>
    // 頁面載入時立即更新總金額
    document.addEventListener('DOMContentLoaded', function () {
        updateTotalPrice();
    });

    async function loadCartItems() {
        try {
            const response = await fetch(`api/cart.php?action=getCartItems&memberId=<?php echo $_SESSION['user_id']; ?>`);
            const data = await response.json();

            if (data.success) {
                // 清空現有購物車內容
                document.querySelector('tbody').innerHTML = '';
                document.querySelector('.d-md-none').innerHTML = '';

                if (data.data && data.data.length > 0) {
                    // 渲染桌面版表格
                    const tbody = document.querySelector('tbody');
                    data.data.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td style="vertical-align: middle;">
                                <div style="width: 50px; height: 50px; overflow: hidden;">
                                    <img src="${item.image || 'imgs/default-product.jpg'}" alt="商品圖片" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                                </div>
                            </td>
                            <td style="max-width: 450px; white-space: normal; word-break: break-all; vertical-align: middle;">
                                ${item.ProductName || '未命名商品'}
                            </td>
                            <td style="vertical-align: middle;">
                                <div class="d-flex flex-column">
                                    <span>${item.Color || '無顏色'}</span>
                                    ${item.Size != -1 ? `<span class="text-muted">${item.SizeDescription || '無尺寸'}</span>` : ''}
                                </div>
                            </td>
                            <td style="vertical-align: middle;">$${(item.Price || 0).toLocaleString()}</td>
                            <td style="vertical-align: middle;">
                                <div class="input-group" style="max-width: 150px;">
                                    <button class="btn btn-outline-success btn-sm decrease-qty" type="button" data-option-id="${item.OptionID}">-</button>
                                    <input type="number" class="form-control form-control-sm text-center border-success quantity-input" value="${item.Quantity || 1}" min="1" data-option-id="${item.OptionID}">
                                    <button class="btn btn-outline-success btn-sm increase-qty" type="button" data-option-id="${item.OptionID}">+</button>
                                </div>
                            </td>
                            <td style="vertical-align: middle;">$${((item.Price || 0) * (item.Quantity || 1)).toLocaleString()}</td>
                            <td style="vertical-align: middle;">
                                <button class="btn btn-danger btn-sm remove-item" data-option-id="${item.OptionID}">移除</button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // 渲染手機版卡片
                    const mobileContainer = document.querySelector('.d-md-none');
                    data.data.forEach(item => {
                        const card = document.createElement('div');
                        card.className = 'card mb-3';
                        card.innerHTML = `
                            <div class="row g-0">
                                <div class="col-3">
                                    <div style="aspect-ratio: 1; overflow: hidden;">
                                        <img src="${item.image || 'imgs/default-product.jpg'}" alt="商品圖片" style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1" style="word-break: break-all; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            ${item.ProductName || '未命名商品'}
                                        </h6>
                                        <p class="card-text mb-1">
                                            <small class="text-muted">
                                                ${item.Color || '無顏色'}
                                                ${item.Size != -1 ? ' - ' + (item.SizeDescription || '無尺寸') : ''}
                                            </small>
                                        </p>
                                        <p class="card-text mb-2">單價：$${(item.Price || 0).toLocaleString()}</p>
                                        <p class="card-text">小計：$${((item.Price || 0) * (item.Quantity || 1)).toLocaleString()}</p>
                                        <div class="input-group input-group-sm mb-2" style="max-width: 120px;">
                                            <button class="btn btn-outline-success btn-sm decrease-qty" type="button" data-option-id="${item.OptionID}">-</button>
                                            <input type="number" class="form-control form-control-sm text-center border-success quantity-input" value="${item.Quantity || 1}" min="1" data-option-id="${item.OptionID}">
                                            <button class="btn btn-outline-success btn-sm increase-qty" type="button" data-option-id="${item.OptionID}">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 d-flex align-items-center justify-content-center">
                                    <button class="btn btn-danger btn-sm remove-item" data-option-id="${item.OptionID}">移除</button>
                                </div>
                            </div>
                        `;
                        mobileContainer.appendChild(card);
                    });

                    // 重新綁定事件
                    bindCartEvents();
                    updateTotalPrice();
                } else {
                    // 購物車為空的情況
                    document.querySelector('.container').innerHTML = `
                        <div class="alert alert-info">您的購物車是空的</div>
                        <a href="index.php" class="btn btn-primary">繼續購物</a>
                    `;
                }
            } else {
                console.error('獲取購物車失敗:', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', async function () {
            const optionId = this.getAttribute('data-option-id');

            try {
                const response = await fetch('api/cart.php?action=removeFromCart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        memberId: <?php echo $_SESSION['user_id']; ?>,
                        optionId: optionId
                    })
                });
                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    console.error('移除商品失敗:', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });

    // 統一處理數量變更功能
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', async function () {
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

            try {
                const response = await fetch('api/cart.php?action=updateCartQuantity', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        memberId: <?php echo $_SESSION['user_id']; ?>,
                        optionId: optionId,
                        quantity: quantity
                    })
                });
                const data = await response.json();

                if (!data.success) {
                    console.error('更新數量失敗:', data.message);
                    location.reload();
                } else {
                    updateTotalPrice();
                }
            } catch (error) {
                console.error('Error:', error);
            }
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
    async function updateTotalPrice() {
        try {
            const response = await fetch(`api/cart.php?action=getCartTotal&memberId=<?php echo $_SESSION['user_id']; ?>`);
            const data = await response.json();

            if (data.success) {
                document.querySelector('h4').textContent = '總金額: $' + data.total.toLocaleString();
            } else {
                console.error('獲取總金額失敗:', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
</script>