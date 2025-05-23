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
        '已完成' => '已完成',
        '處理中' => '處理中',
        '維修中' => '維修中',
        '已取消' => '已取消',
        '異常' => '異常',
    ];
}

function getRepairStatusClass(string $status): string
{
    return match ($status) {
        '已完成' => 'bg-success',
        '處理中' => 'bg-info text-dark',
        '維修中' => 'bg-primary',
        '已取消' => 'bg-warning',
        '異常' => 'bg-danger',
        default => 'bg-secondary',
    };
}

function fetchRepairOrders(?string $status = null, ?string $memberId = null): array
{
    $pdo = getDBConnection();
    $sql = <<<SQL
        SELECT r.RepairID, r.RepairDate, r.RepairStatus, r.RepairCommit,
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

function updateRepairStatus(int $repairId, string $newStatus): bool
{
    $pdo = getDBConnection();
    $sql = 'UPDATE Repairs SET RepairStatus = :status WHERE RepairID = :id';

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['status' => $newStatus, 'id' => $repairId]);
    } catch (PDOException $e) {
        error_log('[updateRepairStatus] ' . $e->getMessage());
        return false;
    }
}

/* ========= 如果直接執行，視為 AJAX ========= */
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $repairId = isset($_POST['repair_id']) ? (int) $_POST['repair_id'] : null;
    $newStatus = $_POST['new_status'] ?? null;

    if (!$repairId || !$newStatus) {
        echo json_encode(['success' => false, 'message' => '缺少參數']);
        exit;
    }

    if (updateRepairStatus($repairId, $newStatus)) {
        echo json_encode([
            'success' => true,
            'statusText' => $newStatus,
            'statusClass' => getRepairStatusClass($newStatus),
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '資料庫更新失敗']);
    }
    exit;
}