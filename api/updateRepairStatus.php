<?php
require_once __DIR__ . '/db.php'; // 根據你的專案結構調整路徑
header('Content-Type: application/json');

if (empty($_POST['repair_id']) || empty($_POST['new_status'])) {
    echo json_encode(['success' => false, 'message' => '缺少參數']);
    exit;
}

$repairId = (int) $_POST['repair_id'];
$newStatus = $_POST['new_status'];

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE Repairs SET RepairStatus = :status WHERE RepairID = :id");
    $stmt->execute(['status' => $newStatus, 'id' => $repairId]);

    // 狀態樣式
    $statusClass = match ($newStatus) {
        '已完成' => 'bg-success',
        '處理中' => 'bg-info text-dark',
        '維修中' => 'bg-primary',
        '已取消' => 'bg-warning',
        '異常' => 'bg-danger',
        default => 'bg-secondary'
    };

    echo json_encode(['success' => true, 'statusText' => $newStatus, 'statusClass' => $statusClass]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '資料庫更新失敗']);
}