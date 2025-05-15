<?php
declare(strict_types=1);
require_once __DIR__ . '/error_handler.php';

require_once __DIR__ . '/db.php';   // 需提供 db_connect(): PDO

// 只在直接訪問時設置 JSON 頭，而不是在被引入時
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
}

/* =========  API 入口  ========= */
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

// 獲取訂單詳情
if ($method === 'GET' && $action === 'getOrderDetails') {
    if (!isset($_GET['orderId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少 orderId 參數']);
        exit;
    }
    $orderDetails = getOrderDetails((int) $_GET['orderId']);
    echo json_encode(['success' => true, 'data' => $orderDetails]);
    exit;
}

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

// 篩選訂單
if ($method === 'GET' && $action === 'filter') {
    $status = $_GET['status'] ?? null;
    $dateFrom = $_GET['dateFrom'] ?? null;
    $dateTo = $_GET['dateTo'] ?? null;
    $search = $_GET['search'] ?? null;

    $orders = filterOrders($status, $dateFrom, $dateTo, $search);
    echo json_encode(['success' => true, 'orders' => $orders]);
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
if ($action === 'resetPassword' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'message' => '未收到有效 JSON']);
        exit;
    }

    $memberId = (int) ($input['memberId'] ?? 0);
    $oldPassword = $input['oldPassword'] ?? '';
    $newPassword = $input['newPassword'] ?? '';

    if (!$memberId || !$oldPassword || !$newPassword) {
        echo json_encode(['success' => false, 'message' => '資訊不足']);
        exit;
    }

    try {
        $pdo = getDBConnection();

        /* 1) 驗證舊密碼 */
        $stmt = $pdo->prepare('SELECT Password FROM Member WHERE MemberID = ?');
        $stmt->execute([$memberId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($oldPassword, $row['Password'])) {
            echo json_encode(['success' => false, 'message' => '舊密碼錯誤']);
            exit;
        }

        /* 2) 更新成新密碼 */
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE Member SET Password = ? WHERE MemberID = ?');
        $upd->execute([$newHash, $memberId]);

        echo json_encode(['success' => true]);
    } catch (Throwable $e) {
        error_log('resetPassword: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '伺服器錯誤，請稍後再試']);
    }
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
        'Shipping' => '已送達',
        'Cancel' => '已取消',
        'abnormal' => '異常',
    ][$status] ?? $status;
}

/* =========  資料存取  ========= */

function filterOrders(?string $status = null, ?string $dateFrom = null, ?string $dateTo = null, ?string $search = null, ?PDO $pdo = null): array
{
    $pdo = $pdo ?? db_connect();

    $sql = "SELECT o.OrderID, m.Name AS MemberName, o.OrderDate, 
                   COUNT(oi.OrderID) AS ItemCount, SUM(oi.Quantity * orec.ProductPrice) AS TotalAmount, o.Status
            FROM Orders o
            JOIN Member m ON o.MemberID = m.MemberID
            LEFT JOIN OrderItem oi ON o.OrderID = oi.OrderID
            LEFT JOIN OrderRecord orec ON oi.OrderRecordID = orec.RecordID
            WHERE 1=1";

    $params = [];

    if ($status) {
        $sql .= " AND o.Status = :status";
        $params['status'] = $status;
    }

    if ($dateFrom) {
        $sql .= " AND o.OrderDate >= :dateFrom";
        $params['dateFrom'] = $dateFrom;
    }

    if ($dateTo) {
        $sql .= " AND o.OrderDate <= :dateTo";
        $params['dateTo'] = $dateTo;
    }

    if ($search) {
        $sql .= " AND (o.OrderID LIKE :search OR m.Name LIKE :search)";
        $params['search'] = "%$search%";
    }

    $sql .= " GROUP BY o.OrderID";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderDetails(int $orderId, ?PDO $pdo = null): array
{
    $pdo = $pdo ?? db_connect();

    // 1. 明細
    $detailSql = <<<SQL
        SELECT  orec.ProductName,
                orec.ProductType as ProductID,
                orec.ProductColor as Color,
                orec.ProductSize as Size,
                orec.ProductPrice as Price,
                oi.Quantity,
                (orec.ProductPrice * oi.Quantity) AS Subtotal
        FROM    OrderItem oi
        JOIN    OrderRecord orec ON oi.OrderRecordID = orec.RecordID
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
                <?php
                foreach ($detail['items'] as $item): ?>
                    <div class="d-flex align-items-center mb-2 border-top mt-2 pt-2">
                        <div>
                            <p class="mb-1 h5"><?= $item['ProductName'] ?></p>
                            <p class="mb-0">顏色: <?= "{$item['Color']}" ?></p>
                            <p class="mb-0">數量: <?= $item['Quantity'] ?> 件</p>
                            <p class="mb-0">單價: $<?= $item['Price'] ?></p>
                        </div>
                    </div>
                    <?php
                endforeach; ?>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                    <span class="badge fs-6 <?= getStatusClass($order['Status']) ?>">
                        <?= getStatusText($order['Status']) ?>
                    </span>
                    <div class="text-center fw-bold fs-5 text-danger">總計 $<?= number_format($detail['total_amount']) ?></div>
                    <div class="d-flex align-items-center">
                        <?php
                        if ($status === 'Pending'): ?>
                            <?php
                            if ($order['Status'] === 'Shipping'): ?>
                                <button class="btn btn-success btn-sm" data-action="pay"
                                    data-order-id="<?= $order['OrderID'] ?>">付款</button>
                                <?php
                            elseif ($order['Status'] === 'Pending'): ?>
                                <button class="btn btn-secondary btn-sm" data-action="pay" data-order-id="<?= $order['OrderID'] ?>"
                                    disabled>付款</button>
                                <?php
                            endif; ?>
                            <?php
                        else: ?>
                            <button class="btn btn-warning btn-sm" data-action="repair"
                                data-order-id="<?= $order['OrderID'] ?>">維修</button>
                            <?php
                        endif; ?>
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
            INSERT INTO Orders (OrderID, MemberID, Status, Notes, OrderDate)
            VALUES (:orderId, :memberId, 'Pending', :note, NOW())
        ");
        $stmt->execute([
            'orderId' => $orderId,
            'memberId' => $memberId,
            'note' => $note
        ]);

        /* 2. 取得購物車列表並寫入 OrderRecord 和 OrderItem */
        $cartItems = getCartItems($memberId, $pdo);

        // 先插入 OrderRecord
        // 獲取最大RecordID並加1
        $stmt = $pdo->query("SELECT MAX(RecordID) as maxId FROM OrderRecord");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $recordId = $result['maxId'] ? $result['maxId'] + 1 : 1;

        $recordSql = "INSERT INTO OrderRecord (RecordID, ProductName, ProductType, ProductColor, ProductSize, ProductPrice, OrderID) 
                      VALUES (:recordId, :productName, :productType, :color, :size, :price, :orderId)";
        $recordStmt = $pdo->prepare($recordSql);

        // 再插入 OrderItem
        $itemSql = "INSERT INTO OrderItem (OrderID, OrderRecordID, Quantity) VALUES (:orderId, :recordId, :qty)";
        $itemStmt = $pdo->prepare($itemSql);

        foreach ($cartItems as $item) {
            // 插入 OrderRecord
            $recordStmt->execute([
                'recordId' => $recordId,
                'productName' => $item['ProductName'],
                'productType' => $item['Type'] ?? '',
                'color' => $item['Color'],
                'size' => $item['Size'],
                'price' => $item['Price'],
                'orderId' => $orderId
            ]);

            // 插入 OrderItem
            $itemStmt->execute([
                'orderId' => $orderId,
                'recordId' => $recordId,
                'qty' => $item['Quantity']
            ]);

            // 更新商品庫存
            $pdo->prepare("UPDATE Options SET Stock = Stock - :quantity WHERE OptionID = :optionId")
                ->execute([
                    'quantity' => $item['Quantity'],
                    'optionId' => $item['OptionID']
                ]);

            // 更新 RecordID 為下一個值
            $recordId++;
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
                p.Type,
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

/* =========  更新訂單狀態 ========= */

function updateOrderStatus(int $memberId, int $orderId, string $newStatus, string $paymentMethod = '信用卡'): array
{
    $pdo = db_connect();

    try {
        $pdo->beginTransaction();

        // 1. 確認訂單歸屬
        $checkOrder = $pdo->prepare(
            "SELECT OrderID FROM Orders WHERE OrderID = :orderId AND MemberID = :memberId"
        );
        $checkOrder->execute(['orderId' => $orderId, 'memberId' => $memberId]);
        if (!$checkOrder->fetch()) {
            $pdo->rollBack();
            return ['success' => false, 'message' => '訂單不存在或不屬於該會員'];
        }

        // 2. 更新訂單狀態
        $updateOrder = $pdo->prepare(
            "UPDATE Orders SET Status = :status WHERE OrderID = :orderId"
        );
        $updateOrder->execute(['status' => $newStatus, 'orderId' => $orderId]);

        // 3. 若完成付款，建立收據與保固
        if ($newStatus === 'Completed') {
            // 3-1. 產生收據 ID
            $receiptId = $pdo->query(
                "SELECT IFNULL(MAX(CAST(ReceiptID AS UNSIGNED)), 0) + 1 AS newId FROM Receipt"
            )->fetchColumn();

            // 3-2. 新增收據
            $createReceipt = $pdo->prepare(
                "INSERT INTO Receipt
                 (ReceiptID, OrderID, MemberID, PaymentMethod, PaymentDate)
                 VALUES (:receiptId, :orderId, :memberId, :paymentMethod, NOW())"
            );
            $createReceipt->execute([
                'receiptId' => $receiptId,
                'orderId' => $orderId,
                'memberId' => $memberId,
                'paymentMethod' => $paymentMethod
            ]);

            // 3-3. 建立保固並關聯到收據項目
            $items = $pdo->prepare(
                "SELECT OrderRecordID, Quantity FROM OrderItem WHERE OrderID = :orderId"
            );
            $items->execute(['orderId' => $orderId]);
            $items = $items->fetchAll(PDO::FETCH_ASSOC);

            $createWarranty = $pdo->prepare(
                "INSERT INTO warranty (WarrantyID, WarrantyDate, WarrantyStatus)
                 VALUES (:warrantyId, DATE_ADD(NOW(), INTERVAL 1 YEAR), true)"
            );
            $createReceiptItem = $pdo->prepare(
                "INSERT INTO ReceiptItem (ReceiptID, OrderRecordID, WarrantyID)
                 VALUES (:receiptId, :orderRecordId, :warrantyId)"
            );

            foreach ($items as $item) {
                for ($i = 0; $i < $item['Quantity']; $i++) {
                    $warrantyId = $pdo->query(
                        "SELECT IFNULL(MAX(WarrantyID), 0) + 1 AS newId FROM warranty"
                    )->fetchColumn();

                    $createWarranty->execute(['warrantyId' => $warrantyId]);
                    $createReceiptItem->execute([
                        'receiptId' => $receiptId,
                        'orderRecordId' => $item['OrderRecordID'],
                        'warrantyId' => $warrantyId
                    ]);
                }
            }
        }

        // 關鍵紀錄：交易提交
        $pdo->commit();
        return ['success' => true, 'message' => '訂單狀態已更新'];
    } catch (Throwable $e) {
        // 關鍵紀錄：交易回滾
        $pdo->rollBack();
        error_log('[updateOrderStatus] Rollback due to error: ' . $e->getMessage());
        return ['success' => false, 'message' => '操作失敗：' . $e->getMessage()];
    }
}

/* ========= 更新會員資料 ========= */
function updateMember(
    int $memberId,
    string $name,
    string $phone,
    string $email,
    string $city = '',
    string $address =
    '',
    bool $isAdmin = false
): array {
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
        $updateMember = $pdo->prepare("UPDATE Member SET Name = :name, Phone = :phone, Email = :email, City = :city, Address =
:address WHERE MemberID = :memberId");
        $updateMember->execute([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'city' => $city,
            'address' => $address,
            'memberId' => $memberId
        ]);

        return ['success' => true, 'message' => '會員資料已更新'];
    } catch (Throwable $e) {
        error_log('更新會員資料失敗：' . $e->getMessage());
        return ['success' => false, 'message' => '操作失敗：' . $e->getMessage()];
    }
}
?>