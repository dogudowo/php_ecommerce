<?php
session_start();

// 檢查用戶是否已登入，如果沒有則直接重定向到登入頁面
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 如果是管理員，重定向到後台管理頁面
if ($_SESSION['user_role'] === 'admin') {
    header('Location: backend.php');
    exit;
}
require_once __DIR__ . '/php/APIs.php';
$memberId = $_SESSION['user_id'];
$orders = getMemberOrders($memberId);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/webStruct.css">
    <style>
        html,
        body {
            overflow-x: hidden;
            overflow-y: scroll;
        }
    </style>
    <title>歐印精品-我的訂單</title>
</head>

<body>
    <?php include "header.php"; ?>
    <div class="container text-center border-bottom border-black my-3  flex-grow-1 pt-5">
        <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
        <h1 class="h1">我的訂單</h1>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h3>會員資訊</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $memberDetails = getMembers();
                        $currentMember = array_filter($memberDetails, function ($member) {
                            return $member['MemberID'] == $_SESSION['user_id'];
                        });
                        $currentMember = reset($currentMember);
                        ?>
                        <h4><?php echo htmlspecialchars($currentMember['membername']); ?></h4>
                        <p class="mb-0"><strong>身份：</strong> <?php echo htmlspecialchars($currentMember['role']); ?></p>
                        <p class="mb-0"><strong>手機號碼：</strong>
                            <?php echo htmlspecialchars($currentMember['phonenum']); ?></p>
                        <p class="mb-0"><strong>電子郵件：</strong>
                            <?php echo htmlspecialchars($currentMember['memberemail']); ?></p>
                        <p class="mb-0"><strong>訂單數量：</strong>
                            <?php echo htmlspecialchars($currentMember['order_count']); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h3>您的訂單</h3>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="orderTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-success active" id="pending-tab" data-bs-toggle="tab"
                                    data-bs-target="#pending" type="button" role="tab" aria-controls="pending"
                                    aria-selected="true">進行中訂單</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-success" id="completed-tab" data-bs-toggle="tab"
                                    data-bs-target="#completed" type="button" role="tab" aria-controls="completed"
                                    aria-selected="false">已完成訂單</button>
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

    <!-- 添加 Modal 結構 -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">系統訊息</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalMessage">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">確定</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 付款確認 Modal -->
    <div class="modal fade" id="paymentConfirmModal" tabindex="-1" aria-labelledby="paymentConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentConfirmModalLabel">確認付款</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <!-- <h5>訂單編號: ORD-<?= $order['OrderID'] ?? '' ?></h5> -->
                        <p>訂單總金額: NT$<?= number_format($order['TotalAmount'] ?? 0) ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">請選擇付款方式：</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="creditCard"
                                value="credit_card" checked>
                            <label class="form-check-label" for="creditCard">信用卡付款</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="atmTransfer"
                                value="atm_transfer">
                            <label class="form-check-label" for="atmTransfer">ATM轉帳</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="linePay"
                                value="linePay">
                            <label class="form-check-label" for="linePay">Line Pay</label>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        請確認已完成付款程序，系統將在收到款項後盡快處理您的訂單。
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="confirmPaymentBtn">確認</button>
                </div>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 初始化所有 Modal
        const messageModalEl = document.getElementById('messageModal');
        const paymentConfirmModalEl = document.getElementById('paymentConfirmModal');

        let messageModal = null;
        let paymentConfirmModal = null;

        if (messageModalEl) {
            messageModal = new bootstrap.Modal(messageModalEl);
        }
        if (paymentConfirmModalEl) {
            paymentConfirmModal = new bootstrap.Modal(paymentConfirmModalEl);
        }

        let currentOrderId = null;

        // 處理再買一次按鈕
        document.querySelectorAll('.reorder-btn').forEach(button => {
            button.addEventListener('click', function () {
                const orderId = this.getAttribute('data-order-id');

                fetch('php/APIs.php?action=reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        memberId: <?php echo $_SESSION['user_id']; ?>,
                        orderId: orderId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'shoppingCart.php';
                        } else {
                            document.getElementById('modalMessage').textContent = '再買一次失敗: ' + data.message;
                            messageModal.show();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('modalMessage').textContent = '發生錯誤，請稍後再試';
                        messageModal.show();
                    });
            });
        });

        // 處理付款按鈕
        document.querySelectorAll('.payment-btn').forEach(button => {
            button.addEventListener('click', function () {
                console.log('付款按钮被点击');
                currentOrderId = this.getAttribute('data-order-id');
                const orderAmount = this.getAttribute('data-order-amount');

                // 更新Modal中的订单信息
                document.querySelector('#paymentConfirmModal h5').textContent = `訂單編號: ORD-${currentOrderId}`;
                document.querySelector('#paymentConfirmModal p').textContent = `訂單總金額: NT$${orderAmount}`;

                paymentConfirmModal.show();
            });
        });

        // 處理付款確認
        document.getElementById('confirmPaymentBtn').addEventListener('click', function () {
            if (!currentOrderId) return;

            fetch('php/APIs.php?action=updateOrderStatus', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: currentOrderId,
                    new_status: 'Completed',
                    memberId: <?php echo $_SESSION['user_id']; ?>
                })
            })
                .then(response => response.json())
                .then(data => {
                    paymentConfirmModal.hide();
                    if (data.success) {
                        document.getElementById('modalMessage').textContent = '付款成功！';
                        messageModal.show();
                        document.getElementById('messageModal').addEventListener('hidden.bs.modal', function () {
                            location.reload();
                        }, { once: true });
                    } else {
                        document.getElementById('modalMessage').textContent = '付款處理失敗: ' + data.message;
                        messageModal.show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    paymentConfirmModal.hide();
                    document.getElementById('modalMessage').textContent = '發生錯誤，請稍後再試';
                    messageModal.show();
                });
        });
    </script>
</body>

</html>