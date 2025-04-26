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
    <div class="container text-center border-bottom border-black my-3">
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

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                            window.location.href = 'shoppingCart.php';  // 確保成功後跳轉到購物車頁面
                        } else {
                            alert('再買一次失敗: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('發生錯誤，請稍後再試');
                    });
            });
        });
    </script>
</body>

</html>