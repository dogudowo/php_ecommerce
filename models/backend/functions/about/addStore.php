<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/storeFunctions.php';

header('Content-Type: application/json');

try {
    // 獲取當前最大tax_id
    $pdo = getDBConnection();
    $stmt = $pdo->query('SELECT MAX(tax_id) AS max_tax_id FROM store');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $new_tax_id = $result['max_tax_id'] + 1;

    $data = [
        'tax_id' => $new_tax_id,
        'name' => $_POST['name'],
        'phone' => $_POST['phone'],
        'location' => $_POST['location'],
        'opening_time' => $_POST['opening_time'],
        'url' => preg_match('/src="([^"]+)"/', $_POST['map_url'], $matches) ? $matches[1] : $_POST['map_url'],
    ];

    $success = StoreFunctions::addStore($data);
    echo json_encode(['success' => $success, 'message' => '門市新增成功']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '新增失敗: ' . $e->getMessage()]);
}
?>