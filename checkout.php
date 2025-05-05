<?php
session_start();

// 檢查用戶是否登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$memberId = $_SESSION['user_id'];

// 獲取會員資訊
$memberInfo = [];
require_once __DIR__ . '/api/db.php';
try {
    $db = getDBConnection();

    $stmt = $db->prepare("SELECT Name, Phone, City, Address FROM Member WHERE MemberID = ?");
    $stmt->execute([$memberId]);
    $memberInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$memberInfo) {
        throw new Exception("找不到會員資訊");
    }

    // 測試輸出會員資訊
    error_log("獲取到的會員資訊: " . print_r($memberInfo, true));
} catch (Exception $e) {
    error_log("獲取會員資訊失敗: " . $e->getMessage());
    $memberInfo = ['Name' => '', 'Phone' => '', 'City' => '', 'Address' => ''];
}

// 獲取購物車數據
$cartItems = [];
$total = 0;
try {
    $response = file_get_contents("http://localhost/new_test/api/cart.php?action=getCartItems&memberId=$memberId");
    $data = json_decode($response, true);
    if ($data['success'] && !empty($data['data'])) {
        $cartItems = $data['data'];

        // 獲取總金額
        $totalResponse = file_get_contents("http://localhost/new_test/api/cart.php?action=getCartTotal&memberId=$memberId");
        $totalData = json_decode($totalResponse, true);
        if ($totalData['success']) {
            $total = $totalData['total'];
        }
    }
} catch (Exception $e) {
    error_log("獲取購物車數據失敗: " . $e->getMessage());
}

include __DIR__ . "/fixedFile/header.php";
?>

<div class="container mt-3">
    <h2 class="mb-4">結帳</h2>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">您的購物車是空的</div>
        <a href="index.php" class="btn btn-primary">繼續購物</a>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">購物車商品</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="row mb-3">
                                <div class="col-3">
                                    <img src="<?php echo $item['image']; ?>" class="img-fluid" alt="商品圖片">
                                </div>
                                <div class="col-6">
                                    <h6><?php echo htmlspecialchars($item['ProductName']); ?></h6>
                                    <p class="mb-1">
                                        <small class="text-muted">
                                            <?= htmlspecialchars($item['Color']) ?>
                                            <?= $item['Size'] != -1 ? ' - ' . htmlspecialchars($item['SizeDescription']) : '' ?>
                                        </small>
                                    </p>
                                    <p class="mb-1">數量: <?= $item['Quantity'] ?></p>
                                    <p class="mb-0">單價: $<?= number_format($item['Price']) ?></p>
                                </div>
                                <div class="col-3 text-end">
                                    <p class="fw-bold">$<?= number_format($item['Price'] * $item['Quantity']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">收件人資訊</h5>
                    </div>
                    <div class="card-body">
                        <form id="checkoutForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">收件人姓名</label>
                                <input type="text" class="form-control" id="name"
                                    value="<?php echo htmlspecialchars($memberInfo['Name'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">聯絡電話</label>
                                <input type="tel" class="form-control" id="phone"
                                    value="<?php echo htmlspecialchars($memberInfo['Phone'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">城市</label>
                                <input type="text" class="form-control" id="city"
                                    value="<?php echo htmlspecialchars($memberInfo['City'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">收件地址</label>
                                <input type="text" class="form-control" id="address"
                                    value="<?php echo htmlspecialchars($memberInfo['Address'] ?? ''); ?>" disabled required>
                            </div>
                            <div class="mb-3">
                                <label for="note" class="form-label">備註</label>
                                <textarea class="form-control" id="note" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">訂單摘要</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>商品總計</span>
                            <span>$<?= number_format($total) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>運費</span>
                            <span>$0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>總金額</span>
                            <span>$<?= number_format($total) ?></span>
                        </div>
                        <button id="submitOrder" class="btn btn-primary w-100 mt-3">確認結帳</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include "fixedFile/footer.php"; ?>

<script>
    // 提交訂單
    const submitOrder = document.getElementById('submitOrder');
    if (submitOrder) {
        submitOrder.addEventListener('click', async function () {
            const name = document.getElementById('name').value;
            const phone = document.getElementById('phone').value;
            const city = document.getElementById('city').value;
            const address = document.getElementById('address').value;
            const note = document.getElementById('note').value;

            if (!name || !phone || !city || !address) {
                alert('請填寫完整的收件人資訊');
                return;
            }

            try {
                const response = await fetch('api/order.php?action=createOrder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        memberId: <?php echo $memberId; ?>,
                        name: name,
                        phone: phone,
                        address: address + (city ? ', ' + city : ''),
                        note: note
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-50 start-50 translate-middle-x mt-3';
                    alertDiv.innerHTML = `
                        <strong>訂單創建成功！</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.body.appendChild(alertDiv);
                    setTimeout(() => {
                        window.location.href = 'myOrder.php';
                    }, 500);
                } else {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-50 start-50 translate-middle-x mt-3';
                    alertDiv.innerHTML = `
                        <strong>訂單提交失敗: ${data.message || '未知錯誤'}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.body.appendChild(alertDiv);
                }
            } catch (error) {
                console.error('Error:', error);
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-50 start-50 translate-middle-x mt-3';
                alertDiv.innerHTML = `
                        <strong>訂單提交失敗，請稍後再試</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                document.body.appendChild(alertDiv);
            }
        });
    }
</script>