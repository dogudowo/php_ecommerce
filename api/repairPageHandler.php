<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../models/backend/repairManager.php?status=error&message=Invalid_request_method');
    exit();
}

/**
 * 取得專案根目錄（以目前檔案為基準往上兩層）
 * 例：/htdocs/public_html/models/backend → /htdocs/public_html
 * 如果您的實際層級不同，調整 dirname(__DIR__, 2) 的第二個參數即可。
 */
$projectRoot = dirname(__DIR__, 1);

/* ---------- 目標檔案（相對）路徑 ---------- */
const CH_CONTEXT_IMG_PATH = 'imgs/repair/context-ch/repairContext.jpg';
const CH_RUNDOWN_IMG_PATH = 'imgs/repair/context-ch/repairRundown.jpg';
const EN_CONTEXT_IMG_PATH = 'imgs/repair/context-en/repairContextUS.jpg';
const EN_RUNDOWN_IMG_PATH = 'imgs/repair/context-en/repairRundownEN.jpg';
const PRICE_IMG_PATH = 'imgs/repair/price/AllenRepairPrice0609.jpg';
const KEY_REPORT_DOC_PATH = 'src/KeyReport.doc';

$uploadErrors = [];

/**
 * 處理單一檔案上傳
 *
 * @param string $fileKey            表單欄位名稱
 * @param string $relativePath       相對於專案根目錄的路徑
 * @param array  $allowedMimeTypes   允許的 MIME 類型
 * @param array  $allowedExtensions  允許的副檔名
 */
function handleUpload(string $fileKey, string $relativePath, array $allowedMimeTypes, array $allowedExtensions): void
{
    global $uploadErrors, $projectRoot;

    if (!isset($_FILES[$fileKey])) {
        return;                     // 沒該欄位
    }
    if ($_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return;                     // 未選檔
    }
    if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors[] = "上傳 {$fileKey} 時錯誤（代碼 {$_FILES[$fileKey]['error']}）";
        return;
    }

    $fileName = $_FILES[$fileKey]['name'];
    $fileTmpPath = $_FILES[$fileKey]['tmp_name'];
    $fileSize = $_FILES[$fileKey]['size'];
    $fileType = $_FILES[$fileKey]['type'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // 類型與副檔名檢查
    if (
        !in_array($fileType, $allowedMimeTypes, true) ||
        !in_array($fileExt, $allowedExtensions, true)
    ) {
        $uploadErrors[] = "檔案「{$fileName}」的型態（{$fileType}, .{$fileExt}）不被允許。";
        return;
    }

    // 大小限制（5 MB）
    if ($fileSize > 5 * 1024 * 1024) {
        $uploadErrors[] = "檔案「{$fileName}」過大（> 5 MB）。";
        return;
    }

    // 組合目標絕對路徑
    $targetAbsPath = "{$projectRoot}/{$relativePath}";

    // 特殊處理 key_report_doc，固定檔名但保留副檔名
    if ($fileKey === 'key_report_doc') {
        $baseName = 'KeyReport';
        $targetDir = "{$projectRoot}/src";
        $targetAbsPath = "{$targetDir}/{$baseName}.{$fileExt}";

        // 檢查並刪除同檔名但副檔名不同的舊檔案
        $existingFiles = glob("{$targetDir}/{$baseName}.*");
        if ($existingFiles) {
            foreach ($existingFiles as $existingFile) {
                if (pathinfo($existingFile, PATHINFO_EXTENSION) !== $fileExt) {
                    if (is_file($existingFile)) {
                        unlink($existingFile);
                    }
                }
            }
        }
    } else {
        $targetAbsPath = "{$projectRoot}/{$relativePath}";
        $targetDir = dirname($targetAbsPath);
    }

    // 建立資料夾
    if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
        $uploadErrors[] = "無法建立資料夾：{$targetDir}";
        return;
    }
    chmod($targetDir, 0777);
    // 搬移檔案
    if (!move_uploaded_file($fileTmpPath, $targetAbsPath)) {
        $uploadErrors[] = "無法將檔案「{$fileName}」搬到 {$relativePath}，請檢查權限。";
    } else {
        // 設定檔案權限為 777
        chmod($targetAbsPath, 0777);
    }
}

/* ---------- 執行上傳 ---------- */

// 圖片限制
$imageMimeTypes = ['image/jpeg', 'image/png'];
$imageExtensions = ['jpg', 'jpeg', 'png'];

// Word 文件限制
$docMimeTypes = [
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
$docExtensions = ['doc', 'docx'];

handleUpload('ch_context_img', CH_CONTEXT_IMG_PATH, $imageMimeTypes, $imageExtensions);
handleUpload('ch_rundown_img', CH_RUNDOWN_IMG_PATH, $imageMimeTypes, $imageExtensions);
handleUpload('en_context_img', EN_CONTEXT_IMG_PATH, $imageMimeTypes, $imageExtensions);
handleUpload('en_rundown_img', EN_RUNDOWN_IMG_PATH, $imageMimeTypes, $imageExtensions);
handleUpload('price_img', PRICE_IMG_PATH, $imageMimeTypes, $imageExtensions);
handleUpload('key_report_doc', KEY_REPORT_DOC_PATH, $docMimeTypes, $docExtensions);

/* ---------- 回傳結果 ---------- */
header('Content-Type: application/json');

$uploadedDocExt = null;
if (isset($_FILES['key_report_doc']) && $_FILES['key_report_doc']['error'] === UPLOAD_ERR_OK) {
    $uploadedDocExt = strtolower(pathinfo($_FILES['key_report_doc']['name'], PATHINFO_EXTENSION));
}

$response = empty($uploadErrors)
    ? ['success' => true, 'message' => '內容已成功更新！']
    : ['success' => false, 'message' => implode("\n", array_map('htmlspecialchars', $uploadErrors))];

// Add the uploaded document extension if available and successful
if ($uploadedDocExt !== null && empty($uploadErrors)) {
    $response['key_report_doc_ext'] = $uploadedDocExt;
}

echo json_encode($response);

exit();
?>