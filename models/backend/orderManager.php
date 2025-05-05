<?php
// 訂單管理文件

// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../../login.php');
    exit;
}

// 引入數據庫連接
require_once __DIR__ . '/../../api/db.php';

// 獲取所有訂單
function getAllOrders()
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT o.OrderID, o.OrderDate, o.Status, m.Name as MemberName, 
                              COUNT(oi.OptionID) as ItemCount, 
                              SUM(op.Price * oi.Quantity) as TotalAmount 
                              FROM Orders o 
                              JOIN Member m ON o.MembersID = m.MemberID 
                              JOIN OrderItem oi ON o.OrderID = oi.OrderID 
                              JOIN Options op ON oi.OptionID = op.OptionID 
                              GROUP BY o.OrderID 
                              ORDER BY o.OrderDate DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching orders: " . $e->getMessage());
        return [];
    }
}

// 獲取訂單詳情
function getOrderDetails($orderId)
{
    global $pdo;
    try {
        // 獲取訂單基本信息
        $stmt = $pdo->prepare("SELECT o.*, m.Name as MemberName, m.Email, m.Phone, m.City, m.Address 
                              FROM Orders o 
                              JOIN Member m ON o.MembersID = m.MemberID 
                              WHERE o.OrderID = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // 獲取訂單項目
        $stmt = $pdo->prepare("SELECT oi.*, op.Color, op.Size, op.SizeDescription, op.Price, 
                              p.ProductName, p.Type 
                              FROM OrderItem oi 
                              JOIN Options op ON oi.OptionID = op.OptionID 
                              JOIN Product p ON op.ProductID = p.ProductID 
                              WHERE oi.OrderID = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 獲取付款信息
        $stmt = $pdo->prepare("SELECT r.* 
                              FROM Receipt r 
                              WHERE r.OrderID = ?");
        $stmt->execute([$orderId]);
        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'order' => $order,
            'items' => $items,
            'receipt' => $receipt
        ];
    } catch (PDOException $e) {
        error_log("Error fetching order details: " . $e->getMessage());
        return null;
    }
}

// 更新訂單狀態
function updateOrderStatus($orderId, $status)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE Orders SET Status = ? WHERE OrderID = ?");
        $result = $stmt->execute([$status, $orderId]);
        return $result;
    } catch (PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        return false;
    }
}

// 獲取訂單狀態列表
function getOrderStatusList()
{
    return [
        'Completed' => '已完成',
        'Pending' => '處理中',
        'Shipping' => '運送中',
        'Cancel' => '已取消',
        'abnormal' => '異常'
    ];
}

// 獲取訂單狀態樣式
function getOrderStatusClass($status)
{
    return match ($status) {
        'Completed' => 'bg-success',
        'Pending' => 'bg-info text-dark',
        'Shipping' => 'bg-primary',
        'Cancel' => 'bg-warning',
        'abnormal' => 'bg-danger',
        default => 'bg-secondary'
    };
}

// 嘗試獲取訂單列表
try {
    $orders = getAllOrders();
    $statusList = getOrderStatusList();
} catch (Exception $e) {
    error_log("Error in order manager: " . $e->getMessage());
    $orders = [];
    $statusList = getOrderStatusList();
}
?>

<div class="container-fluid p-0">
    <!-- 訂單管理頁面標題 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cart-check me-2"></i>購買訂單管理</h3>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" id="exportOrders">
                匯出訂單
            </button>
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
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center">暫無訂單數據</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['OrderID']); ?></td>
                                    <td><?php echo htmlspecialchars($order['MemberName']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($order['OrderDate']))); ?></td>
                                    <td><?php echo htmlspecialchars($order['ItemCount']); ?></td>
                                    <td>$<?php echo htmlspecialchars(number_format($order['TotalAmount'], 2)); ?></td>
                                    <td>
                                        <span class="badge <?php echo getOrderStatusClass($order['Status']); ?>">
                                            <?php echo htmlspecialchars($statusList[$order['Status']] ?? $order['Status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary view-order"
                                                data-id="<?php echo htmlspecialchars($order['OrderID']); ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success update-status"
                                                data-id="<?php echo htmlspecialchars($order['OrderID']); ?>"
                                                data-status="<?php echo htmlspecialchars($order['Status']); ?>">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info print-order"
                                                data-id="<?php echo htmlspecialchars($order['OrderID']); ?>">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php require_once __DIR__ . '/functions/pagination.php';

            // 使用分頁功能
            $pagination = generatePagination($currentPage, $totalPages, $filterParams); ?>
            <?php echo $pagination; ?>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    <button type="button" class="btn btn-primary" id="printOrderDetail">
                        <i class="bi bi-printer me-2"></i>列印訂單
                    </button>
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
                        <div class="mb-3">
                            <label for="statusNote" class="form-label">備註</label>
                            <textarea class="form-control" id="statusNote" rows="3" placeholder="可選填寫狀態更新備註"></textarea>
                        </div>
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
    // 訂單篩選功能
    document.getElementById('filterStatus').addEventListener('change', filterOrders);
    document.getElementById('filterDateFrom').addEventListener('change', filterOrders);
    document.getElementById('filterDateTo').addEventListener('change', filterOrders);
    document.getElementById('searchOrder').addEventListener('input', filterOrders);

    function filterOrders() {
        console.log('篩選訂單功能尚未實現');
        // 實際篩選功能將在後續開發中實現
    }

    // 查看訂單詳情
    document.querySelectorAll('.view-order').forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.getAttribute('data-id');
            console.log('查看訂單詳情：', orderId);
            // 實際查看功能將在後續開發中實現

            // 顯示模態框
            const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
            modal.show();
        });
    });

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
    document.getElementById('saveStatus').addEventListener('click', function () {
        const orderId = document.getElementById('updateOrderId').value;
        const status = document.getElementById('orderStatus').value;
        const note = document.getElementById('statusNote').value;

        console.log('保存訂單狀態更新：', orderId, status, note);
        // 實際保存功能將在後續開發中實現

        // 關閉模態框
        const modal = bootstrap.Modal.getInstance(document.getElementById('updateStatusModal'));
        modal.hide();

        // 顯示成功提示
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '11';

        toastContainer.innerHTML = `
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i>訂單狀態已成功更新！
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        document.body.appendChild(toastContainer);
        const toastElement = toastContainer.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();

        // 3秒後移除 Toast 元素
        setTimeout(() => {
            document.body.removeChild(toastContainer);
        }, 3500);
    });

    // 列印訂單
    document.querySelectorAll('.print-order').forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.getAttribute('data-id');
            console.log('列印訂單：', orderId);
            // 實際列印功能將在後續開發中實現
            alert('列印功能正在開發中');
        });
    });

    // 匯出訂單
    document.getElementById('exportOrders').addEventListener('click', function () {
        console.log('匯出訂單功能尚未實現');
        // 實際匯出功能將在後續開發中實現
        alert('匯出功能正在開發中');
    });

</script>