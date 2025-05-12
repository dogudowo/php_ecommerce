<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /models/backend/repairManager.php?status=error&message=Invalid_request_method');
    exit();
}

// Define root path for file operations
$webRoot = $_SERVER['DOCUMENT_ROOT'];
$basePath = is_dir($webRoot . '/public_html') ? $webRoot . '/public_html' : $webRoot;

define('CH_CONTEXT_IMG_TARGET_PATH', $basePath . '/imgs/repair/context-ch/repairContext.jpg');
define('CH_RUNDOWN_IMG_TARGET_PATH', $basePath . '/imgs/repair/context-ch/repairRundown.jpg');
define('EN_CONTEXT_IMG_TARGET_PATH', $basePath . '/imgs/repair/context-en/repairContextUS.jpg');
define('EN_RUNDOWN_IMG_TARGET_PATH', $basePath . '/imgs/repair/context-en/repairRundownEN.jpg');
define('PRICE_IMG_TARGET_PATH', $basePath . '/imgs/repair/price/AllenRepairPrice0609.jpg');
define('KEY_REPORT_DOC_TARGET_PATH', $basePath . '/src/KeyReport.doc');

$uploadErrors = [];

// Helper function to handle file uploads
function handleUpload($fileKey, $targetPath, $allowedMimeTypes, $allowedExtensions)
{
    global $uploadErrors;
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
        $fileName = $_FILES[$fileKey]['name'];
        $fileSize = $_FILES[$fileKey]['size'];
        $fileType = $_FILES[$fileKey]['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file type and extension
        if (!in_array($fileType, $allowedMimeTypes) || !in_array($fileExtension, $allowedExtensions)) {
            $uploadErrors[] = "檔案 '{$fileName}' 的類型 ({$fileType}, .{$fileExtension}) 不被允許上傳至 {$fileKey}。";
            return;
        }

        // Validate file size (e.g., max 5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            $uploadErrors[] = "檔案 '{$fileName}' 過大 (超過 5MB)。";
            return;
        }

        // Ensure target directory exists (it should, based on project structure)
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                $uploadErrors[] = "無法建立目標資料夾：{$targetDir}";
                return;
            }
        }

        // Move the uploaded file to the target path, overwriting the existing file
        if (!move_uploaded_file($fileTmpPath, $targetPath)) {
            $uploadErrors[] = "無法移動上傳的檔案 '{$fileName}' 到 {$targetPath}. 請檢查權限及路徑。";
        }
    } elseif (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors
        $uploadErrors[] = "上傳檔案 '{$fileKey}' 時發生錯誤，錯誤代碼: " . $_FILES[$fileKey]['error'];
    }
}

// --- Handle Image and Document Uploads ---

// Allowed image types
$allowedImageMimeTypes = ['image/jpeg', 'image/png'];
$allowedImageExtensions = ['jpg', 'jpeg', 'png'];

// Allowed document types for KeyReport
$allowedDocMimeTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$allowedDocExtensions = ['doc', 'docx'];

// Process uploads
handleUpload('ch_context_img', CH_CONTEXT_IMG_TARGET_PATH, $allowedImageMimeTypes, $allowedImageExtensions);
handleUpload('ch_rundown_img', CH_RUNDOWN_IMG_TARGET_PATH, $allowedImageMimeTypes, $allowedImageExtensions);
handleUpload('en_context_img', EN_CONTEXT_IMG_TARGET_PATH, $allowedImageMimeTypes, $allowedImageExtensions);
handleUpload('en_rundown_img', EN_RUNDOWN_IMG_TARGET_PATH, $allowedImageMimeTypes, $allowedImageExtensions);
handleUpload('price_img', PRICE_IMG_TARGET_PATH, $allowedImageMimeTypes, $allowedImageExtensions);
handleUpload('key_report_doc', KEY_REPORT_DOC_TARGET_PATH, $allowedDocMimeTypes, $allowedDocExtensions);

// Return JSON response
header('Content-Type: application/json');
if (empty($uploadErrors)) {
    echo json_encode(['success' => true, 'message' => '內容已成功更新！']);
} else {
    $errorMessage = implode("\n", array_map('htmlspecialchars', $uploadErrors));
    echo json_encode(['success' => false, 'message' => $errorMessage]);
}
exit();
?>