<?php
session_start();

// 檢查用戶是否登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$memberId = $_SESSION['user_id'];

// 獲取會員資訊
$memberInfo = [];
require_once __DIR__ . '/api/db.php';
try {
    $db = getDBConnection();

    $stmt = $db->prepare("SELECT Name, Phone, City, Address FROM Member WHERE MemberID = ?");
    $stmt->execute([$memberId]);
    $memberInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$memberInfo) {
        throw new Exception("找不到會員資訊");
    }

    // 測試輸出會員資訊
    error_log("獲取到的會員資訊: " . print_r($memberInfo, true));
} catch (Exception $e) {
    error_log("獲取會員資訊失敗: " . $e->getMessage());
    $memberInfo = ['Name' => '', 'Phone' => '', 'City' => '', 'Address' => ''];
}

// 獲取購物車數據
$cartItems = [];
$total = 0;

include __DIR__ . "/header.php";
?>

<div class="container mt-3">
    <h2 class="mb-4">結帳</h2>

    <div id="cartContainer">
        <!-- 購物車內容將通過AJAX加載 -->
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>正在加載購物車資訊...</p>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script>
    // 使用AJAX獲取購物車數據
    async function loadCartData() {
        try {
            // 獲取購物車商品
            const response = await fetch(`api/cart.php?action=getCartItems&memberId=<?php echo $memberId; ?>`);
            const data = await response.json();

            // 獲取總金額
            const totalResponse = await fetch(`api/cart.php?action=getCartTotal&memberId=<?php echo $memberId; ?>`);
            const totalData = await totalResponse.json();

            // 渲染購物車內容
            renderCart(data, totalData);
        } catch (error) {
            console.error('獲取購物車數據失敗:', error);
            document.getElementById('cartContainer').innerHTML = `
                <div class="alert alert-danger">獲取購物車數據失敗，請稍後再試</div>
                <a href="index.php" class="btn btn-primary">返回首頁</a>
            `;
        }
    }

    // 渲染購物車內容
    function renderCart(data, totalData) {
        const cartContainer = document.getElementById('cartContainer');

        if (!data.success || !data.data || data.data.length === 0) {
            cartContainer.innerHTML = `
                <div class="alert alert-info">您的購物車是空的</div>
                <a href="index.php" class="btn btn-primary">繼續購物</a>
            `;
            return;
        }

        const cartItems = data.data;
        const total = totalData.success ? totalData.total : 0;

        let cartHTML = `
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">購物車商品</h5>
                    </div>
                    <div class="card-body">
        `;

        cartItems.forEach(item => {
            cartHTML += `
                <div class="row mb-3">
                    <div class="col-3">
                        <img src="imgs/products/${item.ProductID}/main.jpg" class="img-fluid" alt="商品圖片">
                    </div>
                    <div class="col-6">
                        <h6>${item.ProductName}</h6>
                        <p class="mb-1">
                            <small class="text-muted">
                                ${item.Color}
                                ${item.Size != -1 ? ' - ' + item.SizeDescription : ''}
                            </small>
                        </p>
                        <p class="mb-1">數量: ${item.Quantity}</p>
                        <p class="mb-0">單價: $${numberFormat(item.Price)}</p>
                    </div>
                    <div class="col-3 text-end">
                        <p class="fw-bold">$${numberFormat(item.Price * item.Quantity)}</p>
                    </div>
                </div>
            `;
        });

        cartHTML += `
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">收件人資訊</h5>
                    </div>
                    <div class="card-body">
                        <form id="checkoutForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">收件人姓名</label>
                                <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($memberInfo['Name'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">聯絡電話</label>
                                <input type="tel" class="form-control" id="phone" value="<?php echo htmlspecialchars($memberInfo['Phone'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">城市</label>
                                <input type="text" class="form-control" id="city" value="<?php echo htmlspecialchars($memberInfo['City'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">收件地址</label>
                                <input type="text" class="form-control" id="address" value="<?php echo htmlspecialchars($memberInfo['Address'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="mb-3">
                                <label for="note" class="form-label">備註</label>
                                <textarea class="form-control" id="note" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">訂單摘要</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>商品總計</span>
                            <span>$${numberFormat(total)}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>運費</span>
                            <span>$0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>總金額</span>
                            <span>$${numberFormat(total)}</span>
                        </div>
                        <button id="submitOrder" class="btn btn-primary w-100 mt-3">確認結帳</button>
                    </div>
                </div>
            </div>
        </div>
        `;

        cartContainer.innerHTML = cartHTML;

        // 重新綁定提交訂單事件
        bindSubmitOrderEvent();
    }

    // 顯示庫存不足的modal
    function showOutOfStockModal(outOfStockItems) {
        const modalHTML = `
        <div class="modal fade" id="outOfStockModal" tabindex="-1" aria-labelledby="outOfStockModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="outOfStockModalLabel">庫存不足</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>以下商品庫存不足，請選擇處理方式：</p>
                        <ul class="list-group mb-3">
                            ${outOfStockItems.map(item => `
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-3">
                                            <img src="imgs/products/${item.ProductID}/main.jpg" class="img-fluid" alt="商品圖片">
                                        </div>
                                        <div class="col-6">
                                            <h6>${item.ProductName}</h6>
                                            <p class="mb-1">
                                                <small class="text-muted">
                                                    ${item.Color}
                                                    ${item.Size != -1 ? ' - ' + item.SizeDescription : ''}
                                                </small>
                                            </p>
                                            <p class="mb-1">數量: ${item.Quantity}</p>
                                            <p class="mb-0">庫存: ${item.Stock}</p>
                                        </div>
                                    </div>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                        <button type="button" class="btn btn-danger" id="removeOutOfStockBtn">移除缺貨商品</button>
                        <button type="button" class="btn btn-primary" id="backToCartBtn">返回購物車</button>
                    </div>
                </div>
            </div>
        </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('outOfStockModal'));
        modal.show();

        // 綁定modal按鈕事件
        document.getElementById('removeOutOfStockBtn').addEventListener('click', async () => {
            try {
                // 逐一移除每個缺貨商品
                for (const item of outOfStockItems) {
                    const response = await fetch('api/cart.php?action=removeFromCart', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            memberId: <?php echo $memberId; ?>,
                            optionId: item.OptionID
                        })
                    })

                    const data = await response.json();
                    if (data.success) {
                        showAlert('已成功移除缺貨商品', 'success');
                        modal.hide();
                        loadCartData();
                    } else {
                        const errorMsg = data.message || '移除缺貨商品失敗';
                        showAlert(`移除缺貨商品失敗: ${errorMsg}`, 'danger');
                        console.error('移除缺貨商品錯誤:', data);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('移除缺貨商品失敗，請稍後再試', 'danger');
            }
        });

        document.getElementById('backToCartBtn').addEventListener('click', () => {
            modal.hide();
            window.location.href = 'cart.php';
        });

        // modal關閉時移除DOM元素
        document.getElementById('outOfStockModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('outOfStockModal').remove();
        });
    }

    // 綁定提交訂單事件
    function bindSubmitOrderEvent() {
        const submitOrder = document.getElementById('submitOrder');
        if (submitOrder) {
            submitOrder.addEventListener('click', async function () {
                const name = document.getElementById('name').value;
                const phone = document.getElementById('phone').value;
                const city = document.getElementById('city').value;
                const address = document.getElementById('address').value;
                const note = document.getElementById('note').value;

                if (!name || !phone || !city || !address) {
                    showAlert('請填寫完整的收件人資訊', 'danger');
                    return;
                }

                try {
                    // 先檢查庫存
                    const stockResponse = await fetch('api/cart.php?action=checkStock', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            memberId: <?php echo $memberId; ?>
                        })
                    });

                    const stockData = await stockResponse.json();

                    if (!stockData.success) {
                        showAlert('檢查庫存失敗', 'danger');
                        return;
                    }

                    if (stockData.outOfStockItems && stockData.outOfStockItems.length > 0) {
                        showOutOfStockModal(stockData.outOfStockItems);
                        return;
                    }

                    // 庫存充足，創建訂單
                    const response = await fetch('api/order.php?action=createOrder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            memberId: <?php echo $memberId; ?>,
                            name: name,
                            phone: phone,
                            address: address + (city ? ', ' + city : ''),
                            note: note
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert('訂單創建成功！', 'success');
                        setTimeout(() => {
                            window.location.href = 'myOrder.php';
                        }, 500);
                    } else {
                        showAlert(`訂單提交失敗: ${data.message || '未知錯誤'}`, 'danger');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('訂單提交失敗，請稍後再試', 'danger');
                }
            });
        }
    }

    // 顯示提示訊息
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-50 start-50 translate-middle-x mt-3`;
        alertDiv.innerHTML = `
            <strong>${message}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);

        // 自動關閉
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    // 格式化數字
    function numberFormat(number) {
        return new Intl.NumberFormat().format(number);
    }
    // 頁面加載時獲取購物車數據
    document.addEventListener('DOMContentLoaded', loadCartData);
</script>