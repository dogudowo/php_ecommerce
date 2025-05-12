<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/companyFunctions.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_GET['tax_id'])) {
        throw new Exception('Missing tax_id parameter');
    }

    $tax_id = $_GET['tax_id'];
    $company = CompanyFunctions::getCompanyById($tax_id);

    if (!$company) {
        throw new Exception('Company not found');
    }

    echo json_encode([
        'success' => true,
        'data' => $company
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>