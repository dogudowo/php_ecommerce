<?php
header('Content-Type: application/json');

// Define the path to the footer.php file
$footerFilePath = __DIR__ . '/../../../../fixedFile/footer.php';

// Function to send JSON response
function sendJsonResponse($success, $message = '', $data = [])
{
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// Function to extract social links from footer content
function extractSocialLinks($content)
{
    $links = [];
    // Regex to find href attribute within <a> tags with specific IDs
    $patterns = [
        'line' => '/<a[^>]*id=["\']lineLinkIcon["\'][^>]*href=["\']([^"\']+)["\']/i',
        'facebook' => '/<a[^>]*id=["\']facebookLinkIcon["\'][^>]*href=["\']([^"\']+)["\']/i',
        'instagram' => '/<a[^>]*id=["\']instagramLinkIcon["\'][^>]*href=["\']([^"\']+)["\']/i',
        'youtube' => '/<a[^>]*id=["\']youtubeLinkIcon["\'][^>]*href=["\']([^"\']+)["\']/i',
    ];

    foreach ($patterns as $key => $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            $links[$key] = htmlspecialchars_decode($matches[1], ENT_QUOTES);
        }
    }
    return $links;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'getSystemSettings') {
        if (!file_exists($footerFilePath)) {
            sendJsonResponse(false, 'footer.php 文件不存在。');
        }
        $footerContent = file_get_contents($footerFilePath);
        if ($footerContent === false) {
            sendJsonResponse(false, '無法讀取 footer.php。');
        }
        $currentLinks = extractSocialLinks($footerContent);
        if (empty($currentLinks)) {
            sendJsonResponse(true, '無法從 footer.php 提取連結，將使用預設值。', ['line' => '', 'facebook' => '', 'instagram' => '', 'youtube' => '']);
        } else {
            sendJsonResponse(true, '成功獲取系統設定。', $currentLinks);
        }
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

    if (!file_exists($footerFilePath)) {
        sendJsonResponse(false, 'footer.php 文件不存在。');
    }

    if (!is_writable($footerFilePath)) {
        sendJsonResponse(false, 'footer.php 文件不可寫。請檢查文件權限。');
    }

    $backupFilePath = $footerFilePath . '.bak.' . date('YmdHis');
    if (!copy($footerFilePath, $backupFilePath)) {
        error_log('無法創建 footer.php 的備份文件: ' . $backupFilePath);
        // Not a fatal error for the user, but should be logged.
    }

    try {
        $footerContent = file_get_contents($footerFilePath);
        if ($footerContent === false) {
            sendJsonResponse(false, '無法讀取 footer.php 文件內容。');
        }

        // Update links using regex, targeting the href attribute of <a> tags with specific IDs
        $patterns = [
            'line' => '/(<a[^>]*id=["\']lineLinkIcon["\'][^>]*href=["\'])([^"\']*)(["\'])/i',
            'facebook' => '/(<a[^>]*id=["\']facebookLinkIcon["\'][^>]*href=["\'])([^"\']*)(["\'])/i',
            'instagram' => '/(<a[^>]*id=["\']instagramLinkIcon["\'][^>]*href=["\'])([^"\']*)(["\'])/i',
            'youtube' => '/(<a[^>]*id=["\']youtubeLinkIcon["\'][^>]*href=["\'])([^"\']*)(["\'])/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (isset($socialLinks[$key])) {
                $footerContent = preg_replace(
                    $pattern,
                    '$1' . htmlspecialchars($socialLinks[$key], ENT_QUOTES, 'UTF-8') . '$3',
                    $footerContent
                );
            }
        }

        if (file_put_contents($footerFilePath, $footerContent) === false) {
            if (file_exists($backupFilePath)) {
                copy($backupFilePath, $footerFilePath); // Attempt to restore
            }
            sendJsonResponse(false, '無法寫入更新到 footer.php。');
        }
        sendJsonResponse(true, '社群連結已成功更新！');

    } catch (Exception $e) {
        if (file_exists($backupFilePath)) {
            copy($backupFilePath, $footerFilePath); // Attempt to restore
        }
        error_log('更新 footer.php 時發生錯誤: ' . $e->getMessage());
        sendJsonResponse(false, '更新社群連結時發生內部錯誤。');
    }
    exit;
}

sendJsonResponse(false, '不支援的請求方法。');

?>