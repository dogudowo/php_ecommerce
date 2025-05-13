<?php
/**
 * 系統社群連結設定 API
 * - GET  ?action=getSystemSettings : 取得設定
 * - POST body:{ action:'saveSystemSettings', socialLinks:{...} } : 儲存設定
 */

require_once __DIR__ . '/../../../../api/error_handler.php';
header('Content-Type: application/json; charset=utf-8');

// 設定檔路徑
const SOCIAL_LINK_FILE = __DIR__ . '/../../../../src/social_links.json';
const REQUIRED_KEYS = ['line', 'facebook', 'instagram', 'youtube'];

// 統一輸出格式
function respond(bool $success, string $message = '', array $data = []): never
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// 讀取設定，如檔案不存在則回傳空值陣列
function read_social_links(): array
{
    if (!file_exists(SOCIAL_LINK_FILE)) {
        return array_fill_keys(REQUIRED_KEYS, '');
    }
    $json = file_get_contents(SOCIAL_LINK_FILE);
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return array_fill_keys(REQUIRED_KEYS, '');
    }
    return array_merge(array_fill_keys(REQUIRED_KEYS, ''), $data);
}

// 寫入設定
function write_social_links(array $links): bool
{
    $json = json_encode($links, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return file_put_contents(SOCIAL_LINK_FILE, $json, LOCK_EX) !== false;
}

// 驗證 URL
function validate_links(array $links): ?string
{
    foreach (REQUIRED_KEYS as $key) {
        if (empty($links[$key]) || !filter_var($links[$key], FILTER_VALIDATE_URL)) {
            return ucfirst($key) . ' 連結無效或為空。';
        }
    }
    return null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action !== 'getSystemSettings') {
        respond(false, '無效的 GET 請求。');
    }
    respond(true, '讀取成功。', read_social_links());
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(false, '無效的 JSON 請求。');
    }

    if (($input['action'] ?? '') !== 'saveSystemSettings' || !isset($input['socialLinks'])) {
        respond(false, '缺少必要參數。');
    }

    $links = (array) $input['socialLinks'];
    $err = validate_links($links);
    if ($err) {
        respond(false, $err);
    }

    // 確保資料夾存在且可寫入
    $dir = dirname(SOCIAL_LINK_FILE);
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        respond(false, '無法建立設定資料夾。');
    }
    if (!is_writable($dir)) {
        respond(false, '設定資料夾不可寫。');
    }

    if (!write_social_links($links)) {
        respond(false, '寫入設定失敗，請檢查檔案權限。');
    }

    respond(true, '社群連結已更新成功！');
}

respond(false, '不支援的請求方法。');
