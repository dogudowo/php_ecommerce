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
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container my-5">
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
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>商品</th>
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
                            <button type="submit" class="btn btn-primary w-100 btn-lg">確認結帳</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>