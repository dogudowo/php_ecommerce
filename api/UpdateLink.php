<?php
/**
 * 合併廣告連結與社群連結設定 API
 * - GET  ?action=getLinks : 取得所有連結設定
 * - POST body:{ action:'saveAdLink', adLink:'...' } : 儲存廣告連結
 * - POST body:{ action:'saveSocialLinks', socialLinks:{...} } : 儲存社群連結
 */

require_once __DIR__ . '/error_handler.php';
header('Content-Type: application/json; charset=utf-8');

// 設定檔路徑
const AD_LINK_FILE = __DIR__ . '/../src/adLink.json';
const SOCIAL_LINK_FILE = __DIR__ . '/../src/social_links.json';
const REQUIRED_SOCIAL_KEYS = ['line', 'facebook', 'instagram', 'youtube'];

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

// 讀取廣告連結
function read_ad_link(): array
{
    if (!file_exists(AD_LINK_FILE)) {
        return ['adLink' => ''];
    }
    $json = file_get_contents(AD_LINK_FILE);
    return json_decode($json, true) ?? ['adLink' => ''];
}

// 讀取社群連結
function read_social_links(): array
{
    if (!file_exists(SOCIAL_LINK_FILE)) {
        return array_fill_keys(REQUIRED_SOCIAL_KEYS, '');
    }
    $json = file_get_contents(SOCIAL_LINK_FILE);
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return array_fill_keys(REQUIRED_SOCIAL_KEYS, '');
    }
    return array_merge(array_fill_keys(REQUIRED_SOCIAL_KEYS, ''), $data);
}

// 寫入廣告連結
function write_ad_link(string $adLink): bool
{
    $json = json_encode(['adLink' => $adLink], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(AD_LINK_FILE, $json, LOCK_EX) !== false;
}

// 寫入社群連結
function write_social_links(array $links): bool
{
    $json = json_encode($links, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return file_put_contents(SOCIAL_LINK_FILE, $json, LOCK_EX) !== false;
}

// 驗證社群連結
function validate_social_links(array $links): ?string
{
    foreach (REQUIRED_SOCIAL_KEYS as $key) {
        if (empty($links[$key]) || !filter_var($links[$key], FILTER_VALIDATE_URL)) {
            return ucfirst($key) . ' 連結無效或為空。';
        }
    }
    return null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'getLinks') {
        respond(true, '讀取成功。', [
            'adLink' => read_ad_link()['adLink'] ?? '',
            'socialLinks' => read_social_links()
        ]);
    }
    respond(false, '無效的 GET 請求。');
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(false, '無效的 JSON 請求。');
    }

    $action = $input['action'] ?? '';

    // 處理廣告連結
    if ($action === 'saveAdLink') {
        $adLink = trim($input['adLink'] ?? '');
        if (empty($adLink)) {
            respond(false, '廣告連結不能為空');
        }

        if (!write_ad_link($adLink)) {
            respond(false, '寫入廣告連結失敗，請檢查檔案權限。');
        }
        respond(true, '廣告連結已更新成功！');
    }

    // 處理社群連結
    if ($action === 'saveSocialLinks') {
        if (!isset($input['socialLinks'])) {
            respond(false, '缺少必要參數。');
        }

        $links = (array) $input['socialLinks'];
        $err = validate_social_links($links);
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

    respond(false, '不支援的動作。');
}

respond(false, '不支援的請求方法。');
?>