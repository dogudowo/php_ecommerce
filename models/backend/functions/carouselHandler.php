<?php
require_once __DIR__ . '/../../../api/error_handler.php';
// 防止直接訪問此文件
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 2) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '未授權的訪問']);
    exit;
}

// 輪播圖目錄路徑
$carousel_dir = __DIR__ . '/../../../imgs/carousel/';

// 確保目錄存在
if (!is_dir($carousel_dir)) {
    mkdir($carousel_dir, 0755, true);
}

// 處理不同的操作
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'list':
        // 獲取輪播圖列表
        listCarouselImages();
        break;

    case 'upload':
        // 上傳新輪播圖
        uploadCarouselImage();
        break;

    case 'delete':
        // 刪除輪播圖
        deleteCarouselImage();
        break;

    default:
        // 未知操作
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '未知操作']);
        break;
}

/**
 * 獲取輪播圖列表
 */
function listCarouselImages()
{
    global $carousel_dir;

    $images = [];

    // 讀取目錄中的所有圖片文件
    if (is_dir($carousel_dir)) {
        $files = scandir($carousel_dir);

        foreach ($files as $file) {
            // 排除目錄和非圖片文件
            if ($file != '.' && $file != '..' && isImageFile($file)) {
                $images[] = $file;
            }
        }
    }

    // 返回JSON格式的圖片列表
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'images' => $images]);
}

/**
 * 上傳新輪播圖
 */
function uploadCarouselImage()
{
    global $carousel_dir;

    // 檢查是否有文件上傳
    if (!isset($_FILES['carouselImage']) || $_FILES['carouselImage']['error'] != UPLOAD_ERR_OK) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => '上傳失敗：' . getUploadErrorMessage($_FILES['carouselImage']['error'])
        ]);
        return;
    }

    // 檢查文件類型
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['carouselImage']['type'];

    if (!in_array($file_type, $allowed_types)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => '上傳失敗：只允許上傳 JPG、PNG、GIF 或 WebP 格式的圖片'
        ]);
        return;
    }

    // 檢查文件大小（最大2MB）
    $max_size = 2 * 1024 * 1024; // 2MB
    if ($_FILES['carouselImage']['size'] > $max_size) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => '上傳失敗：圖片大小不能超過2MB'
        ]);
        return;
    }

    // 生成唯一的文件名
    $file_extension = pathinfo($_FILES['carouselImage']['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $target_path = $carousel_dir . $new_filename;

    // 移動上傳的文件到目標目錄
    if (move_uploaded_file($_FILES['carouselImage']['tmp_name'], $target_path)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'filename' => $new_filename]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '上傳失敗：無法保存文件']);
    }
}

/**
 * 刪除輪播圖
 */
function deleteCarouselImage()
{
    global $carousel_dir;

    // 檢查是否提供了文件名
    if (!isset($_POST['filename']) || empty($_POST['filename'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '刪除失敗：未提供文件名']);
        return;
    }

    $filename = $_POST['filename'];

    // 安全檢查：確保文件名不包含目錄遍歷
    if (strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '刪除失敗：無效的文件名']);
        return;
    }

    $file_path = $carousel_dir . $filename;

    // 檢查文件是否存在
    if (!file_exists($file_path)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '刪除失敗：文件不存在']);
        return;
    }

    // 刪除文件
    if (unlink($file_path)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '刪除失敗：無法刪除文件']);
    }
}

/**
 * 檢查文件是否為圖片
 */
function isImageFile($filename)
{
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowed_extensions);
}

/**
 * 獲取上傳錯誤訊息
 */
function getUploadErrorMessage($error_code)
{
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return '上傳的文件超過了 php.ini 中 upload_max_filesize 的限制';
        case UPLOAD_ERR_FORM_SIZE:
            return '上傳的文件超過了表單中 MAX_FILE_SIZE 的限制';
        case UPLOAD_ERR_PARTIAL:
            return '文件只有部分被上傳';
        case UPLOAD_ERR_NO_FILE:
            return '沒有文件被上傳';
        case UPLOAD_ERR_NO_TMP_DIR:
            return '找不到臨時文件夾';
        case UPLOAD_ERR_CANT_WRITE:
            return '文件寫入失敗';
        case UPLOAD_ERR_EXTENSION:
            return '文件上傳被PHP擴展停止';
        default:
            return '未知上傳錯誤';
    }
}