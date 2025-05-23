<?php
require_once __DIR__ . '/../api/error_handler.php';
// 維修訂單管理文件

// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit;
}

// 引入數據庫連接
require_once __DIR__ . '/../api/db.php';

// 獲取所有維修訂單
function getAllRepairOrders()
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT r.RepairID, r.RepairDate, r.RepairStatus, r.RepairCommit,
                              w.WarrantyID, w.WarrantyDate, w.WarrantyStatus, 
                              m.Name as MemberName, orec.ProductName 
                              FROM Repairs r 
                              JOIN Warranty w ON r.WarrantyID = w.WarrantyID 
                              JOIN ReceiptItem ri ON w.WarrantyID = ri.WarrantyID 
                              JOIN OrderRecord orec ON ri.OrderRecordID = orec.RecordID 
                              JOIN Receipt rec ON ri.ReceiptID = rec.ReceiptID 
                              JOIN Member m ON rec.MemberID = m.MemberID 
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
        '已完成' => '已完成',
        '處理中' => '處理中',
        '維修中' => '維修中',
        '已取消' => '已取消',
        '異常' => '異常'
    ];
}

// 獲取維修訂單狀態樣式
function getRepairStatusClass($status)
{
    return match ($status) {
        '已完成' => 'bg-success',
        '處理中' => 'bg-info text-dark',
        '維修中' => 'bg-primary',
        '已取消' => 'bg-warning',
        '異常' => 'bg-danger',
        default => 'bg-secondary'
    };
}

// 查詢過濾
function getFilteredRepairOrders($status, $memberId)
{
    $pdo = getDBConnection();
    $sql = "SELECT r.RepairID, r.RepairDate, r.RepairStatus, r.RepairCommit,
                  w.WarrantyID, w.WarrantyDate, w.WarrantyStatus, 
                  m.Name as MemberName, orec.ProductName, m.MemberID
            FROM Repairs r 
            JOIN Warranty w ON r.WarrantyID = w.WarrantyID 
            JOIN ReceiptItem ri ON w.WarrantyID = ri.WarrantyID 
            JOIN OrderRecord orec ON ri.OrderRecordID = orec.RecordID 
            JOIN Receipt rec ON ri.ReceiptID = rec.ReceiptID 
            JOIN Member m ON rec.MemberID = m.MemberID 
            WHERE 1=1";
    $params = [];
    if ($status !== '' && $status !== null) {
        $sql .= " AND r.RepairStatus = :status";
        $params['status'] = $status;
    }
    if ($memberId !== '' && $memberId !== null) {
        $sql .= " AND m.MemberID = :member_id";
        $params['member_id'] = $memberId;
    }
    $sql .= " ORDER BY r.RepairDate DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 嘗試獲取維修訂單列表
try {
    // 決定要用全部還是過濾後的資料
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $memberId = isset($_GET['member_id']) ? $_GET['member_id'] : '';
    if ($status !== '' || $memberId !== '') {
        $repairOrders = getFilteredRepairOrders($status, $memberId);
    } else {
        $repairOrders = getAllRepairOrders();
    }
    $statusList = getRepairStatusList();
} catch (Exception $e) {
    error_log("Error in repair order manager: " . $e->getMessage());
    $repairOrders = [];
    $statusList = getRepairStatusList();
}

$tab = $_GET['tab'] ?? 'product';
?>

<div class="container-fluid p-0">
    <!-- 維修訂單管理頁面標題 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-tools me-2"></i>維修訂單</h3>
        <div>

        </div>
    </div>

    <!-- 維修訂單篩選區 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="backend.php" class="row g-3 align-items-end">
                <input type="hidden" name="tab" value="repair">
                <div class="col-md-3">
                    <label for="filterRepairStatus" class="form-label">維修狀態</label>
                    <select class="form-select" id="filterRepairStatus" name="status">
                        <option value="">全部狀態</option>
                        <?php foreach ($statusList as $key => $value): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php if (isset($_GET['status']) && $_GET['status'] === $key)
                                   echo 'selected'; ?>>
                                <?php echo htmlspecialchars($value); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterMemberId" class="form-label">會員ID</label>
                    <input type="text" class="form-control" id="filterMemberId" name="member_id"
                        value="<?php echo isset($_GET['member_id']) ? htmlspecialchars($_GET['member_id']) : ''; ?>"
                        placeholder="請輸入會員ID">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">查詢</button>
                    <a href="?" class="btn btn-secondary ms-2">重設</a>
                </div>
            </form>
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
                            <?php
                            require_once __DIR__ . '/../api/error_handler.php';
                            foreach ($repairOrders as $repair): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($repair['RepairID']); ?></td>
                                    <td><?php echo htmlspecialchars($repair['WarrantyID']); ?></td>
                                    <td><?php echo htmlspecialchars($repair['MemberName']); ?></td>
                                    <td><?php echo htmlspecialchars($repair['ProductName']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($repair['RepairDate']))); ?></td>
                                    <td>
                                        <span class="badge <?php echo getRepairStatusClass($repair['RepairStatus']); ?>">
                                            <?php
                                            require_once __DIR__ . '/../api/error_handler.php';
                                            echo htmlspecialchars($statusList[$repair['RepairStatus']] ?? $repair['RepairStatus']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary view-repair"
                                                data-id="<?php echo htmlspecialchars($repair['RepairID']); ?>"
                                                data-commit="<?php echo htmlspecialchars($repair['RepairCommit'] ?? ''); ?>"
                                                data-warranty-date="<?php echo htmlspecialchars($repair['WarrantyDate'] ?? ''); ?>"
                                                data-bs-toggle="modal" data-bs-target="#repairDetailModal" title="查看詳情">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success update-repair-status"
                                                data-id="<?php echo htmlspecialchars($repair['RepairID']); ?>"
                                                data-status="<?php echo htmlspecialchars($repair['RepairStatus']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#updateRepairStatusModal">
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
                    <div class="mb-3">
                        <label class="form-label">維修備註：</label>
                        <div id="modalRepairCommit" class="form-control"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">保固到期日：</label>
                        <div id="modalWarrantyDate" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 變更維修狀態 Modal -->
    <div class="modal fade" id="updateRepairStatusModal" tabindex="-1" aria-labelledby="updateRepairStatusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="updateRepairStatusForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateRepairStatusModalLabel">變更維修狀態</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="關閉"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalRepairId" name="repair_id">
                    <div class="mb-3">
                        <label for="modalNewStatus" class="form-label">選擇新狀態</label>
                        <select class="form-select" id="modalNewStatus" name="new_status" required>
                            <?php foreach ($statusList as $key => $value): ?>
                                <option value="<?php echo htmlspecialchars($key); ?>">
                                    <?php echo htmlspecialchars($value); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">儲存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // 查看維修訂單詳情
    document.querySelectorAll('.view-repair').forEach(button => {
        button.addEventListener('click', function () {
            // 取得資料
            const commit = this.getAttribute('data-commit') || '無';
            const warrantyDate = this.getAttribute('data-warranty-date') || '無';

            // 填進 modal
            document.getElementById('modalRepairCommit').textContent = commit;
            document.getElementById('modalWarrantyDate').textContent = warrantyDate;
        });
    });

    // 彈出 modal 時帶入 RepairID 與目前狀態
    document.querySelectorAll('.update-repair-status').forEach(button => {
        button.addEventListener('click', function () {
            const repairId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            document.getElementById('modalRepairId').value = repairId;
            document.getElementById('modalNewStatus').value = currentStatus;
        });
    });

    // 處理表單送出
    document.getElementById('updateRepairStatusForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const repairId = document.getElementById('modalRepairId').value;
        const newStatus = document.getElementById('modalNewStatus').value;

        try {
            const response = await fetch('api/updateRepairStatus.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `repair_id=${encodeURIComponent(repairId)}&new_status=${encodeURIComponent(newStatus)}`
            });
            const result = await response.json();
            if (result.success) {
                // 更新表格上的狀態
                const row = document.querySelector(`button.update-repair-status[data-id="${repairId}"]`).closest('tr');
                const statusBadge = row.querySelector('span.badge');
                statusBadge.textContent = newStatus;
                statusBadge.className = 'badge ' + result.statusClass;
                // 更新按鈕的 data-status 屬性
                row.querySelector(`button.update-repair-status[data-id="${repairId}"]`).setAttribute('data-status', newStatus);
                // 關閉 modal
                bootstrap.Modal.getInstance(document.getElementById('updateRepairStatusModal')).hide();
            } else {
                alert(result.message || '更新失敗');
            }
        } catch (err) {
            alert('發生錯誤，請稍後再試');
        }
    });

</script>