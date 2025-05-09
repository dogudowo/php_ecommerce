<?php
// 訂單管理文件

// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../../login.php');
    exit;
}

// 引入訂單管理函數
require_once __DIR__ . '/functions/order/orderFunctions.php';

// 獲取訂單狀態列表
$statusList = getOrderStatusList();
?>

<div class="container-fluid p-0">
    <!-- 訂單管理頁面標題 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cart-check me-2"></i>購買訂單管理</h3>
        <div>

        </div>
    </div>

    <!-- 訂單篩選區 -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="filterStatus" class="form-label">訂單狀態</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">全部狀態</option>
                        <?php foreach ($statusList as $key => $value): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>">
                                <?php echo htmlspecialchars($value); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterDateFrom" class="form-label">開始日期</label>
                    <input type="date" class="form-control" id="filterDateFrom">
                </div>
                <div class="col-md-3">
                    <label for="filterDateTo" class="form-label">結束日期</label>
                    <input type="date" class="form-control" id="filterDateTo">
                </div>
                <div class="col-md-3">
                    <label for="searchOrder" class="form-label">搜尋訂單</label>
                    <input type="text" class="form-control" id="searchOrder" placeholder="訂單ID或會員名稱">
                </div>
            </div>
        </div>
    </div>

    <!-- 訂單列表 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>訂單ID</th>
                            <th>會員名稱</th>
                            <th>訂單日期</th>
                            <th>商品數量</th>
                            <th>總金額</th>
                            <th>狀態</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="orderTableBody">
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">載入中...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


        </div>
    </div>

    <!-- 訂單詳情模態框 -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="orderDetailModalLabel">訂單詳情</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>訂單詳情功能正在開發中，敬請期待。
                    </div>
                    <!-- 訂單詳情內容將在這裡動態加載 -->
                    <div id="orderDetailContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 更新訂單狀態模態框 -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="updateStatusModalLabel">更新訂單狀態</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="updateOrderId">
                        <div class="mb-3">
                            <label for="orderStatus" class="form-label">訂單狀態</label>
                            <select class="form-select" id="orderStatus" required>
                                <?php foreach ($statusList as $key => $value): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>">
                                        <?php echo htmlspecialchars($value); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- 移除備註輸入框 -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="saveStatus">保存更新</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 初始化載入訂單
    $(document).ready(function () {
        loadOrders();

        // 篩選器事件
        $('#filterStatus, #filterDateFrom, #filterDateTo, #searchOrder').on('change input', function () {
            loadOrders();
        });
    });

    // 載入訂單函數
    async function loadOrders() {
        try {
            const status = $('#filterStatus').val();
            const dateFrom = $('#filterDateFrom').val();
            const dateTo = $('#filterDateTo').val();
            const search = $('#searchOrder').val();

            const params = new URLSearchParams();
            if (status) params.append('status', status);
            if (dateFrom) params.append('dateFrom', dateFrom);
            if (dateTo) params.append('dateTo', dateTo);
            if (search) params.append('search', search);

            const response = await fetch(`api/order.php?action=filter&${params.toString()}`);
            const data = await response.json();

            if (!data.success) throw new Error(data.message);

            const $tbody = $('#orderTableBody').empty();
            if (data.orders.length === 0) {
                $tbody.append('<tr><td colspan="7" class="text-center">暫無符合條件的訂單</td></tr>');
                return;
            }

            data.orders.forEach((order) => {
                $tbody.append(`
                    <tr>
                        <td>${order.OrderID}</td>
                        <td>${order.MemberName}</td>
                        <td>${order.OrderDate ? new Date(order.OrderDate).toLocaleDateString() : ''}</td>
                        <td>${order.ItemCount}</td>
                        <td>$${order.TotalAmount ? order.TotalAmount.toLocaleString() : 0}</td>
                        <td>
                            <span class="badge ${getOrderStatusClass(order.Status)}">
                                ${getOrderStatusName(order.Status)}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary view-order"
                                    data-id="${order.OrderID}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success update-status"
                                    data-id="${order.OrderID}"
                                    data-status="${order.Status}">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
            });
        } catch (error) {
            console.error('載入訂單錯誤:', error);
            $('#orderTableBody').html('<tr><td colspan="7" class="text-center text-danger">載入訂單失敗</td></tr>');
        }
    }

    // 使用PHP函數獲取訂單詳情
    async function getOrderDetailsFromBackend(orderId) {
        try {
            const response = await fetch('models/backend/functions/order/orderFunctions.php?orderId=' + orderId);
            const data = await response.json();
            return data.success ? data.data : null;
        } catch (error) {
            console.error('獲取訂單詳情錯誤：', error);
            return null;
        }
    }

    // 訂單篩選功能
    document.getElementById('filterStatus').addEventListener('change', filterOrders);
    document.getElementById('filterDateFrom').addEventListener('change', filterOrders);
    document.getElementById('filterDateTo').addEventListener('change', filterOrders);
    document.getElementById('searchOrder').addEventListener('input', filterOrders);

    function filterOrders() {
        console.log('篩選訂單功能尚未實現');
    }

    // 查看訂單詳情
    document.querySelectorAll('.view-order').forEach(button => {
        button.addEventListener('click', async function () {
            const orderId = this.getAttribute('data-id');
            console.log('查看訂單詳情：', orderId);

            try {
                // 顯示載入中提示
                document.getElementById('orderDetailContent').innerHTML = `
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">載入中...</span>
                        </div>
                    </div>
                `;

                // 顯示模態框
                const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
                modal.show();

                // 獲取訂單詳情 - 使用後端函數直接獲取
                const orderId = this.getAttribute('data-id');
                const orderDetails = await getOrderDetailsFromBackend(orderId);

                // 渲染訂單詳情
                if (orderDetails) {
                    // 移除提示信息
                    document.querySelector('#orderDetailModal .alert-info').style.display = 'none';

                    // 渲染訂單詳情
                    renderOrderDetails(orderDetails);
                } else {
                    document.getElementById('orderDetailContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>獲取訂單詳情失敗
                        </div>
                    `;
                }
            } catch (error) {
                console.error('獲取訂單詳情錯誤：', error);
                document.getElementById('orderDetailContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>系統錯誤，請稍後再試
                    </div>
                `;
                // 顯示具體錯誤信息（僅在開發環境）
                if (error.message) {
                    console.error('錯誤詳情：', error.message);
                }
            }
        });
    });

    // 渲染訂單詳情
    function renderOrderDetails(orderData) {
        // 獲取訂單數據
        const { order_id, member_name, order_date, items, total_amount, status, statusClass, statusText } = orderData;

        // 格式化日期
        const formattedDate = new Date(order_date).toLocaleDateString('zh-TW');

        // 構建訂單詳情HTML
        let html = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="border-bottom pb-2">訂單信息</h5>
                    <table class="table table-sm">
                        <tr>
                            <th>訂單編號：</th>
                            <td>${order_id}</td>
                        </tr>
                        <tr>
                            <th>會員名稱：</th>
                            <td>${member_name}</td>
                        </tr>
                        <tr>
                            <th>訂單日期：</th>
                            <td>${formattedDate}</td>
                        </tr>
                        <tr>
                            <th>訂單狀態：</th>
                            <td>
                                <span class="badge ${statusClass}">
                                    ${statusText}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <h5 class="border-bottom pb-2">訂單商品</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>商品圖片</th>
                            <th>商品名稱</th>
                            <th>顏色</th>
                            <th>尺寸</th>
                            <th>單價</th>
                            <th>數量</th>
                            <th>小計</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        // 添加商品行
        items.forEach(item => {
            html += `
                <tr>
                    <td>
                        <img src="../../${item.image}" alt="${item.ProductName}" class="img-thumbnail" style="max-width: 80px;">
                    </td>
                    <td>${item.ProductName}</td>
                    <td>${item.Color}</td>
                    <td>${item.Size}${item.SizeDescription ? ` (${item.SizeDescription})` : ''}</td>
                    <td>$${Number(item.Price).toLocaleString()}</td>
                    <td>${item.Quantity}</td>
                    <td>$${Number(item.Subtotal).toLocaleString()}</td>
                </tr>
            `;
        });

        // 添加總計行
        html += `
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end">總計：</th>
                            <th>$${Number(total_amount).toLocaleString()}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;

        // 更新模態框內容
        document.getElementById('orderDetailContent').innerHTML = html;
    }

    // 獲取訂單狀態名稱
    function getOrderStatusName(status) {
        const statusList = {
            'Completed': '已完成',
            'Pending': '處理中',
            'Shipping': '運送中',
            'Cancel': '已取消',
            'abnormal': '異常'
        };
        return statusList[status] || status;
    }

    // 獲取訂單狀態樣式
    function getOrderStatusClass(status) {
        const statusClasses = {
            'Completed': 'bg-success',
            'Pending': 'bg-info text-dark',
            'Shipping': 'bg-primary',
            'Cancel': 'bg-warning',
            'abnormal': 'bg-danger'
        };
        return statusClasses[status] || 'bg-secondary';
    }

    // 更新訂單狀態
    document.querySelectorAll('.update-status').forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            console.log('更新訂單狀態：', orderId, currentStatus);

            // 設置當前狀態
            document.getElementById('updateOrderId').value = orderId;
            document.getElementById('orderStatus').value = currentStatus;

            // 顯示模態框
            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();
        });
    });

    // 保存訂單狀態更新
    document.getElementById('saveStatus').addEventListener('click', async function () {
        const orderId = document.getElementById('updateOrderId').value;
        const status = document.getElementById('orderStatus').value;

        console.log('保存訂單狀態更新：', orderId, status);

        // 顯示載入中提示
        const saveButton = document.getElementById('saveStatus');
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 處理中...';
        saveButton.disabled = true;

        try {
            // 使用 fetch API 發送 POST 請求
            const response = await fetch('models/backend/functions/order/orderFunctions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    orderId: orderId,
                    status: status
                })
            });

            const result = await response.json();

            // 關閉模態框
            const modal = bootstrap.Modal.getInstance(document.getElementById('updateStatusModal'));
            modal.hide();

            // 顯示結果提示
            const toastContainer = document.createElement('div');
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '11';

            if (result.success) {
                // 更新成功
                toastContainer.innerHTML = `
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle me-2"></i>${result.message || '訂單狀態已成功更新！'}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;

                // 更新頁面上的訂單狀態顯示
                // 找到對應的訂單行並更新狀態
                const orderRow = document.querySelector(`button.update-status[data-id="${orderId}"]`).closest('tr');
                if (orderRow) {
                    const statusCell = orderRow.querySelector('td:nth-child(6)');
                    const statusClass = getOrderStatusClass(status);
                    const statusText = getOrderStatusName(status);
                    statusCell.innerHTML = `<span class="badge ${statusClass}">${statusText}</span>`;

                    // 更新按鈕的 data-status 屬性
                    orderRow.querySelector('button.update-status').setAttribute('data-status', status);
                }
            } else {
                // 更新失敗
                toastContainer.innerHTML = `
                    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-exclamation-triangle me-2"></i>${result.message || '更新訂單狀態失敗！'}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
            }

            document.body.appendChild(toastContainer);
            const toastElement = toastContainer.querySelector('.toast');
            const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
            toast.show();

            // 3秒後移除 Toast 元素
            setTimeout(() => {
                document.body.removeChild(toastContainer);
            }, 3500);

        } catch (error) {
            console.error('更新訂單狀態錯誤：', error);

            // 顯示錯誤提示
            const errorToast = document.createElement('div');
            errorToast.className = 'position-fixed bottom-0 end-0 p-3';
            errorToast.style.zIndex = '11';
            errorToast.innerHTML = `
                <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-exclamation-triangle me-2"></i>系統錯誤，請稍後再試
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            document.body.appendChild(errorToast);
            const errorToastElement = errorToast.querySelector('.toast');
            const errorToastInstance = new bootstrap.Toast(errorToastElement, { delay: 3000 });
            errorToastInstance.show();

            // 3秒後移除 Toast 元素
            setTimeout(() => {
                document.body.removeChild(errorToast);
            }, 3500);
        } finally {
            // 恢復按鈕狀態
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
        }
    });

</script>