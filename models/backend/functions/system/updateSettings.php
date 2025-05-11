<?php
require_once __DIR__ . '/../../../../api/error_handler.php';
header('Content-Type: application/json');

// Define the path to the social_links.json file
$socialLinksJsonPath = __DIR__ . '/../../../../fixedFile/social_links.json';

// Function to send JSON response
function sendJsonResponse($success, $message = '', $data = [])
{
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}



if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'getSystemSettings') {
        if (!file_exists($socialLinksJsonPath)) {
            // If the JSON file doesn't exist, create it with default empty values
            $defaultLinks = ['line' => '', 'facebook' => '', 'instagram' => '', 'youtube' => ''];
            if (file_put_contents($socialLinksJsonPath, json_encode($defaultLinks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
                sendJsonResponse(false, '無法建立 social_links.json 檔案。');
            }
            sendJsonResponse(true, 'social_links.json 不存在，已建立預設檔案。', $defaultLinks);
        }
        $socialLinksJson = file_get_contents($socialLinksJsonPath);
        if ($socialLinksJson === false) {
            sendJsonResponse(false, '無法讀取 social_links.json。');
        }
        $currentLinks = json_decode($socialLinksJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(false, 'social_links.json 內容格式錯誤。');
        }
        sendJsonResponse(true, '成功獲取系統設定。', $currentLinks);
    } else {
        sendJsonResponse(false, '無效的 GET 請求操作。');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        sendJsonResponse(false, '無效的請求數據。');
    }

    $action = $input['action'] ?? null;
    $socialLinks = $input['socialLinks'] ?? null;

    if ($action !== 'saveSystemSettings' || !$socialLinks) {
        sendJsonResponse(false, '請求無效或未提供社群連結數據。');
    }

    // Validate URLs
    $requiredLinks = ['line', 'facebook', 'instagram', 'youtube'];
    foreach ($requiredLinks as $key) {
        if (empty($socialLinks[$key]) || !filter_var($socialLinks[$key], FILTER_VALIDATE_URL)) {
            sendJsonResponse(false, ucfirst($key) . ' 的連結格式不正確或為空。');
        }
    }

    // Ensure the config directory exists and is writable
    $configDir = dirname($socialLinksJsonPath);
    if (!is_dir($configDir)) {
        if (!mkdir($configDir, 0755, true)) {
            sendJsonResponse(false, '無法建立 config 目錄。');
        }
    }
    if (!is_writable($configDir)) {
        sendJsonResponse(false, 'config 目錄不可寫。');
    }
    if (file_exists($socialLinksJsonPath) && !is_writable($socialLinksJsonPath)) {
        sendJsonResponse(false, 'social_links.json 文件不可寫。請檢查文件權限。');
    }

    try {
        // Write the new social links to social_links.json
        $jsonData = json_encode($socialLinks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($socialLinksJsonPath, $jsonData) === false) {
            sendJsonResponse(false, '無法寫入更新到 social_links.json。');
        }
        sendJsonResponse(true, '社群連結已成功更新！');

    } catch (Exception $e) {
        error_log('更新 social_links.json 時發生錯誤: ' . $e->getMessage());
        sendJsonResponse(false, '更新社群連結時發生內部錯誤。');
    }
    exit;
}

sendJsonResponse(false, '不支援的請求方法。');

?>