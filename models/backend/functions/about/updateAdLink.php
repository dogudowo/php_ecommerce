<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/../../../../api/error_handler.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '只接受 POST 請求']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// 檢查 POST 參數
if (empty($_POST['adLink']) && (!isset($data['adLink']) || empty($data['adLink']))) {
    echo json_encode(['success' => false, 'message' => '缺少廣告連結參數']);
    exit;
}

// 優先使用 POST 參數
$adLink = isset($_POST['adLink']) ? trim($_POST['adLink']) : trim($data['adLink']);

if (empty($adLink)) {
    echo json_encode(['success' => false, 'message' => '廣告連結不能為空']);
    exit;
}

$adLinkData = ['adLink' => $adLink];
$jsonData = json_encode($adLinkData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$filePath = __DIR__ . '/../../../../src/adLink.json';

if (file_put_contents($filePath, $jsonData) === false) {
    echo json_encode(['success' => false, 'message' => '寫入檔案失敗']);
    exit;
}

echo json_encode(['success' => true, 'message' => '廣告連結更新成功']);
?>