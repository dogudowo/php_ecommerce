<?php
session_start();
require_once __DIR__ . '/php/APIs.php';

// 檢查用戶是否登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$memberId = $_SESSION['user_id']; // 假設session中存儲了用戶ID
$cartItems = getCartItems($memberId);
$total = 0;

// 計算總金額
foreach ($cartItems as $item) {
    $total += $item['Price'] * $item['Quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>購物車 - 歐印精品</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container my-5">
        <h2 class="mb-4">我的購物車</h2>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info">您的購物車是空的</div>
            <a href="index.php" class="btn btn-primary">繼續購物</a>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>商品名稱</th>
                            <th>規格</th>
                            <th>單價</th>
                            <th>數量</th>
                            <th>小計</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['ProductName']) ?></td>
                                <td>
                                    <?= htmlspecialchars($item['Color']) ?>
                                    <?= $item['Size'] != -1 ? ' - ' . htmlspecialchars($item['SizeDescription']) : '' ?>
                                </td>
                                <td>$<?= number_format($item['Price']) ?></td>
                                <td>
                                    <input type="number" class="form-control quantity-input" value="<?= $item['Quantity'] ?>"
                                        min="1" data-option-id="<?= $item['OptionID'] ?>">
                                </td>
                                <td>$<?= number_format($item['Price'] * $item['Quantity']) ?></td>
                                <td>
                                    <button class="btn btn-danger btn-sm remove-item"
                                        data-option-id="<?= $item['OptionID'] ?>">移除</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <h4>總金額: $<?= number_format($total) ?></h4>
                <a href="checkout.php" class="btn btn-primary btn-lg mt-3">結帳</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 這裡可以添加購物車操作的JavaScript代碼
    </script>
</body>

</html>