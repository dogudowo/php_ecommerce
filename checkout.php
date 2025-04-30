<?php
session_start();
require_once __DIR__ . '/php/APIs.php';

// 检查用户是否登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$memberId = $_SESSION['user_id'];
$cartItems = getCartItems($memberId);
$total = 0;

// 计算总金额
foreach ($cartItems as $item) {
    $total += $item['Price'] * $item['Quantity'];
}

// 处理结账请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = checkout($memberId);
    if ($result['success']) {
        header("Location: myOrder.php?order_id=" . $result['orderId']);
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>結帳 - 歐印精品</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/webStruct.css">
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container my-5 flex-grow-1 pt-5">
        <h2 class="mb-4">結帳</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h4>訂單明細</h4>
                    </div>
                    <div class="card-body">
                        <table class="table align-items-center">
                            <thead>
                                <tr>
                                    <th>商品圖片</th>
                                    <th>商品名稱</th>
                                    <th>規格</th>
                                    <th>單價</th>
                                    <th>數量</th>
                                    <th>小計</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                                    alt="<?php echo htmlspecialchars($item['ProductName']); ?>" width="80"
                                                    height="80" style="object-fit: cover; object-position: center;"
                                                    class="me-2">
                                            <?php endif; ?>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <?php echo htmlspecialchars($item['ProductName']); ?>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <?= htmlspecialchars($item['Color']) ?>
                                            <?php if ($item['Size'] != -1): ?>
                                                <br><small
                                                    class="text-muted"><?= htmlspecialchars($item['SizeDescription']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="vertical-align: middle;">$<?php echo number_format($item['Price']); ?>
                                        </td>
                                        <td style="vertical-align: middle;"><?php echo $item['Quantity']; ?></td>
                                        <td style="vertical-align: middle;">
                                            $<?php echo number_format($item['Price'] * $item['Quantity']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 新增收件人資訊區塊 -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h4>收件人資訊</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        // 獲取當前會員資訊
                        require_once __DIR__ . '/php/APIs.php';
                        $memberDetails = getMembers();
                        $currentMember = array_filter($memberDetails, function ($member) {
                            return $member['MemberID'] == $_SESSION['user_id'];
                        });
                        $currentMember = reset($currentMember);
                        ?>
                        <div class="mb-3">
                            <label class="form-label">姓名</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($currentMember['membername']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">手機號碼</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($currentMember['phonenum']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">電子郵件</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($currentMember['memberemail']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">送貨地址</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($currentMember['City'] . $currentMember['Address']); ?>"
                                disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class=" col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h4>訂單總計</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>商品總計</span>
                            <span>$<?php echo number_format($total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>運費</span>
                            <span>$0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5>總計</h5>
                            <h5>$<?php echo number_format($total); ?></h5>
                        </div>
                        <form method="POST" class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 btn-lg">確認下單</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <!-- 新增確認Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">確認訂單</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    您確定要提交此訂單嗎？
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="confirmOrder">確認下單</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form[method="POST"]');
            const submitBtn = form.querySelector('button[type="submit"]');

            submitBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
                modal.show();
            });

            document.getElementById('confirmOrder').addEventListener('click', function () {
                form.submit();
            });
        });
    </script>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>