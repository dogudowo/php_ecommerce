<?php
require_once __DIR__ . '/error_handler.php';

// 只有後台登入者可存取
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '未授權的存取。']);
    exit;
}

// 檢查請求方法是否為 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不允許的方法。']);
    exit;
}

// 檢查是否有檔案上傳
if (!isset($_FILES['logoFile']) || $_FILES['logoFile']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '檔案上傳失敗或無檔案上傳。']);
    exit;
}

$file = $_FILES['logoFile'];
$logoType = $_POST['logoType'] ?? '';
$targetDir = __DIR__ . '/../imgs/';
$allowedTypes = [];
$targetFileName = '';

// 根據 logoType 設定目標檔案名和允許的檔案類型
switch ($logoType) {
    case 'title':
        $targetFileName = 'title.png';
        $allowedTypes = ['image/png'];
        break;
    case 'favicon':
        $targetFileName = 'webimg.ico';
        $allowedTypes = ['image/vnd.microsoft.icon', 'image/x-icon']; // 接受 .ico 的常見 MIME 類型
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '無效的 logo 類型。']);
        exit;
}

$targetFilePath = $targetDir . $targetFileName;
$fileType = mime_content_type($file['tmp_name']);

// 檢查檔案類型
if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '不支援的檔案類型。請上傳 ' . implode(', ', $allowedTypes) . ' 格式的檔案。']);
    exit;
}

// 移動上傳的檔案，覆蓋舊檔案
if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
    if (!chmod($targetFilePath, 0777)) {
        // chmod 失敗也回報，但仍算檔案移動成功
        error_log("Failed to chmod 0777 on {$targetFilePath}");
    }
    echo json_encode(['success' => true, 'message' => '更新成功！']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '移動檔案失敗。']);
}

?>