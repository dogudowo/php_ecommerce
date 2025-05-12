<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/storeFunctions.php';

header('Content-Type: application/json');

$tax_id = $_GET['tax_id'];

try {
    $store = StoreFunctions::getStoreById($tax_id);
    if ($store) {
        echo json_encode(['success' => true, 'data' => $store]);
    } else {
        echo json_encode(['success' => false, 'message' => '找不到指定門市']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '取得門市資料失敗: ' . $e->getMessage()]);
}
?>