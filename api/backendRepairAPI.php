<?php
/*  後端維修訂單 API + 共用函式
 *  1. 被 include 時：僅提供函式
 *  2. 直接執行時：處理 AJAX 更新維修狀態
 */
require_once __DIR__ . '/db.php';

/* ========= 共用常數 / 函式 ========= */

function getRepairStatusList(): array
{
    return [
        'pending' => '待處理',
        'repairing' => '維修中',
        'pending_payment' => '待付款',
        'completed' => '已完成',
        'abnormal' => '異常'
    ];
}

function getRepairStatusClass(string $status): string
{
    return match ($status) {
        'completed' => 'bg-success',
        'pending' => 'bg-info text-dark',
        'repairing' => 'bg-primary',
        'abnormal' => 'bg-danger',
        'pending_payment' => 'bg-warning',
        default => 'bg-secondary',
    };
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

function fetchRepairOrders(?string $status = null, ?string $memberId = null, ?string $dateFrom = null, ?string $dateTo = null): array
{
    $pdo = getDBConnection();
    $sql = <<<SQL
        SELECT r.RepairID, r.RepairDate, r.RepairStatus, r.RepairCommit, r.Price,
               w.WarrantyID, w.WarrantyDate,
               m.Name  AS MemberName,
               o.ProductName
        FROM Repairs       r
        JOIN Warranty      w   USING (WarrantyID)
        JOIN ReceiptItem   ri  USING (WarrantyID)
        JOIN OrderRecord   o   ON ri.OrderRecordID = o.RecordID
        JOIN Receipt       rec ON ri.ReceiptID     = rec.ReceiptID
        JOIN Member        m   ON rec.MemberID     = m.MemberID
        WHERE 1 = 1
    SQL;
    $params = [];

    if ($status !== null && $status !== '') {
        $sql .= ' AND r.RepairStatus = :status';
        $params['status'] = $status;
    }
    if ($memberId !== null && $memberId !== '') {
        $sql .= ' AND m.MemberID = :memberId';
        $params['memberId'] = $memberId;
    }
    if ($dateFrom !== null && $dateFrom !== '') {
        $sql .= ' AND r.RepairDate >= :dateFrom';
        $params['dateFrom'] = $dateFrom;
    }
    if ($dateTo !== null && $dateTo !== '') {
        $sql .= ' AND r.RepairDate <= :dateTo';
        $params['dateTo'] = $dateTo;
    }

    $sql .= ' ORDER BY r.RepairDate DESC';

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[fetchRepairOrders] ' . $e->getMessage());
        return [];
    }
}

function updateRepairStatus(int $repairId, string $newStatus, ?int $price = null): bool
{
    $pdo = getDBConnection();
    $sql = 'UPDATE Repairs SET RepairStatus = :status';
    $params = ['status' => $newStatus, 'repairId' => $repairId];

    // 如果有提供價格，則更新價格
    if ($price !== null) {
        $sql .= ', Price = :price';
        $params['price'] = $price;
    }

    $sql .= ' WHERE RepairID = :repairId';

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log('[updateRepairStatus] ' . $e->getMessage());
        return false;
    }
}

/* ========= 如果直接執行，視為 AJAX ========= */
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json; charset=utf-8');

    // 處理獲取維修訂單價格的請求
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_repair_price') {
        $repairId = isset($_GET['repair_id']) ? (int) $_GET['repair_id'] : null;
        
        if (!$repairId) {
            echo json_encode(['success' => false, 'message' => '缺少參數']);
            exit;
        }

        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare('SELECT Price FROM Repairs WHERE RepairID = :id');
            $stmt->execute(['id' => $repairId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'price' => $result ? $result['Price'] : null
            ]);
        } catch (PDOException $e) {
            error_log('[getRepairPrice] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => '資料庫查詢失敗']);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $repairId = isset($_POST['repair_id']) ? (int) $_POST['repair_id'] : null;
    $newStatus = $_POST['new_status'] ?? null;
    $price = null;
    if (isset($_POST['repair_price']) && $_POST['repair_price'] !== '') {
        $price = (int) $_POST['repair_price'];
        // 如果轉換後為0，但原始輸入不是0，則可能是轉換錯誤
        if ($price === 0 && $_POST['repair_price'] !== '0') {
            $price = null;
        }
    }

    // 記錄接收到的資料
    error_log(sprintf(
        '[updateRepairStatus] 接收到的資料：repair_id=%s, new_status=%s, repair_price=%s, 轉換後價格=%s',
        $repairId,
        $newStatus,
        $_POST['repair_price'] ?? '未提供',
        $price === null ? 'null' : $price
    ));

    if (!$repairId || !$newStatus) {
        echo json_encode(['success' => false, 'message' => '缺少參數']);
        exit;
    }

    if (updateRepairStatus($repairId, $newStatus, $price)) {
        $statusList = getRepairStatusList();
        $response = [
            'success' => true,
            'statusText' => $statusList[$newStatus] ?? $newStatus,
            'statusClass' => getRepairStatusClass($newStatus),
        ];
        // 記錄更新成功的回應
        error_log('[updateRepairStatus] 更新成功：' . json_encode($response));
        echo json_encode($response);
    } else {
        $error = ['success' => false, 'message' => '資料庫更新失敗'];
        // 記錄更新失敗
        error_log('[updateRepairStatus] 更新失敗：' . json_encode($error));
        echo json_encode($error);
    }
    exit;
}