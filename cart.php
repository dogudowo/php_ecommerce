<?php
session_start();

// 檢查用戶是否登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$memberId = $_SESSION['user_id'];

include __DIR__ . "/fixedFile/header.php";
?>

<div class="container mt-3">
    <h2 class="mb-4">我的購物車</h2>
    <div id="cart-container">
        <!-- 購物車內容將通過 AJAX 載入 -->
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">載入中...</span>
            </div>
            <p>載入購物車內容中...</p>
        </div>
    </div>
</div>

<script>
    // 頁面載入時立即獲取購物車數據
    document.addEventListener('DOMContentLoaded', function () {
        loadCartItems();
    });

    // 載入購物車商品
    async function loadCartItems() {
        try {
            const response = await fetch(`api/cart.php?action=getCartItems&memberId=<?php echo $_SESSION['user_id']; ?>`);
            const data = await response.json();
            const cartContainer = document.getElementById('cart-container');

            if (data.success) {
                if (data.data && data.data.length > 0) {
                    let desktopView = `
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
                    `;

                    let mobileView = `<div class="d-md-none">`;

                    // 計算初始總金額
                    let totalAmount = 0;

                    // 渲染商品列表
                    data.data.forEach(item => {
                        const itemTotal = item.Price * item.Quantity;
                        totalAmount += itemTotal;

                        // 桌面版表格行
                        desktopView += `
                            <tr data-option-id="${item.OptionID}" data-price="${item.Price}">
                                <td style="vertical-align: middle;">
                                    <div style="width: 50px; height: 50px; overflow: hidden;">
                                        <img src="imgs/products/${item.ProductID}/main.jpg" alt="商品圖片"
                                            style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
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
                                <td style="vertical-align: middle;" class="item-price">$${(item.Price || 0).toLocaleString()}</td>
                                <td style="vertical-align: middle;">
                                    <div class="input-group" style="max-width: 150px;">
                                        <button class="btn btn-outline-success btn-sm decrease-qty" type="button"
                                            data-option-id="${item.OptionID}">-</button>
                                        <input type="number"
                                            class="form-control form-control-sm text-center border-success quantity-input"
                                            value="${item.Quantity || 1}" min="1" data-option-id="${item.OptionID}">
                                        <button class="btn btn-outline-success btn-sm increase-qty" type="button"
                                            data-option-id="${item.OptionID}">+</button>
                                    </div>
                                </td>
                                <td style="vertical-align: middle;" class="item-subtotal">$${((item.Price || 0) * (item.Quantity || 1)).toLocaleString()}</td>
                                <td style="vertical-align: middle;">
                                    <button class="btn btn-danger btn-sm remove-item"
                                        data-option-id="${item.OptionID}">移除</button>
                                </td>
                            </tr>
                        `;

                        // 手機版卡片
                        mobileView += `
                            <div class="card mb-3" data-option-id="${item.OptionID}" data-price="${item.Price}">
                                <div class="row g-0">
                                    <div class="col-3">
                                        <div style="aspect-ratio: 1; overflow: hidden;">
                                            <img src="imgs/products/${item.ProductID}/main.jpg" alt="商品圖片"
                                                style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                                        </div>
                                    </div>
                                    <div class="col-7">
                                        <div class="card-body p-2">
                                            <h6 class="card-title mb-1"
                                                style="word-break: break-all; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                ${item.ProductName || '未命名商品'}
                                            </h6>
                                            <p class="card-text mb-1">
                                                <small class="text-muted">
                                                    ${item.Color || '無顏色'}
                                                    ${item.Size != -1 ? ' - ' + (item.SizeDescription || '無尺寸') : ''}
                                                </small>
                                            </p>
                                            <p class="card-text mb-2 item-price">單價：$${(item.Price || 0).toLocaleString()}</p>
                                            <p class="card-text item-subtotal">小計：$${((item.Price || 0) * (item.Quantity || 1)).toLocaleString()}</p>
                                            <div class="input-group input-group-sm mb-2" style="max-width: 120px;">
                                                <button class="btn btn-outline-success btn-sm decrease-qty" type="button"
                                                    data-option-id="${item.OptionID}">-</button>
                                                <input type="number"
                                                    class="form-control form-control-sm text-center border-success quantity-input"
                                                    value="${item.Quantity || 1}" min="1" data-option-id="${item.OptionID}">
                                                <button class="btn btn-outline-success btn-sm increase-qty" type="button"
                                                    data-option-id="${item.OptionID}">+</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-2 d-flex align-items-center justify-content-center">
                                        <button class="btn btn-danger btn-sm remove-item"
                                            data-option-id="${item.OptionID}">移除</button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    desktopView += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;

                    mobileView += `</div>`;

                    // 總金額和結帳按鈕
                    const totalSection = `
                        <div class="text-end mt-4">
                            <h4 class="total-price">總金額: $${totalAmount.toLocaleString()}</h4>
                            <a href="checkout.php" class="btn btn-primary mt-3">結帳</a>
                        </div>
                    `;

                    // 更新購物車容器
                    cartContainer.innerHTML = desktopView + mobileView + totalSection;

                    // 綁定事件
                    bindCartEvents();
                } else {
                    // 購物車為空的情況
                    cartContainer.innerHTML = `
                        <div class="alert alert-info">您的購物車是空的</div>
                        <a href="index.php" class="btn btn-primary">繼續購物</a>
                    `;
                }
            } else {
                console.error('獲取購物車失敗:', data.message);
                cartContainer.innerHTML = `
                    <div class="alert alert-danger">獲取購物車數據失敗</div>
                    <a href="index.php" class="btn btn-primary">返回首頁</a>
                `;
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('cart-container').innerHTML = `
                <div class="alert alert-danger">發生錯誤，請稍後再試</div>
                <a href="index.php" class="btn btn-primary">返回首頁</a>
            `;
        }
    }

    // 綁定購物車事件
    function bindCartEvents() {
        // 綁定移除商品按鈕
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', removeCartItem);
        });

        // 綁定數量輸入框
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', updateItemQuantity);
        });

        // 綁定加減按鈕
        document.querySelectorAll('.decrease-qty, .increase-qty').forEach(button => {
            button.addEventListener('click', handleQuantityButton);
        });
    }

    // 移除購物車商品
    async function removeCartItem() {
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
                // 重新載入購物車
                loadCartItems();
            } else {
                console.error('移除商品失敗:', data.message);
                alert('移除商品失敗: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('發生錯誤，請稍後再試');
        }
    }

    // 更新商品數量
    async function updateItemQuantity() {
        const optionId = this.getAttribute('data-option-id');
        const quantity = parseInt(this.value);

        // 獲取商品元素
        let itemElement;
        if (this.closest('tr')) {
            itemElement = this.closest('tr');
        } else {
            itemElement = this.closest('.card');
        }

        // 獲取價格
        const price = parseFloat(itemElement.getAttribute('data-price'));

        // 更新小計
        updateSubtotal(itemElement, price, quantity);

        // 更新總金額
        updateTotalAmount();

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
                alert('更新數量失敗: ' + data.message);
                loadCartItems(); // 重新載入以恢復正確狀態
            }
        } catch (error) {
            console.error('Error:', error);
            alert('發生錯誤，請稍後再試');
            loadCartItems(); // 重新載入以恢復正確狀態
        }
    }

    // 處理加減按鈕
    function handleQuantityButton() {
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
    }

    // 更新小計
    function updateSubtotal(element, price, quantity) {
        const subtotal = price * quantity;
        const subtotalElement = element.querySelector('.item-subtotal');

        if (subtotalElement) {
            if (subtotalElement.textContent.includes('小計')) {
                subtotalElement.textContent = `小計：$${subtotal.toLocaleString()}`;
            } else {
                subtotalElement.textContent = `$${subtotal.toLocaleString()}`;
            }
        }
    }

    // 更新總金額
    function updateTotalAmount() {
        let total = 0;

        // 計算桌面版總金額
        document.querySelectorAll('tbody tr').forEach(row => {
            const price = parseFloat(row.getAttribute('data-price'));
            const quantity = parseInt(row.querySelector('.quantity-input').value);
            total += price * quantity;
        });

        // 如果沒有桌面版，計算手機版總金額
        if (total === 0) {
            document.querySelectorAll('.card[data-option-id]').forEach(card => {
                const price = parseFloat(card.getAttribute('data-price'));
                const quantity = parseInt(card.querySelector('.quantity-input').value);
                total += price * quantity;
            });
        }

        // 更新總金額顯示
        const totalElement = document.querySelector('.total-price');
        if (totalElement) {
            totalElement.textContent = `總金額: $${total.toLocaleString()}`;
        }
    }
</script>

<?php include "fixedFile/footer.php"; ?>