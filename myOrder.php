<?php
declare(strict_types=1);
session_start();

/* ---------- 小工具：安全輸出 ---------- */
function h(mixed $val): string
{
    return htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
}

/* ---------- 登入驗證 ---------- */
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

/* ---------- 載入依賴 ---------- */
require_once __DIR__ . '/api/Member.php';
require_once __DIR__ . '/api/order.php';
require_once __DIR__ . '/api/error_handler.php';

/* ---------- 撈取資料 ---------- */
$memberId = (int) $_SESSION['user_id'];
$currentMember = getMemberByID($memberId);
$orders = getMemberOrders($memberId);
?>
<?php include __DIR__ . '/header.php'; ?>

<!-- ========= Main ========= -->
<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png?t=<?php echo time(); ?>" style="max-height: 100px;" alt="歐印精品" class="img-fluid">
    <h1 class="h1">會員資料</h1>
</div>

<div class="container mt-3">
    <div class="row">
        <!-- 會員資訊 -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">會員資訊</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#editMemberModal">編輯會員資料</button>
                </div>
                <div class="card-body">
                    <h4><?= h($currentMember['membername']) ?></h4>
                    <p class="mb-0"><strong>身份：</strong><?= ['一般會員', '員工', '管理員'][$currentMember['role']] ?? '一般會員' ?>
                    </p>
                    <p class="mb-0"><strong>手機號碼：</strong><?= h($currentMember['phonenum']) ?></p>
                    <p class="mb-0"><strong>電子郵件：</strong><?= h($currentMember['memberemail']) ?></p>
                    <p class="mb-0"><strong>地址：</strong><?= h($currentMember['City'] . $currentMember['Address']) ?></p>
                </div>
            </div>
        </div>

        <!-- 訂單列表 -->
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h3>您的訂單</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="orderTabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending">
                                <i class="bi bi-clock-history me-1"></i>進行中
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completed">
                                <i class="bi bi-check-circle me-1"></i>已完成
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#repairs">
                                <i class="bi bi-tools me-1"></i>維修記錄
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="orderTabsContent">
                        <?php renderOrderTab($orders, 'Pending', '進行中'); ?>
                        <?php renderOrderTab($orders, 'Completed', '已完成'); ?>
                        <?php renderRepairTab($orders, '維修記錄'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ---------- 共用訊息 Modal ---------- -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">系統訊息</h5>
            </div>
            <div class="modal-body" id="modalMessage"></div>
            <div class="modal-footer"><button class="btn btn-primary" data-bs-dismiss="modal">確定</button></div>
        </div>
    </div>
</div>

<!-- ---------- 編輯會員資料 Modal ---------- -->
<div class="modal fade" id="editMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="memberEditForm">
                <div class="modal-header">
                    <h5 class="modal-title">編輯會員資料</h5>
                </div>
                <div class="modal-body">
                    <?php
                    $map = [
                        'name' => ['姓名', 'membername'],
                        'phone' => ['電話', 'phonenum'],
                        'email' => ['電子郵件', 'memberemail'],
                        'city' => ['城市', 'City'],
                        'address' => ['地址', 'Address']
                    ];
                    foreach ($map as $name => [$label, $col]): ?>
                        <?php if ($name === 'city'): ?>
                            <div class="mb-3">
                                <label class="form-label"><?= $label ?></label>
                                <select class="form-select" id="city" name="city" required>
                                    <option value="" selected disabled>城市</option>
                                    <option value="基隆市" <?= $currentMember[$col] === '基隆市' ? 'selected' : '' ?>>基隆市</option>
                                    <option value="新北市" <?= $currentMember[$col] === '新北市' ? 'selected' : '' ?>>新北市</option>
                                    <option value="台北市" <?= $currentMember[$col] === '台北市' ? 'selected' : '' ?>>台北市</option>
                                    <option value="桃園市" <?= $currentMember[$col] === '桃園市' ? 'selected' : '' ?>>桃園市</option>
                                    <option value="新竹縣" <?= $currentMember[$col] === '新竹縣' ? 'selected' : '' ?>>新竹縣</option>
                                    <option value="新竹市" <?= $currentMember[$col] === '新竹市' ? 'selected' : '' ?>>新竹市</option>
                                    <option value="苗栗縣" <?= $currentMember[$col] === '苗栗縣' ? 'selected' : '' ?>>苗栗縣</option>
                                    <option value="台中市" <?= $currentMember[$col] === '台中市' ? 'selected' : '' ?>>台中市</option>
                                    <option value="彰化縣" <?= $currentMember[$col] === '彰化縣' ? 'selected' : '' ?>>彰化縣</option>
                                    <option value="南投縣" <?= $currentMember[$col] === '南投縣' ? 'selected' : '' ?>>南投縣</option>
                                    <option value="雲林縣" <?= $currentMember[$col] === '雲林縣' ? 'selected' : '' ?>>雲林縣</option>
                                    <option value="嘉義縣" <?= $currentMember[$col] === '嘉義縣' ? 'selected' : '' ?>>嘉義縣</option>
                                    <option value="嘉義市" <?= $currentMember[$col] === '嘉義市' ? 'selected' : '' ?>>嘉義市</option>
                                    <option value="台南市" <?= $currentMember[$col] === '台南市' ? 'selected' : '' ?>>台南市</option>
                                    <option value="高雄市" <?= $currentMember[$col] === '高雄市' ? 'selected' : '' ?>>高雄市</option>
                                    <option value="屏東縣" <?= $currentMember[$col] === '屏東縣' ? 'selected' : '' ?>>屏東縣</option>
                                    <option value="宜蘭縣" <?= $currentMember[$col] === '宜蘭縣' ? 'selected' : '' ?>>宜蘭縣</option>
                                    <option value="花蓮縣" <?= $currentMember[$col] === '花蓮縣' ? 'selected' : '' ?>>花蓮縣</option>
                                    <option value="台東縣" <?= $currentMember[$col] === '台東縣' ? 'selected' : '' ?>>台東縣</option>
                                    <option value="金門縣" <?= $currentMember[$col] === '金門縣' ? 'selected' : '' ?>>金門縣</option>
                                    <option value="連江縣" <?= $currentMember[$col] === '連江縣' ? 'selected' : '' ?>>連江縣</option>
                                    <option value="澎湖縣" <?= $currentMember[$col] === '澎湖縣' ? 'selected' : '' ?>>澎湖縣</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label"><?= $label ?></label>
                                <input class="form-control" name="<?= $name ?>" value="<?= h($currentMember[$col]) ?>"
                                    <?= $name === 'email' ? 'type=email' : '' ?>>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning me-auto" id="openResetPwdModal">重設密碼</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button class="btn btn-primary" id="saveMemberChanges">儲存變更</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ---------- 重設密碼 Modal ---------- -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="passwordResetForm">
                <div class="modal-header">
                    <h5 class="modal-title">重設密碼</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">舊密碼</label><input name="oldPassword" type="password"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">新密碼</label><input name="newPassword" type="password"
                            class="form-control" required minlength="8"></div>
                    <div class="mb-3"><label class="form-label">確認新密碼</label><input name="confirmPassword"
                            type="password" class="form-control" required minlength="8"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">儲存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ---------- 付款 Modal ---------- -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">付款方式</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="paymentRepairId">
                    <div class="mb-3">
                        <label class="form-label">請選擇付款方式：</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="payCredit" value="信用卡" checked>
                            <label class="form-check-label" for="payCredit">信用卡</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="payATM" value="ATM轉帳">
                            <label class="form-check-label" for="payATM">ATM轉帳</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="payLinePay" value="LinePAY">
                            <label class="form-check-label" for="payLinePay">LinePay</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="confirmPaymentBtn">確認付款</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

<!-- ---------- 將 PHP 變數輸出到 JS ---------- -->
<script>
    const CONFIG = <?= json_encode([
        'memberId' => $memberId,
        'isAdmin' => ($_SESSION['user_role'] ?? '') === 'admin',
        'orderAmountMap' => array_column($orders, 'TotalAmount', 'OrderID')
    ], JSON_UNESCAPED_UNICODE); ?>;
</script>

<!-- ---------- 功能腳本 ---------- -->
<script>
    (() => {
        // 所有 Modal 的 id 列表
        const modalIds = ['messageModal', 'editMemberModal', 'resetPasswordModal', 'paymentModal'];
        const msgModal = new bootstrap.Modal(document.getElementById('messageModal'));
        const resetPwdModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
        let currentOrderId = null;
        let lastClosedModalId = null; // 用來記錄剛剛被關掉的 modal id

        /* === 當任何一個 modal 要顯示時，先把其他還開著的 modal 全部隱藏 === */
        document.addEventListener('show.bs.modal', e => {
            modalIds.forEach(id => {
                // 跳過正在開啟的那個
                if (id === e.target.id) return;
                const el = document.getElementById(id);
                bootstrap.Modal.getInstance(el)?.hide();
            });
        });

        const showMsg = t => {
            // 找到目前開啟的 modal
            const openedModal = document.querySelector('.modal.show:not(#messageModal)');
            if (openedModal) {
                lastClosedModalId = openedModal.id;
                bootstrap.Modal.getInstance(openedModal)?.hide();
            } else {
                lastClosedModalId = null;
            }
            document.getElementById('modalMessage').textContent = t;
            msgModal.show();
        };

        // 監聽 messageModal 關閉事件
        document.getElementById('messageModal').addEventListener('hidden.bs.modal', () => {
            if (lastClosedModalId) {
                const modalEl = document.getElementById(lastClosedModalId);
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modalInstance.show();
                }
                lastClosedModalId = null;
            }
        });

        /* -------- 再買一次 -------- */
        document.addEventListener('click', async ev => {
            const btn = ev.target.closest('[data-action="reorder"]');
            if (btn) return reorder(btn.dataset.orderId);
        });
        async function reorder(orderId) {
            try {
                const r = await fetch('api/order.php?action=reorder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ memberId: CONFIG.memberId, orderId })
                });
                const d = await r.json();
                if (d.success) {
                    location.href = 'cart.php';
                } else {
                    showMsg('再買一次失敗：' + d.message);
                }
            } catch {
                showMsg('發生錯誤，請稍後再試');
            }
        }

        /* -------- 會員資料更新 -------- */
        document.getElementById('memberEditForm').addEventListener('submit', async ev => {
            ev.preventDefault();
            const payload = Object.fromEntries(new FormData(ev.target).entries());
            if (!payload.name || !payload.phone || !payload.email) {
                showMsg('請填寫所有必填欄位');
                return;
            }
            payload.memberId = CONFIG.memberId;
            payload.isAdmin = CONFIG.isAdmin ? 1 : 0;
            try {
                const r = await fetch('api/order.php?action=updateMember', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const d = await r.json();
                bootstrap.Modal.getInstance(document.getElementById('editMemberModal')).hide();
                if (d.success) {
                    showMsg('會員資料更新成功！');
                    document.getElementById('messageModal').addEventListener('hidden.bs.modal', () => location.reload(), { once: true });
                } else {
                    showMsg('更新失敗：' + (d.message || '未知錯誤'));
                }
            } catch {
                showMsg('發生錯誤，請稍後再試');
            }
        });

        /* -------- 開啟重設密碼 -------- */
        document.getElementById('openResetPwdModal').addEventListener('click', () => {
            resetPwdModal.show();
            document.getElementById('passwordResetForm').reset();
        });

        /* -------- 密碼重設 -------- */
        document.getElementById('passwordResetForm').addEventListener('submit', async ev => {
            ev.preventDefault();
            const fd = Object.fromEntries(new FormData(ev.target).entries());
            if (fd.newPassword !== fd.confirmPassword) {
                showMsg('新密碼與確認密碼不一致');
                return;
            }
            try {
                const res = await fetch('api/order.php?action=resetPassword', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        memberId: CONFIG.memberId,
                        oldPassword: fd.oldPassword,
                        newPassword: fd.newPassword
                    })
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
                    showMsg('密碼已更新，請使用新密碼重新登入');
                    document.getElementById('messageModal').addEventListener('hidden.bs.modal', () => location.href = 'logout.php', { once: true });
                } else {
                    showMsg('密碼更新失敗：' + data.message);
                }
            } catch {
                showMsg('網路或伺服器錯誤，請稍後再試');
            }
        });

        // ========== 維修申請與付款 Modal 互動腳本 ==========
        document.querySelectorAll('.submit-repair').forEach(button => {
            button.addEventListener('click', function () {
                const orderId = this.dataset.orderId;
                const form = document.getElementById(`repairForm${orderId}`);
                const selectedWarranties = Array.from(form.querySelectorAll('input[name="warranty_ids[]"]:checked'))
                    .map(checkbox => checkbox.value);

                if (selectedWarranties.length === 0) {
                    showMsg('請選擇要維修的商品');
                    return;
                }

                // 發送維修申請
                const repairCommit = document.getElementById(`repairCommit${orderId}`).value;
                fetch('api/order.php?action=createRepair', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        warrantyIds: selectedWarranties,
                        repairCommit: repairCommit
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 先關閉申請維修的 modal
                            const openRepairModal = document.querySelector('.modal.show[id^="repairModal"]');
                            if (openRepairModal) {
                                bootstrap.Modal.getInstance(openRepairModal).hide();
                            }
                            // 顯示成功提示
                            showMsg('維修申請已送出！');
                            setTimeout(() => { location.reload(); }, 1000);
                        } else {
                            showMsg(data.message || '送出失敗，請稍後再試');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMsg('發生錯誤，請稍後再試');
                    });
            });
        });

        // 付款按鈕事件監聽
        document.querySelectorAll('.submit-payment').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('paymentRepairId').value = this.dataset.repairId;
                const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                paymentModal.show();
            });
        });

        // 確認付款按鈕事件
        document.getElementById('confirmPaymentBtn').addEventListener('click', async function () {
            const repairId = document.getElementById('paymentRepairId').value;
            // 呼叫後端 API 更新狀態
            try {
                const res = await fetch('api/order.php?action=updateRepairStatus', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ repairId: repairId })
                });
                const data = await res.json();
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                if (data.success) {
                    showMsg('付款成功！');
                    document.getElementById('messageModal').addEventListener('hidden.bs.modal', () => location.reload(), { once: true });
                } else {
                    showMsg('付款失敗：' + (data.message || '未知錯誤'));
                }
            } catch {
                showMsg('網路或伺服器錯誤，請稍後再試');
            }
        });
    })();
</script>