<?php
declare(strict_types=1);
session_start();

/* ---------- 小工具：安全輸出 ---------- */
function h(mixed $val): string
{
    return htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
}

/* ---------- 登入 & 角色驗證 ---------- */
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

/* ---------- 相依檔 ---------- */
require_once __DIR__ . '/api/Member.php';
require_once __DIR__ . '/api/order.php';

/* ---------- 取資料 ---------- */
$memberId = (int) $_SESSION['user_id'];
$currentMember = getMemberByID($memberId);       // 來自 Member.php（已使用 PDO）
$orders = getMemberOrders($memberId);     // 來自 order.php  （已使用 PDO）

?>

<?php include __DIR__. '/fixedFile/header.php';?>

<!-- ========= Main ========= -->
<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
    <h1 class="h1">我的訂單</h1>
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
                    <p class="mb-0"><strong>身份：</strong><?= $currentMember['role'] == 0 ? '一般會員' : '管理員' ?></p>
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
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending"
                                type="button">進行中</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completed"
                                type="button">已完成</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="orderTabsContent">
                        <?php renderOrderTab($orders, 'Pending', '進行中'); ?>
                        <?php renderOrderTab($orders, 'Completed', '已完成'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========== 共用訊息 Modal ========== -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">系統訊息</h5>
            </div>
            <div class="modal-body" id="modalMessage"></div>
            <div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">確定</button>
            </div>
        </div>
    </div>
</div>

<!-- 付款確認 Modal -->
<div class="modal fade" id="paymentConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="paymentConfirmModalLabel" class="modal-title">確認付款</h5>
            </div>
            <div class="modal-body" id="paymentModalBody"></div>
            <div class="modal-body">
                <h6 class="mb-3">選擇付款方式</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="creditCard" value="信用卡" checked>
                    <label class="form-check-label" for="creditCard">
                        信用卡
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="atmTransfer" value="ATM轉帳">
                    <label class="form-check-label" for="atmTransfer">
                        ATM轉帳
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="linePay" value="LinePay">
                    <label class="form-check-label" for="linePay">
                        LinePay
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button class="btn btn-primary" id="confirmPaymentBtn">確認</button>
            </div>
        </div>
    </div>
</div>

<!-- 編輯會員資料 Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="memberEditForm">
                <div class="modal-header">
                    <h5 class="modal-title">編輯會員資料</h5>
                </div>
                <div class="modal-body">
                    <?php
                    $fields = [
                        'name' => ['姓名', 'membername'],
                        'phone' => ['電話', 'phonenum'],
                        'email' => ['電子郵件', 'memberemail'],
                        'city' => ['城市', 'City'],
                        'address' => ['地址', 'Address'],
                    ];
                    foreach ($fields as $name => [$label, $src]): ?>
                        <div class="mb-3">
                            <label class="form-label"><?= $label ?></label>
                            <input type="<?= $name === 'email' ? 'email' : 'text' ?>" class="form-control" name="<?= $name ?>"
                                value="<?= h($currentMember[$src]) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button class="btn btn-primary" id="saveMemberChanges">儲存變更</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/fixedFile/footer.php';

/* ---------- 將 PHP 參數輸出到 JS ---------- */
echo '<script>';
echo 'const CONFIG = ' . json_encode([
    'memberId' => $memberId,
    'isAdmin' => $_SESSION['user_role'] === 'admin',
    'orderAmountMap' => array_column($orders, 'TotalAmount', 'OrderID'),
], JSON_UNESCAPED_UNICODE) . ';';
echo '</script>';
?>

<!-- ========== 功能腳本 ========== -->
<script>
    (() => {
        const msgModal = new bootstrap.Modal('#messageModal');
        const payModal = new bootstrap.Modal('#paymentConfirmModal');
        let currentOrderId = null;

        const showMsg = t => {
            document.getElementById('modalMessage').textContent = t;
            msgModal.show();
        };

        /* 事件委派：再買一次 / 付款 */
        document.addEventListener('click', async ev => {
            const el = ev.target.closest('[data-action]');
            if (!el) return;
            const act = el.dataset.action;

            if (act === 'reorder') {
                await reorder(el.dataset.orderId);
            } else if (act === 'pay') {
                preparePay(el.dataset.orderId);
            }
        });

        async function reorder(orderId) {
            try {
                const res = await fetch('api/order.php?action=reorder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ memberId: CONFIG.memberId, orderId })
                });
                const data = await res.json();
                if (data.success) location.href = 'cart.php';
                else showMsg(`再買一次失敗：${data.message}`);
            } catch (e) {
                console.error(e); showMsg('發生錯誤，請稍後再試');
            }
        }

        function preparePay(orderId) {
            currentOrderId = orderId;
            const amt = CONFIG.orderAmountMap[orderId] ?? 0;
            document.getElementById('paymentConfirmModalLabel').textContent = `訂單編號: ORD-${orderId}`;
            document.getElementById('paymentModalBody').innerHTML = `<p>訂單總金額: NT$${amt.toLocaleString()}</p>`;
            payModal.show();
        }

        document.getElementById('confirmPaymentBtn').addEventListener('click', async () => {
            if (!currentOrderId) return;
            try {
                // 獲取選擇的付款方式
                const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
                
                const res = await fetch('api/order.php?action=updateOrderStatus', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        memberId: CONFIG.memberId,
                        order_id: currentOrderId,
                        new_status: 'Completed',
                        paymentMethod: paymentMethod
                    })
                });
                const data = await res.json();
                payModal.hide();
                if (data.success) {
                    showMsg('付款成功！');
                    document.getElementById('messageModal').addEventListener('hidden.bs.modal', () => location.reload(), { once: true });
                } else showMsg(`更新失敗：${data.message}`);
            } catch (e) {
                console.error(e); payModal.hide(); showMsg('發生錯誤，請稍後再試');
            }
        });

        /* 會員編輯 */
        document.getElementById('memberEditForm').addEventListener('submit', async ev => {
            ev.preventDefault();
            const payload = Object.fromEntries(new FormData(ev.target).entries());
            payload.memberId = CONFIG.memberId;
            payload.isAdmin = CONFIG.isAdmin ? 1 : 0;

            if (!payload.name || !payload.phone || !payload.email)
                return showMsg('請填寫所有必填欄位');

            try {
                const res = await fetch('api/order.php?action=updateMember', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                const editModal = bootstrap.Modal.getInstance('#editMemberModal');
                
                if (data.success) {
                    editModal.hide();
                    showMsg('會員資料更新成功！');
                    document.getElementById('messageModal').addEventListener('hidden.bs.modal', () => location.reload(), { once: true });
                } else {
                    editModal.hide();
                    showMsg(`更新失敗：${data.message ?? '未知錯誤'}`);
                    document.getElementById('messageModal').addEventListener('hidden.bs.modal', () => {
                        editModal.show();
                    }, { once: true });
                }
            } catch (e) {
                console.error(e); 
                const editModal = bootstrap.Modal.getInstance('#editMemberModal');
                editModal.hide();
                showMsg('發生錯誤，請稍後再試');
                document.getElementById('messageModal').addEventListener('hidden.bs.modal', () => {
                    editModal.show();
                }, { once: true });
            }
        });
    })();
</script>