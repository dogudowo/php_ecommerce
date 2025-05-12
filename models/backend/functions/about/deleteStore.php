<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/storeFunctions.php';

header('Content-Type: application/json');

$tax_id = $_POST['tax_id'];

try {
    $success = StoreFunctions::deleteStore($tax_id);
    echo json_encode(['success' => $success, 'message' => '門市刪除成功']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '刪除失敗: ' . $e->getMessage()]);
}
?>