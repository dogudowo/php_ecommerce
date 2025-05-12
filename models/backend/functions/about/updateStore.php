<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/storeFunctions.php';

header('Content-Type: application/json');

$data = [
    'tax_id' => $_POST['tax_id'],
    'name' => $_POST['name'],
    'phone' => $_POST['phone'],
    'location' => $_POST['location'],
    'opening_time' => $_POST['opening_time'],
    'url' => preg_match('/src="([^"]+)"/', $_POST['map_url'], $matches) ? $matches[1] : $_POST['map_url']
];

try {
    $success = StoreFunctions::updateStoreInfo($data);
    echo json_encode(['success' => $success, 'message' => '門市更新成功']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '更新失敗: ' . $e->getMessage()]);
}
?>