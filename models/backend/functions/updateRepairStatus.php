<?php
require_once __DIR__ . '/../../../api/db.php'; // 根據你的專案結構調整路徑
header('Content-Type: application/json');

if (empty($_POST['repair_id']) || empty($_POST['new_status'])) {
    echo json_encode(['success' => false, 'message' => '缺少參數']);
    exit;
}

$repairId = (int)$_POST['repair_id'];
$newStatus = $_POST['new_status'];

try {
    $pdo = getDBConnection(); // 如果你的 function 叫 db_connect()，請改成 db_connect()
    $stmt = $pdo->prepare("UPDATE Repairs SET RepairStatus = :status WHERE RepairID = :id");
    $stmt->execute(['status' => $newStatus, 'id' => $repairId]);

    // 狀態中文與樣式
    $statusMap = [
        'Completed' => ['text' => '已完成', 'class' => 'bg-success'],
        'Pending' => ['text' => '處理中', 'class' => 'bg-info text-dark'],
        'Repairing' => ['text' => '維修中', 'class' => 'bg-primary'],
        'Cancel' => ['text' => '已取消', 'class' => 'bg-warning'],
        'abnormal' => ['text' => '異常', 'class' => 'bg-danger'],
    ];
    $statusText = $statusMap[$newStatus]['text'] ?? $newStatus;
    $statusClass = $statusMap[$newStatus]['class'] ?? 'bg-secondary';

    echo json_encode(['success' => true, 'statusText' => $statusText, 'statusClass' => $statusClass]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '資料庫更新失敗']);
}