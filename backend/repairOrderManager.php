<?php
require_once __DIR__ . '/../api/error_handler.php';
require_once __DIR__ . '/../api/backendRepairAPI.php';   //  ← 只引入，一切函式已在此

// 防止直接訪問
if (!defined('INCLUDED_FROM_ADMIN') && !(isset($_SESSION['logged_in']) && $_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit;
}

/* ===== 取得查詢條件 ===== */
$status = $_GET['status'] ?? '';
$memberId = $_GET['member_id'] ?? '';

$repairOrders = fetchRepairOrders($status, $memberId);
$statusList = getRepairStatusList();
?>

<div class="container-fluid p-0">

    <!-- 標題 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-tools me-2"></i>維修訂單</h3>
    </div>

    <!-- 篩選區 -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="backend.php" class="row g-3 align-items-end">
                <input type="hidden" name="tab" value="repair">
                <!-- 狀態 -->
                <div class="col-md-3">
                    <label for="filterRepairStatus" class="form-label">維修狀態</label>
                    <select class="form-select" id="filterRepairStatus" name="status">
                        <option value="">全部狀態</option>
                        <?php foreach ($statusList as $key => $value): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $status === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- 會員 ID -->
                <div class="col-md-3">
                    <label for="filterMemberId" class="form-label">會員ID</label>
                    <input type="text" class="form-control" id="filterMemberId" name="member_id"
                        value="<?= htmlspecialchars($memberId) ?>" placeholder="請輸入會員ID">
                </div>
                <!-- 按鈕 -->
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">查詢</button>
                    <a href="?" class="btn btn-secondary ms-2">重設</a>
                </div>
            </form>
        </div>
    </div>

    <!-- 列表 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
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
                                <td colspan="7" class="text-center">暫無資料</td>
                            </tr>
                        <?php else:
                            foreach ($repairOrders as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['RepairID']) ?></td>
                                    <td><?= htmlspecialchars($r['WarrantyID']) ?></td>
                                    <td><?= htmlspecialchars($r['MemberName']) ?></td>
                                    <td><?= htmlspecialchars($r['ProductName']) ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($r['RepairDate']))) ?></td>
                                    <td>
                                        <span class="badge <?= getRepairStatusClass($r['RepairStatus']) ?>">
                                            <?= htmlspecialchars($statusList[$r['RepairStatus']] ?? $r['RepairStatus']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <!-- 詳情 -->
                                            <button class="btn btn-sm btn-outline-primary view-repair"
                                                data-id="<?= $r['RepairID'] ?>"
                                                data-commit="<?= htmlspecialchars($r['RepairCommit'] ?? '') ?>"
                                                data-warranty-date="<?= htmlspecialchars($r['WarrantyDate'] ?? '') ?>"
                                                data-bs-toggle="modal" data-bs-target="#repairDetailModal" title="查看詳情">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <!-- 變更狀態 -->
                                            <button class="btn btn-sm btn-outline-success update-repair-status"
                                                data-id="<?= $r['RepairID'] ?>"
                                                data-status="<?= htmlspecialchars($r['RepairStatus']) ?>" data-bs-toggle="modal"
                                                data-bs-target="#updateRepairStatusModal">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========== 詳情 Modal ========== -->
    <div class="modal fade" id="repairDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">維修訂單詳情</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">關閉</button></div>
            </div>
        </div>
    </div>

    <!-- ========== 更新狀態 Modal ========== -->
    <div class="modal fade" id="updateRepairStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="updateRepairStatusForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">變更維修狀態</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalRepairId" name="repair_id">
                    <div class="mb-3">
                        <label for="modalNewStatus" class="form-label">選擇新狀態</label>
                        <select class="form-select" id="modalNewStatus" name="new_status" required>
                            <?php foreach ($statusList as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="priceInputGroup" style="display: none;">
                        <label for="modalRepairPrice" class="form-label">維修金額</label>
                        <input type="number" class="form-control" id="modalRepairPrice" name="repair_price" 
                               placeholder="請填入維修金額" min="0" step="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">儲存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.view-repair').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('modalRepairCommit').textContent = btn.dataset.commit || '無';
            document.getElementById('modalWarrantyDate').textContent = btn.dataset['warrantyDate'] || '無';
        });
    });

    document.querySelectorAll('.update-repair-status').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('modalRepairId').value = btn.dataset.id;
            // 將中文狀態轉換為英文
            const statusMap = {
                '待處理': 'pending',
                '維修中': 'repairing',
                '待付款': 'pending_payment',
                '已完成': 'completed',
                '異常': 'abnormal'
            };
            const currentStatus = btn.dataset.status;
            const newStatusSelect = document.getElementById('modalNewStatus');
            newStatusSelect.value = statusMap[currentStatus] || currentStatus;
            
            // 初始化價格輸入框的狀態
            const priceInputGroup = document.getElementById('priceInputGroup');
            const priceInput = document.getElementById('modalRepairPrice');
            if (newStatusSelect.value === 'pending_payment') {
                priceInputGroup.style.display = 'block';
                priceInput.required = true;
            } else {
                priceInputGroup.style.display = 'none';
                priceInput.required = false;
                priceInput.value = '0';
            }
        });
    });

    // 監聽狀態選擇的變化
    document.getElementById('modalNewStatus').addEventListener('change', function() {
        const priceInputGroup = document.getElementById('priceInputGroup');
        const priceInput = document.getElementById('modalRepairPrice');
        
        if (this.value === 'pending_payment') {
            priceInputGroup.style.display = 'block';
            priceInput.required = true;
        } else {
            priceInputGroup.style.display = 'none';
            priceInput.required = false;
            priceInput.value = '0';
        }
    });

    document.getElementById('updateRepairStatusForm').addEventListener('submit', async e => {
        e.preventDefault();
        const id = document.getElementById('modalRepairId').value;
        const status = document.getElementById('modalNewStatus').value;
        const price = status === 'pending_payment' ? 
            document.getElementById('modalRepairPrice').value : '0';

        try {
            const res = await fetch('api/backendRepairAPI.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `repair_id=${encodeURIComponent(id)}&new_status=${encodeURIComponent(status)}&repair_price=${encodeURIComponent(price)}`
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.message || '更新失敗');

            const row = document.querySelector(`button.update-repair-status[data-id="${id}"]`).closest('tr');
            const badge = row.querySelector('span.badge');
            badge.textContent = json.statusText;
            badge.className = 'badge ' + json.statusClass;

            row.querySelector('button.update-repair-status').dataset.status = status;
            bootstrap.Modal.getInstance(document.getElementById('updateRepairStatusModal')).hide();
        } catch (err) {
            alert(err.message || '發生錯誤，請稍後再試');
        }
    });
</script>