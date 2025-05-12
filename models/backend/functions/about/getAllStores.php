<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/storeFunctions.php';

header('Content-Type: application/json');

try {
    $stores = StoreFunctions::getAllStores();
    echo json_encode(['success' => true, 'data' => $stores]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>