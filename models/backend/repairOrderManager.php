<?php
// 維修訂單管理文件

// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../../login.php');
    exit;
}

// 引入數據庫連接
require_once __DIR__ . '/../../api/db.php';

// 獲取所有維修訂單
function getAllRepairOrders()
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT r.RepairID, r.RepairDate, r.RepairStatus, 
                              w.WarrantyID, w.WarrantyDate, w.WarrantyStatus, 
                              m.Name as MemberName, p.ProductName 
                              FROM Repairs r 
                              JOIN Warranty w ON r.WarrantyID = w.WarrantyID 
                              JOIN ReceiptItem ri ON w.WarrantyID = ri.WarrantyID 
                              JOIN Receipt rec ON ri.ReceiptID = rec.ReceiptID 
                              JOIN Member m ON rec.MemberID = m.MemberID 
                              JOIN Options o ON ri.OptionID = o.OptionID 
                              JOIN Product p ON o.ProductID = p.ProductID 
                              ORDER BY r.RepairDate DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching repair orders: " . $e->getMessage());
        return [];
    }
}

// 獲取維修訂單狀態列表
function getRepairStatusList()
{
    return [
        'Completed' => '已完成',
        'Pending' => '處理中',
        'Repairing' => '維修中',
        'Cancel' => '已取消',
        'abnormal' => '異常'
    ];
}

// 獲取維修訂單狀態樣式
function getRepairStatusClass($status)
{
    return match ($status) {
        'Completed' => 'bg-success',
        'Pending' => 'bg-info text-dark',
        'Repairing' => 'bg-primary',
        'Cancel' => 'bg-warning',
        'abnormal' => 'bg-danger',
        default => 'bg-secondary'
    };
}

// 嘗試獲取維修訂單列表
try {
    $repairOrders = getAllRepairOrders();
    $statusList = getRepairStatusList();
} catch (Exception $e) {
    error_log("Error in repair order manager: " . $e->getMessage());
    $repairOrders = [];
    $statusList = getRepairStatusList();
}
?>

<div class="container-fluid p-0">
    <!-- 維修訂單管理頁面標題 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-tools me-2"></i>維修訂單管理</h3>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" id="exportRepairs">
                匯出維修訂單
            </button>
        </div>
    </div>

    <!-- 維修訂單篩選區 -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="filterRepairStatus" class="form-label">維修狀態</label>
                    <select class="form-select" id="filterRepairStatus">
                        <option value="">全部狀態</option>
                        <?php foreach ($statusList as $key => $value): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>">
                                <?php echo htmlspecialchars($value); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterRepairDateFrom" class="form-label">開始日期</label>
                    <input type="date" class="form-control" id="filterRepairDateFrom">
                </div>
                <div class="col-md-3">
                    <label for="filterRepairDateTo" class="form-label">結束日期</label>
                    <input type="date" class="form-control" id="filterRepairDateTo">
                </div>
                <div class="col-md-3">
                    <label for="searchRepair" class="form-label">搜尋維修訂單</label>
                    <input type="text" class="form-control" id="searchRepair" placeholder="維修ID或會員名稱">
                </div>
            </div>
        </div>
    </div>

    <!-- 維修訂單列表 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>維修ID</th>
                            <th>保固ID</th>
                            <th>會員名稱</th>
                            <th>商品名稱</th>
                            <th>維修日期</th>
                            <th>狀態</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($repairOrders)): ?>
                            <tr>
                                <td colspan="7" class="text-center">暫無維修訂單數據</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($repairOrders as $repair): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($repair['RepairID']); ?></td>
                                    <td><?php echo htmlspecialchars($repair['WarrantyID']); ?></td>
                                    <td><?php echo htmlspecialchars($repair['MemberName']); ?></td>
                                    <td><?php echo htmlspecialchars($repair['ProductName']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($repair['RepairDate']))); ?></td>
                                    <td>
                                        <span class="badge <?php echo getRepairStatusClass($repair['RepairStatus']); ?>">
                                            <?php echo htmlspecialchars($statusList[$repair['RepairStatus']] ?? $repair['RepairStatus']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary view-repair"
                                                data-id="<?php echo htmlspecialchars($repair['RepairID']); ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success update-repair-status"
                                                data-id="<?php echo htmlspecialchars($repair['RepairID']); ?>"
                                                data-status="<?php echo htmlspecialchars($repair['RepairStatus']); ?>">
                                                <i class="bi bi-arrow-repeat"></i>
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
        </div>
    </div>

    <!-- 維修訂單詳情模態框 -->
    <div class="modal fade" id="repairDetailModal" tabindex="-1" aria-labelledby="repairDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="repairDetailModalLabel">維修訂單詳情</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>維修訂單詳情功能正在開發中，敬請期待。
                    </div>
                    <!-- 維修訂單詳情內容將在這裡動態加載 -->
                    <div id="repairDetailContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    <button type="button" class="btn btn-primary" id="printRepairDetail">
                        <i class="bi bi-printer me-2"></i>列印維修單
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 維修訂單篩選功能
    document.getElementById('filterRepairStatus').addEventListener('change', filterRepairs);
    document.getElementById('filterRepairDateFrom').addEventListener('change', filterRepairs);
    document.getElementById('filterRepairDateTo').addEventListener('change', filterRepairs);
    document.getElementById('searchRepair').addEventListener('input', filterRepairs);

    function filterRepairs() {
        console.log('篩選維修訂單功能尚未實現');
        // 實際篩選功能將在後續開發中實現
    }

    // 查看維修訂單詳情
    document.querySelectorAll('.view-repair').forEach(button => {
        button.addEventListener('click', function () {
            const repairId = this.getAttribute('data-id');
            console.log('查看維修訂單詳情：', repairId);
            // 實際查看功能將在後續開發中實現

            // 顯示模態框
            const modal = new bootstrap.Modal(document.getElementById('repairDetailModal'));
            modal.show();
        });
    });

</script>