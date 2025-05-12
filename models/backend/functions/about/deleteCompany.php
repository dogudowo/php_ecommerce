<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/companyFunctions.php';

header('Content-Type: application/json');

try {
    $data = $_POST;

    // 驗證必填欄位
    if (empty($data['tax_id'])) {
        throw new Exception('統一編號為必填欄位');
    }

    // 刪除公司資料
    $result = CompanyFunctions::deleteCompany($data['tax_id']);

    if ($result) {
        echo json_encode(['success' => true, 'message' => '公司刪除成功']);
    } else {
        throw new Exception('刪除公司失敗');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>