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
    foreach (['memberId', 'name', 'phone', 'address', 'paymentMethod'] as $key) {
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
        $input['note'] ?? '',
        $input['paymentMethod']
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

    $sql .= " GROUP BY o.OrderID ORDER BY o.OrderID DESC";

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
function renderRepairTab(array $orders, string $title, ?PDO $pdo = null): void
{
    $pdo = $pdo ?? db_connect();

    echo '<div class="tab-pane fade" id="repairs" role="tabpanel" aria-labelledby="repairs-tab">';

    // 收集所有維修記錄
    $allRepairs = [];
    foreach ($orders as $order) {
        $warranties = getOrderWarranties((int) $order['OrderID'], $pdo);
        foreach ($warranties as $warranty) {
            if (isset($warranty['RepairID'])) {
                $allRepairs[] = [
                    'order' => $order,
                    'repair' => $warranty
                ];
            }
        }
    }

    if (empty($allRepairs)) {
        echo '<div class="no-products-alert alert alert-info text-center mt-4">⚠️ 目前沒有維修記錄</div>';
    } else {
        // 按 RepairID 由大到小排序（最新的在前）
        usort($allRepairs, function ($a, $b) {
            return (int) $b['repair']['RepairID'] - (int) $a['repair']['RepairID'];
        });

        echo '<div class="list-group">';
        foreach ($allRepairs as $item) {
            $repair = $item['repair'];
            $order = $item['order'];
            ?>
            <div class="list-group-item mb-4 border rounded">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">維修單號: REP-<?= $repair['RepairID'] ?></h5>
                    <small><?= $repair['RepairDate'] ?></small>
                </div>
                <div class="d-flex align-items-center mb-2 border-top mt-2 pt-2">
                    <div>
                        <p class="mb-1 h5"><?= htmlspecialchars($repair['ProductName']) ?></p>
                        <p class="mb-0">保固編號:
                            <span>
                                <?= $repair['WarrantyID'] ?>
                            </span>
                        </p>
                        <?php if ($repair['RepairCommit']): ?>
                            <p class="mb-0">維修說明: <?= htmlspecialchars($repair['RepairCommit']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                    <div class="badge <?= getRepairStatusClass($repair['RepairStatus']) ?>">
                        <?= getRepairStatusText($repair['RepairStatus']) ?>
                    </div>
                    <?php if ($repair['Price'] !== null): ?>
                        <div class="text-center fw-bold fs-5 text-danger">維修金額 $<?= number_format($repair['Price']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    }

    echo '</div>';
}

function renderOrderTab(array $orders, string $status, string $title, ?PDO $pdo = null): void
{
    $pdo = $pdo ?? db_connect();

    // 進行中(Pending & Shipping) vs 已完成(Completed)
    $filtered = ($status === 'Pending')
        ? array_filter($orders, function ($o) use ($pdo) {
            // 檢查訂單狀態
            $orderStatus = in_array($o['Status'], ['Pending', 'Shipping'], true);

            // 檢查維修狀態
            $warranties = getOrderWarranties((int) $o['OrderID'], $pdo);
            $hasActiveRepair = false;
            foreach ($warranties as $warranty) {
                if (
                    isset($warranty['RepairStatus']) &&
                    in_array($warranty['RepairStatus'], ['pending', 'repairing', 'pending_payment'], true)
                ) {
                    $hasActiveRepair = true;
                    break;
                }
            }

            return $orderStatus || $hasActiveRepair;
        })
        : array_filter($orders, function ($o) {
            // 已完成頁面只檢查訂單狀態是否為 Completed
            return $o['Status'] === 'Completed';
        });

    echo '<div class="tab-pane fade ' . ($status === 'Pending' ? 'show active' : '') .
        '" id="' . strtolower($status) . '" role="tabpanel" aria-labelledby="' .
        strtolower($status) . '-tab">';

    if ($filtered) {
        echo '<div class="list-group">';
        foreach ($filtered as $order) {
            $detail = getOrderDetails((int) $order['OrderID'], $pdo);
            $warranties = getOrderWarranties((int) $order['OrderID'], $pdo);
            $activeRepairs = array_filter($warranties, function ($w) {
                return isset($w['RepairStatus']) &&
                    in_array($w['RepairStatus'], ['pending', 'repairing', 'pending_payment'], true);
            });

            // 如果是進行中頁面且有維修項目，先顯示維修項目
            if ($status === 'Pending' && !empty($activeRepairs)) {
                foreach ($activeRepairs as $repair) {
                    ?>
                    <div class="list-group-item mb-4 border rounded">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">維修單號: REP-<?= $repair['RepairID'] ?></h5>
                            <small><?= $repair['RepairDate'] ?></small>
                        </div>
                        <div class="d-flex align-items-center mb-2 border-top mt-2 pt-2">
                            <div>
                                <p class="mb-1 h5"><?= $repair['ProductName'] ?></p>
                                <p class="mb-0">保固編號:
                                    <span>
                                        <?= $repair['WarrantyID'] ?>
                                    </span>
                                </p>
                                <?php if ($repair['RepairCommit']): ?>
                                    <p class="mb-0">維修說明: <?= htmlspecialchars($repair['RepairCommit']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <div class="badge <?= getRepairStatusClass($repair['RepairStatus']) ?>">
                                <?= getRepairStatusText($repair['RepairStatus']) ?>
                            </div>
                            <?php if ($repair['Price'] !== null): ?>
                                <div class="text-center fw-bold fs-5 text-danger">維修金額 $<?= number_format($repair['Price']) ?></div>
                            <?php endif; ?>
                            <div class="d-flex align-items-center">
                                <?php if ($repair['RepairStatus'] === 'pending_payment'): ?>
                                    <button class="btn btn-success btn-sm submit-payment" data-repair-id="<?= $repair['RepairID'] ?>">
                                        付款
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }

            // 如果是進行中頁面且訂單狀態為 Pending 或 Shipping，顯示訂單
            if ($status === 'Pending' && in_array($order['Status'], ['Pending', 'Shipping'], true)) {
                renderOrderItem($order, $detail);
            }

            // 如果是已完成頁面，顯示訂單
            if ($status !== 'Pending') {
                renderOrderItem($order, $detail);
            }
        }
        echo '</div>';
    } else {
        echo '<div class="no-products-alert alert alert-info text-center mt-4">⚠️ 目前沒有' . $title . '的訂單</div>';
    }
    echo '</div>';
}

// 新增一個輔助函數來渲染訂單項目
function renderOrderItem($order, $detail)
{
    ?>
    <div class="list-group-item mb-4 border rounded">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">訂單號碼: ORD-<?= $order['OrderID'] ?></h5>
            <small><?= $order['OrderDate'] ?></small>
        </div>
        <?php foreach ($detail['items'] as $item): ?>
            <div class="d-flex align-items-center mb-2 border-top mt-2 pt-2">
                <div>
                    <p class="mb-1 h5"><?= $item['ProductName'] ?></p>
                    <p class="mb-0">顏色: <?= "{$item['Color']}" ?></p>
                    <p class="mb-0">數量: <?= $item['Quantity'] ?> 件</p>
                    <p class="mb-0">單價: $<?= $item['Price'] ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <div class="badge <?= getStatusClass($order['Status']) ?>">
                <?php echo htmlspecialchars(getStatusText($order['Status'])); ?>
            </div>
            <div class="text-center fw-bold fs-5 text-danger">總計 $<?= number_format($detail['total_amount']) ?></div>
            <div class="d-flex align-items-center">
                <?php if ($order['Status'] === 'Completed'): ?>
                    <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal"
                        data-bs-target="#repairModal<?= $order['OrderID'] ?>">
                        <i class="bi bi-tools"></i> 申請維修
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($order['Status'] === 'Completed'): ?>
        <!-- 維修申請 Modal -->
        <div class="modal fade" id="repairModal<?= $order['OrderID'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">申請維修 - 訂單 #<?= $order['OrderID'] ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="repairForm<?= $order['OrderID'] ?>">
                            <div class="mb-3">
                                <label class="form-label">選擇要維修的商品：</label>
                                <?php
                                $warranties = getOrderWarranties((int) $order['OrderID']);
                                $uniqueWarranties = [];
                                foreach ($warranties as $warranty) {
                                    if (!isset($uniqueWarranties[$warranty['WarrantyID']])) {
                                        $uniqueWarranties[$warranty['WarrantyID']] = $warranty;
                                    }
                                }
                                foreach ($uniqueWarranties as $warranty):
                                    // 檢查保固是否真的有效（檢查到期日）
                                    $warrantyDate = strtotime($warranty['WarrantyDate']);
                                    $isWarrantyValid = $warranty['WarrantyStatus'] && $warrantyDate > time();
                                    $warrantyStatus = $isWarrantyValid ? '有效' : '已過期';
                                    $warrantyClass = $isWarrantyValid ? 'text-success' : 'text-danger';

                                    // 檢查是否有進行中的維修
                                    $hasActiveRepair = isset($warranty['RepairStatus']) &&
                                        in_array($warranty['RepairStatus'], ['pending', 'repairing', 'pending_payment'], true);

                                    // 檢查商品類型是否為鋁合金或拉鍊
                                    $isRepairableType = in_array($warranty['ProductType'], ['aluminum', 'zipper']);

                                    // 如果沒有進行中的維修且商品類型符合要求，則顯示該商品
                                    if (!$hasActiveRepair && $isRepairableType):
                                        ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="warranty_ids[]"
                                                value="<?= $warranty['WarrantyID'] ?>" id="warranty<?= $warranty['WarrantyID'] ?>">
                                            <label class="form-check-label" for="warranty<?= $warranty['WarrantyID'] ?>">
                                                <?= htmlspecialchars($warranty['ProductName']) ?>
                                                <span class="ms-2 text-muted">(保固編號: <?= $warranty['WarrantyID'] ?>)</span>
                                                <br>
                                                <small class="ms-4">
                                                    <span class="<?= $warrantyClass ?>">
                                                        <i class="bi <?= $isWarrantyValid ? 'bi-shield-check' : 'bi-shield-x' ?>"></i>
                                                        保固狀態：<?= $warrantyStatus ?>
                                                    </span>
                                                    <br>
                                                    <span class="text-muted ms-4">
                                                        到期日：<?= date('Y-m-d', strtotime($warranty['WarrantyDate'])) ?>
                                                    </span>
                                                    <?php if (!$isWarrantyValid): ?>
                                                        <br>
                                                        <span class="text-warning ms-4">
                                                            <i class="bi bi-exclamation-triangle"></i>
                                                            注意：此商品已過保，維修將收取額外費用
                                                        </span>
                                                    <?php endif; ?>
                                                </small>
                                            </label>
                                        </div>
                                        <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">維修說明：</label>
                                <textarea class="form-control" id="repairCommit<?= $order['OrderID'] ?>" rows="3"
                                    placeholder="請描述商品需要維修的問題..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary submit-repair"
                            data-order-id="<?= $order['OrderID'] ?>">送出維修申請</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php
}

/* =========  建立訂單 ========= */
function createOrder(int $memberId, string $name, string $phone, string $address, string $note = '', string $paymentMethod = ''): array
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

        /* 4. 建立收據 */
        $receiptResult = createReceipt($memberId, $orderId, $paymentMethod, $pdo);
        if (!$receiptResult['success']) {
            $pdo->rollBack();
            return ['success' => false, 'message' => $receiptResult['message']];
        }

        $pdo->commit();
        return ['success' => true, 'orderId' => $orderId, 'receiptId' => $receiptResult['receiptId']];

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

function createReceipt(int $memberId, int $orderId, string $paymentMethod, PDO $pdo): array
{
    try {
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
            "INSERT INTO Warranty (WarrantyID, WarrantyDate, WarrantyStatus)
             VALUES (:warrantyId, DATE_ADD(NOW(), INTERVAL 1 YEAR), true)"
        );
        $createReceiptItem = $pdo->prepare(
            "INSERT INTO ReceiptItem (ReceiptID, OrderRecordID, WarrantyID)
             VALUES (:receiptId, :orderRecordId, :warrantyId)"
        );

        foreach ($items as $item) {
            for ($i = 0; $i < $item['Quantity']; $i++) {
                $warrantyId = $pdo->query(
                    "SELECT IFNULL(MAX(WarrantyID), 0) + 1 AS newId FROM Warranty"
                )->fetchColumn();

                $createWarranty->execute(['warrantyId' => $warrantyId]);
                $createReceiptItem->execute([
                    'receiptId' => $receiptId,
                    'orderRecordId' => $item['OrderRecordID'],
                    'warrantyId' => $warrantyId
                ]);
            }
        }
        return ['success' => true, 'receiptId' => $receiptId];
    } catch (Throwable $e) {
        error_log('[createReceipt] Error: ' . $e->getMessage());
        return ['success' => false, 'message' => '建立收據失敗: ' . $e->getMessage()];
    }
}

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

        // 3. 若完成付款，更新相關維修訂單狀態 (收據和保固已在 createOrder 或手動觸發 updateOrderStatus 時建立)
        if ($newStatus === 'Completed') {
            // 檢查是否已存在收據，如果不存在，則建立它 (這種情況可能發生在手動將訂單標記為已完成時)
            $checkReceipt = $pdo->prepare("SELECT ReceiptID FROM Receipt WHERE OrderID = :orderId");
            $checkReceipt->execute(['orderId' => $orderId]);
            if (!$checkReceipt->fetch()) {
                $receiptResult = createReceipt($memberId, $orderId, $paymentMethod, $pdo);
                if (!$receiptResult['success']) {
                    $pdo->rollBack();
                    return ['success' => false, 'message' => '在更新訂單狀態時建立收據失敗: ' . $receiptResult['message']];
                }
            }

            // 3-4. 更新相關維修訂單狀態
            $updateRepairs = $pdo->prepare("
                UPDATE Repairs r
                JOIN ReceiptItem ri ON r.WarrantyID = ri.WarrantyID
                JOIN Receipt rc ON ri.ReceiptID = rc.ReceiptID
                SET r.RepairStatus = '已完成'
                WHERE rc.OrderID = :orderId 
                AND r.RepairStatus = '已送達'
            ");
            $updateRepairs->execute(['orderId' => $orderId]);
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

// 添加獲取訂單保固資訊的函數
function getOrderWarranties(int $orderId, ?PDO $pdo = null): array
{
    $pdo = $pdo ?? db_connect();

    // 簡化查詢，先確保基本資料能正確獲取
    $sql = <<<SQL
        SELECT  w.WarrantyID,
                w.WarrantyDate,
                w.WarrantyStatus,
                orec.ProductName,
                orec.ProductType,
                r.RepairID,
                r.RepairDate,
                r.RepairStatus,
                r.RepairCommit,
                r.Price
        FROM    Orders o
        JOIN    OrderRecord orec ON o.OrderID = orec.OrderID
        JOIN    ReceiptItem ri ON orec.RecordID = ri.OrderRecordID
        JOIN    Warranty w ON ri.WarrantyID = w.WarrantyID
        LEFT JOIN Repairs r ON w.WarrantyID = r.WarrantyID
        WHERE   o.OrderID = :orderId
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['orderId' => $orderId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $results;
}

// 添加處理維修申請的函數
function createRepairRequest(array $warrantyIds, ?PDO $pdo = null, string $repairCommit = ''): array
{
    $pdo = $pdo ?? db_connect();
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->query("SELECT MAX(RepairID) as maxId FROM Repairs");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $repairId = $result['maxId'] ? $result['maxId'] + 1 : 1;

        $createRepair = $pdo->prepare("
            INSERT INTO Repairs (RepairID, WarrantyID, RepairDate, RepairStatus, RepairCommit, Price) 
            VALUES (:repairId, :warrantyId, NOW(), :status, :repairCommit, :price)
        ");

        foreach ($warrantyIds as $warrantyId) {
            // 檢查保固狀態
            $checkWarranty = $pdo->prepare("
                SELECT WarrantyStatus, WarrantyDate 
                FROM Warranty 
                WHERE WarrantyID = :warrantyId
            ");
            $checkWarranty->execute(['warrantyId' => $warrantyId]);
            $warranty = $checkWarranty->fetch(PDO::FETCH_ASSOC);

            // 判斷是否在保固期內
            $isWarrantyValid = $warranty['WarrantyStatus'] && strtotime($warranty['WarrantyDate']) > time();

            $createRepair->execute([
                'repairId' => $repairId,
                'warrantyId' => $warrantyId,
                'status' => 'pending',
                'repairCommit' => $repairCommit,
                'price' => 0 // 不論保固狀態，預設都為 0
            ]);
            $repairId++;
        }

        $pdo->commit();
        return ['success' => true, 'message' => '維修申請已送出'];
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('創建維修申請失敗：' . $e->getMessage());
        return ['success' => false, 'message' => '操作失敗：' . $e->getMessage()];
    }
}

// 添加API端點處理維修申請
if ($method === 'POST' && $action === 'createRepair') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($input['warrantyIds'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '請選擇要維修的商品']);
        exit;
    }
    $repairCommit = $input['repairCommit'] ?? '';
    $result = createRepairRequest($input['warrantyIds'], null, $repairCommit);
    echo json_encode($result);
    exit;
}

// 添加處理付款的 API 端點
if ($method === 'POST' && $action === 'updateRepairStatus') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    if (empty($input['repairId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '缺少必要參數']);
        exit;
    }

    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("UPDATE Repairs SET RepairStatus = 'completed' WHERE RepairID = :repairId");
        $stmt->execute(['repairId' => $input['repairId']]);

        echo json_encode(['success' => true, 'message' => '付款狀態已更新']);
    } catch (Throwable $e) {
        error_log('更新維修狀態失敗：' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => '操作失敗：' . $e->getMessage()]);
    }
    exit;
}

// 添加維修狀態相關的輔助函數
function getRepairStatusClass(string $status): string
{
    return [
        'pending' => 'bg-secondary',
        'repairing' => 'bg-primary',
        'completed' => 'bg-success',
        'abnormal' => 'bg-danger',
        'pending_payment' => 'bg-warning'
    ][$status] ?? 'bg-secondary';
}

function getRepairStatusText(string $status): string
{
    return [
        'pending' => '待處理',
        'repairing' => '維修中',
        'completed' => '已完成',
        'abnormal' => '異常',
        'pending_payment' => '待付款'
    ][$status] ?? $status;
}