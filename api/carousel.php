<?php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/error_handler.php';

const CAROUSEL_DIR = __DIR__ . '/../imgs/carousel/'; // storage folder
const MAX_UPLOAD_SIZE = 2 * 1024 * 1024;             // 2 MB
const ALLOWED_MIME_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];

if (!is_dir(CAROUSEL_DIR) && !mkdir(CAROUSEL_DIR, 0755, true) && !is_dir(CAROUSEL_DIR)) {
    respondJson(false, '無法建立輪播圖目錄');
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'getCarouselImgs':
        respondJson(true, getPublicImages());
        break;

    case 'list':
        checkAdmin();
        respondJson(true, getAdminImages());
        break;

    case 'upload':
        checkAdmin();
        handleUpload();
        break;

    case 'delete':
        checkAdmin();
        handleDelete();
        break;

    default:
        // fallback to public list (backward‑compatible)
        respondJson(true, getPublicImages());
}

/**
 * Public image list (relative paths).
 *
 * @return string[]
 */
function getPublicImages(): array
{
    $files = glob(CAROUSEL_DIR . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    return array_map(static function ($file) {
        return str_replace(realpath(__DIR__ . '/../') . '/', '', realpath($file));
    }, $files);
}

/**
 * Admin image list (filenames only).
 *
 * @return string[]
 */
function getAdminImages(): array
{
    $files = scandir(CAROUSEL_DIR) ?: [];
    return array_values(array_filter($files, 'isImageFilename'));
}

/**
 * Handle upload action.
 */
function handleUpload(): void
{
    if (!isset($_FILES['carouselImage'])) {
        respondJson(false, '未找到上傳檔案');
    }

    $file = $_FILES['carouselImage'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        respondJson(false, uploadErrorMessage($file['error']));
    }

    // Mime‑type validation using finfo for reliability
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset(ALLOWED_MIME_TYPES[$mime])) {
        respondJson(false, '只允許上傳 JPG、PNG、GIF 或 WebP 格式的圖片');
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        respondJson(false, '圖片大小不能超過 2 MB');
    }

    $extension = ALLOWED_MIME_TYPES[$mime];
    $newFileName = uniqid('', true) . '.' . $extension;
    $targetPath = CAROUSEL_DIR . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        respondJson(false, '檔案保存失敗');
    }

    respondJson(true, ['filename' => $newFileName]);
}

/**
 * Handle delete action.
 */
function handleDelete(): void
{
    $filename = $_POST['filename'] ?? '';

    if (!$filename || !isImageFilename($filename)) {
        respondJson(false, '無效的檔名');
    }

    $filePath = realpath(CAROUSEL_DIR . basename($filename));

    // Confirm the file is really inside CAROUSEL_DIR
    if ($filePath === false || strpos($filePath, realpath(CAROUSEL_DIR)) !== 0) {
        respondJson(false, '非法路徑');
    }

    if (!file_exists($filePath) || !unlink($filePath)) {
        respondJson(false, '刪除失敗或檔案不存在');
    }

    respondJson(true);
}

/**
 * ---------------------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------------------------
 */

function checkAdmin(): void
{
    session_start();
    if (($_SESSION['logged_in'] ?? false) !== true || ($_SESSION['user_role'] ?? -1) !== 2) {
        respondJson(false, '未授權的訪問', 401);
    }
}

function respondJson(bool $success, $data = null, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');

    if ($success) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => $data]);
    }
    exit;
}

function isImageFilename(string $filename): bool
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
}

function uploadErrorMessage(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE => '上傳的文件超過了 php.ini 中 upload_max_filesize 的限制',
        UPLOAD_ERR_FORM_SIZE => '上傳的文件超過了表單中 MAX_FILE_SIZE 的限制',
        UPLOAD_ERR_PARTIAL => '文件只有部分被上傳',
        UPLOAD_ERR_NO_FILE => '沒有文件被上傳',
        UPLOAD_ERR_NO_TMP_DIR => '找不到臨時文件夾',
        UPLOAD_ERR_CANT_WRITE => '文件寫入失敗',
        UPLOAD_ERR_EXTENSION => '檔案上傳被 PHP 擴展中止',
        default => '未知上傳錯誤',
    };
}
?>