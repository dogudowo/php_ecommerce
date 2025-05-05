<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';   // 需提供 db_connect(): PDO

// 只在直接訪問時設置 JSON 頭，而不是在被引入時
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
}

/* =========  API 入口  ========= */
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

if ($method === 'GET' && $action === 'getMemberOrders') {
    if (!isset($_GET['memberId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少 memberId 參數']);
        exit;
    }
    $orders = getMemberOrders((int) $_GET['memberId']);
    echo json_encode(['success' => true, 'data' => $orders]);
    exit;
}

if ($method === 'POST' && $action === 'createOrder') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    foreach (['memberId', 'name', 'phone', 'address'] as $key) {
        if (empty($input[$key])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "缺少必要參數：{$key}"]);
            exit;
        }
    }
    $result = createOrder(
        (int) $input['memberId'],
        $input['name'],
        $input['phone'],
        $input['address'],
        $input['note'] ?? ''
    );
    echo json_encode($result);
    exit;
}

if ($method === 'POST' && $action === 'reorder') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($input['memberId']) || empty($input['orderId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少必要參數']);
        exit;
    }
    $result = reorder((int) $input['memberId'], (int) $input['orderId']);
    echo json_encode($result);
    exit;
}

if ($method === 'POST' && $action === 'updateOrderStatus') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($input['memberId']) || empty($input['order_id']) || empty($input['new_status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少必要參數']);
        exit;
    }
    $result = updateOrderStatus(
        (int) $input['memberId'],
        (int) $input['order_id'],
        $input['new_status'],
        $input['paymentMethod'] ?? '信用卡'
    );
    echo json_encode($result);
    exit;
}

if ($method === 'POST' && $action === 'updateMember') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($input['memberId']) || empty($input['name']) || empty($input['phone']) || empty($input['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少必要參數']);
        exit;
    }
    $result = updateMember(
        (int) $input['memberId'],
        $input['name'],
        $input['phone'],
        $input['email'],
        $input['city'] ?? '',
        $input['address'] ?? '',
        (bool) ($input['isAdmin'] ?? false)
    );
    echo json_encode($result);
    exit;
}

/* =========  共用工具  ========= */
function getStatusClass(string $status): string
{
    return [
        'Completed' => 'bg-success',
        'Pending' => 'bg-info text-dark',
        'Shipping' => 'bg-primary',
        'Cancel' => 'bg-warning',
        'abnormal' => 'bg-danger',
    ][$status] ?? 'bg-secondary';
}

function getStatusText(string $status): string
{
    return [
        'Completed' => '已完成',
        'Pending' => '處理中',
        'Shipping' => '運送中',
        'Cancel' => '已取消',
        'abnormal' => '異常',
    ][$status] ?? $status;
}

/* =========  資料存取  ========= */


function getOrderDetails(int $orderId, ?PDO $pdo = null): array
{
    $pdo = $pdo ?? db_connect();

    // 1. 明細
    $detailSql = <<<SQL
        SELECT  p.ProductName,
                p.ProductID,
                opt.Color,
                opt.Size,
                opt.SizeDescription,
                opt.Price,
                oi.Quantity,
                (opt.Price * oi.Quantity) AS Subtotal
        FROM    OrderItem oi
        JOIN    Options   opt ON oi.OptionID = opt.OptionID
        JOIN    Product   p   ON opt.ProductID = p.ProductID
        WHERE   oi.OrderID = :orderId
    SQL;
    $stmt = $pdo->prepare($detailSql);
    $stmt->execute(['orderId' => $orderId]);
    $items = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['image'] = "imgs/products/{$row['ProductID']}/main.jpg";
        $items[] = $row;
    }

    // 2. 狀態
    $status = $pdo->prepare("SELECT Status FROM Orders WHERE OrderID = :orderId");
    $status->execute(['orderId' => $orderId]);
    $status = $status->fetchColumn() ?: 'unknown';

    return [
        'items' => $items,
        'total_amount' => array_sum(array_column($items, 'Subtotal')),
        'status' => $status,
        'statusClass' => getStatusClass($status),
        'statusText' => getStatusText($status),
    ];
}

/* ========  前端渲染（若需要） ======== */
function renderOrderTab(array $orders, string $status, string $title, ?PDO $pdo = null): void
{
    $pdo = $pdo ?? db_connect();

    // 進行中(Pending & Shipping) vs 其他
    $filtered = ($status === 'Pending')
        ? array_filter($orders, fn($o) => in_array($o['Status'], ['Pending', 'Shipping'], true))
        : array_filter($orders, fn($o) => !in_array($o['Status'], ['Pending', 'Shipping'], true));

    echo '<div class="tab-pane fade ' . ($status === 'Pending' ? 'show active' : '') .
        '" id="' . strtolower($status) . '" role="tabpanel" aria-labelledby="' .
        strtolower($status) . '-tab">';

    if ($filtered) {
        echo '<div class="list-group">';
        foreach ($filtered as $order) {
            $detail = getOrderDetails((int) $order['OrderID'], $pdo);
            ?>
            <div class="list-group-item mb-4 border rounded">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">訂單號碼: ORD-<?= $order['OrderID'] ?></h5>
                    <small><?= $order['OrderDate'] ?></small>
                </div>
                <?php foreach ($detail['items'] as $item): ?>
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-3" style="width:60px;height:60px;">
                            <img src="<?= $item['image'] ?>" alt="<?= $item['ProductName'] ?>" class="img-fluid"
                                style="max-height:100%;">
                        </div>
                        <div>
                            <p class="mb-1"><?= $item['ProductName'] ?></p>
                            <p class="mb-0 text-secondary">規格: <?= "{$item['Color']}, {$item['SizeDescription']}" ?></p>
                            <p class="mb-0">數量: <?= $item['Quantity'] ?> 件</p>
                            <p class="mb-0">單價: <span class="text-danger fw-bold">$<?= $item['Price'] ?></span></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                    <span class="badge fs-6 <?= getStatusClass($order['Status']) ?>">
                        <?= getStatusText($order['Status']) ?>
                    </span>
                    <div class="text-center fw-bold fs-5 text-danger">總計 $<?= number_format($detail['total_amount']) ?></div>
                    <div class="d-flex align-items-center">
                        <?php if ($status === 'Pending'): ?>
                            <button class="btn btn-success btn-sm" data-action="pay"
                                data-order-id="<?= $order['OrderID'] ?>">付款</button>
                        <?php else: ?>
                            <button class="btn btn-primary btn-sm me-2" data-action="reorder"
                                data-order-id="<?= $order['OrderID'] ?>">再買一次</button>
                            <button class="btn btn-warning btn-sm" data-action="repair"
                                data-order-id="<?= $order['OrderID'] ?>">維修</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo '<div class="no-products-alert alert alert-info text-center mt-4">⚠️ 目前沒有' . $title . '的訂單</div>';
    }
    echo '</div>';
}

/* =========  建立訂單 ========= */
function createOrder(int $memberId, string $name, string $phone, string $address, string $note = ''): array
{
    $pdo = db_connect();
    try {
        $pdo->beginTransaction();

        /* 1. 建立 Orders */
        // 1. 從Member表獲取會員資訊
        $memberInfo = $pdo->prepare("SELECT Name, Phone, Address FROM Member WHERE MemberID = :memberId");
        $memberInfo->execute(['memberId' => $memberId]);
        $member = $memberInfo->fetch(PDO::FETCH_ASSOC);

        // 2. 獲取最大OrderID並加1
        $stmt = $pdo->query("SELECT MAX(OrderID) as maxId FROM Orders");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $orderId = $result['maxId'] ? $result['maxId'] + 1 : 1;

        // 3. 建立訂單(只包含必要欄位)
        $stmt = $pdo->prepare("
            INSERT INTO Orders (OrderID, MembersID, Status, Note, OrderDate)
            VALUES (:orderId, :memberId, 'Pending', :note, NOW())
        ");
        $stmt->execute([
            'orderId' => $orderId,
            'memberId' => $memberId,
            'note' => $note
        ]);

        /* 2. 取得購物車列表並寫入 OrderItem */
        $cartItems = getCartItems($memberId, $pdo);
        $itemSql = "INSERT INTO OrderItem (OrderID, OptionID, Quantity) VALUES (:orderId, :optionId, :qty)";
        $itemStmt = $pdo->prepare($itemSql);
        foreach ($cartItems as $item) {
            $itemStmt->execute([
                'orderId' => $orderId,
                'optionId' => $item['OptionID'],
                'qty' => $item['Quantity'],
            ]);
        }

        /* 3. 清空購物車 */
        $pdo->prepare("DELETE FROM CartItem WHERE MemberID = :memberId")
            ->execute(['memberId' => $memberId]);

        $pdo->commit();
        return ['success' => true, 'orderId' => $orderId];

    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('創建訂單失敗：' . $e->getMessage());
        return ['success' => false, 'message' => '創建訂單失敗'];
    }
}

/* =========  購物車 ========= */
function getCartItems(int $memberId, ?PDO $pdo = null): array
{
    $pdo = $pdo ?? db_connect();

    $sql = <<<SQL
        SELECT  ci.Quantity,
                ci.OptionID,
                p.ProductName,
                p.ProductID,
                o.Price,
                o.Color,
                o.SizeDescription,
                o.Size
        FROM    CartItem ci
        JOIN    Options  o ON ci.OptionID = o.OptionID
        JOIN    Product  p ON o.ProductID = p.ProductID
        WHERE   ci.MemberID = :memberId
    SQL;
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['memberId' => $memberId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========  再買一次 ========= */
function reorder(int $memberId, int $orderId): array
{
    $pdo = db_connect();
    try {
        $pdo->beginTransaction();

        // 1. 檢查訂單是否存在且屬於該會員
        $checkOrder = $pdo->prepare("SELECT OrderID FROM Orders WHERE OrderID = :orderId AND MembersID = :memberId");
        $checkOrder->execute(['orderId' => $orderId, 'memberId' => $memberId]);
        if (!$checkOrder->fetch()) {
            return ['success' => false, 'message' => '訂單不存在或不屬於該會員'];
        }

        // 2. 獲取訂單項目
        $getItems = $pdo->prepare("SELECT OptionID, Quantity FROM OrderItem WHERE OrderID = :orderId");
        $getItems->execute(['orderId' => $orderId]);
        $items = $getItems->fetchAll(PDO::FETCH_ASSOC);

        if (empty($items)) {
            return ['success' => false, 'message' => '訂單項目為空'];
        }

        // 3. 清空購物車
        $clearCart = $pdo->prepare("DELETE FROM CartItem WHERE MemberID = :memberId");
        $clearCart->execute(['memberId' => $memberId]);

        // 4. 將訂單項目加入購物車
        $addToCart = $pdo->prepare("INSERT INTO CartItem (MemberID, OptionID, Quantity) VALUES (:memberId, :optionId, :quantity)");
        foreach ($items as $item) {
            $addToCart->execute([
                'memberId' => $memberId,
                'optionId' => $item['OptionID'],
                'quantity' => $item['Quantity']
            ]);
        }

        $pdo->commit();
        return ['success' => true, 'message' => '已將商品加入購物車'];
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('再買一次失敗：' . $e->getMessage());
        return ['success' => false, 'message' => '操作失敗：' . $e->getMessage()];
    }
}

/* =========  更新訂單狀態 ========= */
function updateOrderStatus(int $memberId, int $orderId, string $newStatus, string $paymentMethod = '信用卡'): array
{
    $pdo = db_connect();
    try {
        $pdo->beginTransaction();

        // 1. 檢查訂單是否存在且屬於該會員
        $checkOrder = $pdo->prepare("SELECT OrderID FROM Orders WHERE OrderID = :orderId AND MembersID = :memberId");
        $checkOrder->execute(['orderId' => $orderId, 'memberId' => $memberId]);
        if (!$checkOrder->fetch()) {
            return ['success' => false, 'message' => '訂單不存在或不屬於該會員'];
        }

        // 2. 更新訂單狀態
        $updateOrder = $pdo->prepare("UPDATE Orders SET Status = :status WHERE OrderID = :orderId");
        $updateOrder->execute(['status' => $newStatus, 'orderId' => $orderId]);

        // 3. 如果狀態為已完成，創建收據
        if ($newStatus === 'Completed') {
            // 獲取最大ReceiptID並加1
            $stmt = $pdo->query("SELECT MAX(ReceiptID) as maxId FROM Receipt");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $receiptId = $result['maxId'] ? $result['maxId'] + 1 : 1;

            // 創建收據
            $createReceipt = $pdo->prepare("INSERT INTO Receipt (ReceiptID, MemberID, OrderID, PaymentMethod, PaymentDate) VALUES (:receiptId, :memberId, :orderId, :paymentMethod, NOW())");
            $createReceipt->execute([
                'receiptId' => $receiptId,
                'memberId' => $memberId,
                'orderId' => $orderId,
                'paymentMethod' => $paymentMethod
            ]);

            // 獲取訂單項目並創建收據項目
            $getItems = $pdo->prepare("SELECT OptionID FROM OrderItem WHERE OrderID = :orderId");
            $getItems->execute(['orderId' => $orderId]);
            $items = $getItems->fetchAll(PDO::FETCH_ASSOC);

            $createReceiptItem = $pdo->prepare("INSERT INTO ReceiptItem (ReceiptID, OptionID) VALUES (:receiptId, :optionId)");
            foreach ($items as $item) {
                $createReceiptItem->execute([
                    'receiptId' => $receiptId,
                    'optionId' => $item['OptionID']
                ]);
            }
        }

        $pdo->commit();
        return ['success' => true, 'message' => '訂單狀態已更新'];
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('更新訂單狀態失敗：' . $e->getMessage());
        return ['success' => false, 'message' => '操作失敗：' . $e->getMessage()];
    }
}

/* =========  更新會員資料 ========= */
function updateMember(int $memberId, string $name, string $phone, string $email, string $city = '', string $address = '', bool $isAdmin = false): array
{
    $pdo = db_connect();
    try {
        // 檢查會員是否存在
        $checkMember = $pdo->prepare("SELECT MemberID FROM Member WHERE MemberID = :memberId");
        $checkMember->execute(['memberId' => $memberId]);
        if (!$checkMember->fetch()) {
            return ['success' => false, 'message' => '會員不存在'];
        }

        // 檢查姓名是否重複
        $checkName = $pdo->prepare("SELECT MemberID FROM Member WHERE Name = :name AND MemberID != :memberId");
        $checkName->execute(['name' => $name, 'memberId' => $memberId]);
        if ($checkName->fetch()) {
            return ['success' => false, 'message' => '姓名已被使用，請使用其他姓名'];
        }

        // 檢查電話是否重複
        $checkPhone = $pdo->prepare("SELECT MemberID FROM Member WHERE Phone = :phone AND MemberID != :memberId");
        $checkPhone->execute(['phone' => $phone, 'memberId' => $memberId]);
        if ($checkPhone->fetch()) {
            return ['success' => false, 'message' => '電話號碼已被使用，請使用其他電話號碼'];
        }

        // 更新會員資料
        $updateMember = $pdo->prepare("UPDATE Member SET Name = :name, Phone = :phone, Email = :email, City = :city, Address = :address, IsAdmin = :isAdmin WHERE MemberID = :memberId");
        $updateMember->execute([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'city' => $city,
            'address' => $address,
            'isAdmin' => $isAdmin ? 1 : 0,
            'memberId' => $memberId
        ]);

        return ['success' => true, 'message' => '會員資料已更新'];
    } catch (Throwable $e) {
        error_log('更新會員資料失敗：' . $e->getMessage());
        return ['success' => false, 'message' => '操作失敗：' . $e->getMessage()];
    }
}
?>