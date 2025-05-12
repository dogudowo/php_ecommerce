<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/companyFunctions.php';

header('Content-Type: application/json');

$data = [
    'tax_id' => $_POST['tax_id'],
    'name' => $_POST['name'],
    'email' => $_POST['email'],
    'phone' => $_POST['phone'],
    'fax' => $_POST['fax'] ?? null
];

try {
    $success = CompanyFunctions::updateCompanyInfo($data);
    echo json_encode(['success' => $success, 'message' => '公司資料更新成功']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '更新失敗: ' . $e->getMessage()]);
}
?>