<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/companyFunctions.php';

header('Content-Type: application/json');

try {
    $data = $_POST;

    // 驗證必填欄位
    if (empty($data['tax_id']) || empty($data['name'])) {
        throw new Exception('統一編號和公司名稱為必填欄位');
    }

    // 新增公司資料
    $result = CompanyFunctions::addCompany(
        $data['tax_id'],
        $data['name'],
        $data['email'] ?? null,
        $data['phone'] ?? null,
        $data['fax'] ?? null
    );

    if ($result) {
        echo json_encode(['success' => true, 'message' => '公司新增成功']);
    } else {
        throw new Exception('新增公司失敗');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>