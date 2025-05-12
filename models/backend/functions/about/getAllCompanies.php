<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/companyFunctions.php';

header('Content-Type: application/json');

try {
    $companies = CompanyFunctions::getCompanyInfo();
    echo json_encode(['success' => true, 'data' => $companies]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>