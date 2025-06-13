<?php
require_once __DIR__ . '/../api/error_handler.php';
require_once __DIR__ . '/../api/backendRepairAPI.php';   //  ← 只引入，一切函式已在此

// 取得目前登入者權限
$currentUserRole = isset($_SESSION['user_role']) ? (int) $_SESSION['user_role'] : 0;

// 防止直接訪問
if (!defined('INCLUDED_FROM_ADMIN') && !(isset($_SESSION['logged_in']) && $_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit;
}

/* ===== 取得查詢條件 ===== */
$status = $_GET['status'] ?? '';
$memberId = $_GET['member_id'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$repairOrders = fetchRepairOrders($status, $memberId, $dateFrom, $dateTo);
// 依送出先後順序排列，較早送出的排在上面
usort($repairOrders, function ($a, $b) {
    $dateA = strtotime($a['RepairDate']);
    $dateB = strtotime($b['RepairDate']);
    if ($dateA === $dateB) {
        // 日期一樣時，RepairID 較大的排在上面
        return $b['RepairID'] - $a['RepairID'];
    }
    return $dateA - $dateB;
});
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
            <form method="get" action="backend.php">
                <input type="hidden" name="page" value="repairOrderManager">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filterRepairStatus" class="form-label">維修狀態</label>
                        <select class="form-select" id="filterRepairStatus" name="status">
                            <option value="">全部狀態</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>待處理</option>
                            <option value="repairing" <?= $status === 'repairing' ? 'selected' : '' ?>>維修中</option>
                            <option value="pending_payment" <?= $status === 'pending_payment' ? 'selected' : '' ?>>待付款
                            </option>
                            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>已完成</option>
                            <option value="abnormal" <?= $status === 'abnormal' ? 'selected' : '' ?>>異常</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterMemberId" class="form-label">會員ID</label>
                        <input type="text" class="form-control" id="filterMemberId" name="member_id"
                            value="<?= htmlspecialchars($memberId) ?>" placeholder="請輸入會員ID">
                    </div>
                    <div class="col-md-3">
                        <label for="filterDateFrom" class="form-label">維修日期（起）</label>
                        <input type="date" class="form-control" id="filterDateFrom" name="date_from"
                            value="<?= isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="filterDateTo" class="form-label">維修日期（迄）</label>
                        <input type="date" class="form-control" id="filterDateTo" name="date_to"
                            value="<?= isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : '' ?>">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">查詢</button>
                        <a href="backend.php?page=repairOrderManager" class="btn btn-secondary">重設</a>
                    </div>
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
                                            <button class="btn btn-sm btn-outline-success view-repair"
                                                data-id="<?= $r['RepairID'] ?>"
                                                data-commit="<?= htmlspecialchars($r['RepairCommit'] ?? '') ?>"
                                                data-warranty-date="<?= htmlspecialchars($r['WarrantyDate'] ?? '') ?>"
                                                data-bs-toggle="modal" data-bs-target="#repairDetailModal" title="查看詳情">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <!-- 變更狀態 -->
                                            <?php if ($r['RepairStatus'] !== 'completed' || $currentUserRole === 2): ?>
                                                <button class="btn btn-sm btn-outline-primary update-repair-status"
                                                    data-id="<?= $r['RepairID'] ?>"
                                                    data-status="<?= htmlspecialchars($r['RepairStatus']) ?>"
                                                    data-warranty-date="<?= htmlspecialchars($r['WarrantyDate'] ?? '') ?>"
                                                    data-bs-toggle="modal" data-bs-target="#updateRepairStatusModal">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            <?php endif; ?>
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
                    <div class="mb-3">
                        <label class="form-label">保固狀態：</label>
                        <div id="modalWarrantyStatus" class="form-control"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">維修金額：</label>
                        <div id="modalRepairPrice" class="form-control"></div>
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
                        <label for="repairPriceInput" class="form-label">維修金額</label>
                        <input type="number" class="form-control" id="repairPriceInput" name="repair_price"
                            placeholder="請填入維修金額" min="0" step="1">
                        <div class="mt-2">
                            <label class="form-label">保固狀態：</label>
                            <div id="modalIsWarranty" class="form-control"></div>
                        </div>
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
            // 判斷保固狀態
            const warrantyDate = btn.dataset['warrantyDate'];
            let warrantyStatus = '無資料';
            if (warrantyDate) {
                const today = new Date();
                const warranty = new Date(warrantyDate);
                // 將時間設為當天結束，避免時區誤差
                warranty.setHours(23, 59, 59, 999);
                if (warranty >= today) {
                    warrantyStatus = '保固內';
                } else {
                    warrantyStatus = '保固已過期';
                }
            }
            document.getElementById('modalWarrantyStatus').textContent = warrantyStatus;
            // 從資料庫中讀取維修金額
            fetch(`api/backendRepairAPI.php?action=get_repair_price&repair_id=${btn.dataset.id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.price !== null) {
                        document.getElementById('modalRepairPrice').textContent = `NT$ ${data.price.toLocaleString()}`;
                    } else {
                        document.getElementById('modalRepairPrice').textContent = '無';
                    }
                })
                .catch(err => {
                    console.error('讀取維修金額失敗:', err);
                    document.getElementById('modalRepairPrice').textContent = '無';
                });
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

            // 顯示保固狀態
            const warrantyDate = btn.dataset['warrantyDate'];
            let warrantyStatus = '無資料';
            if (warrantyDate) {
                const today = new Date();
                const warranty = new Date(warrantyDate);
                warranty.setHours(23, 59, 59, 999);
                if (warranty >= today) {
                    warrantyStatus = '保固內';
                } else {
                    warrantyStatus = '保固已過期';
                }
            }
            document.querySelector('#updateRepairStatusModal #modalIsWarranty').textContent = warrantyStatus;

            // 初始化價格輸入框的狀態
            const priceInputGroup = document.getElementById('priceInputGroup');
            const priceInput = document.getElementById('repairPriceInput');
            if (newStatusSelect.value === 'pending_payment') {
                priceInputGroup.style.display = 'block';
                priceInput.required = true;
                // 如果是待付款狀態，從資料庫中讀取價格
                fetch(`api/backendRepairAPI.php?action=get_repair_price&repair_id=${btn.dataset.id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.price !== null) {
                            priceInput.value = data.price;
                        }
                    })
                    .catch(err => console.error('讀取價格失敗:', err));
            } else {
                priceInputGroup.style.display = 'none';
                priceInput.required = false;
                priceInput.value = '0';
            }
        });
    });

    // 監聽狀態選擇的變化
    document.getElementById('modalNewStatus').addEventListener('change', function () {
        const priceInputGroup = document.getElementById('priceInputGroup');
        const priceInput = document.getElementById('repairPriceInput');

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
        const priceInput = document.getElementById('repairPriceInput');

        try {
            const formData = new FormData();
            formData.append('repair_id', id);
            formData.append('new_status', status);
            
            // 修改價格資料的處理邏輯
            let priceValue = '';
            if (priceInput) {
                priceValue = priceInput.value;
                console.log('原始價格輸入值：', priceValue);
                if (priceValue) {
                    priceValue = priceValue.trim();
                    console.log('處理後的價格值：', priceValue);
                }
            }
            
            // 將價格加入 FormData
            formData.append('repair_price', priceValue);

            // 顯示要傳送的資料
            console.log('準備更新維修紀錄：', {
                repair_id: id,
                new_status: status,
                repair_price: priceValue || '未輸入價格'
            });

            const res = await fetch('api/backendRepairAPI.php', {
                method: 'POST',
                body: formData
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
            console.error('更新失敗：', err);
            alert(err.message || '發生錯誤，請稍後再試');
        }
    });
</script>