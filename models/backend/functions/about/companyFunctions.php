<?php
require_once __DIR__ . '/../../../../api/db.php';

class CompanyFunctions
{
    public static function addCompany($tax_id, $name, $email = null, $phone = null, $fax = null)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO company (tax_id, name, email, phone, fax) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$tax_id, $name, $email, $phone, $fax]);
    }

    public static function getCompanyById($tax_id)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT * FROM company WHERE tax_id = :tax_id LIMIT 1');
        $stmt->bindParam(':tax_id', $tax_id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getCompanyInfo()
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM company");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateCompanyInfo($data)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE company SET name = ?, email = ?, phone = ?, fax = ? WHERE tax_id = ?");
        return $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['fax'], $data['tax_id']]);
    }

    public static function deleteCompany($tax_id)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM company WHERE tax_id = ?");
        return $stmt->execute([$tax_id]);
    }

    // New request handlers
    public static function handleAddCompanyRequest($postData)
    {
        header('Content-Type: application/json');
        try {
            // 驗證必填欄位
            if (empty($postData['tax_id']) || empty($postData['name'])) {
                throw new Exception('統一編號和公司名稱為必填欄位');
            }

            // 新增公司資料
            $result = self::addCompany(
                $postData['tax_id'],
                $postData['name'],
                $postData['email'] ?? null,
                $postData['phone'] ?? null,
                $postData['fax'] ?? null
            );

            if ($result) {
                return json_encode(['success' => true, 'message' => '公司新增成功']);
            } else {
                throw new Exception('新增公司失敗');
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function handleDeleteCompanyRequest($postData)
    {
        header('Content-Type: application/json');
        try {
            // 驗證必填欄位
            if (empty($postData['tax_id'])) {
                throw new Exception('統一編號為必填欄位');
            }

            // 刪除公司資料
            $result = self::deleteCompany($postData['tax_id']);

            if ($result) {
                return json_encode(['success' => true, 'message' => '公司刪除成功']);
            } else {
                throw new Exception('刪除公司失敗');
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function handleGetAllCompaniesRequest()
    {
        header('Content-Type: application/json');
        try {
            $companies = self::getCompanyInfo();
            return json_encode(['success' => true, 'data' => $companies]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function handleGetCompanyRequest($getData)
    {
        header('Content-Type: application/json');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Invalid request method');
            }

            if (!isset($getData['tax_id'])) {
                throw new Exception('Missing tax_id parameter');
            }

            $tax_id = $getData['tax_id'];
            $company = self::getCompanyById($tax_id);

            if (!$company) {
                throw new Exception('Company not found');
            }

            return json_encode([
                'success' => true,
                'data' => $company
            ]);
        } catch (Exception $e) {
            return json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public static function handleUpdateCompanyRequest($postData)
    {
        header('Content-Type: application/json');
        $data = [
            'tax_id' => $postData['tax_id'] ?? null, // Ensure keys exist
            'name' => $postData['name'] ?? null,
            'email' => $postData['email'] ?? null,
            'phone' => $postData['phone'] ?? null,
            'fax' => $postData['fax'] ?? null
        ];

        try {
            // 驗證必填欄位
            if (empty($data['tax_id']) || empty($data['name'])) {
                throw new Exception('統一編號和公司名稱為必填欄位');
            }
            $success = self::updateCompanyInfo($data);
            if ($success) {
                return json_encode(['success' => true, 'message' => '公司資料更新成功']);
            } else {
                throw new Exception('更新公司失敗');
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => '更新失敗: ' . $e->getMessage()]);
        }
    }
}

// Router logic to handle direct calls to this file
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    $response = null;

    switch ($action) {
        case 'addCompany':
            $response = CompanyFunctions::handleAddCompanyRequest($_POST);
            break;
        case 'deleteCompany':
            $response = CompanyFunctions::handleDeleteCompanyRequest($_POST);
            break;
        case 'getAllCompanies':
            $response = CompanyFunctions::handleGetAllCompaniesRequest();
            break;
        case 'getCompany':
            // handleGetCompanyRequest should internally check REQUEST_METHOD if necessary
            $response = CompanyFunctions::handleGetCompanyRequest($_GET);
            break;
        case 'updateCompany':
            $response = CompanyFunctions::handleUpdateCompanyRequest($_POST);
            break;
        default:
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            $response = json_encode(['success' => false, 'message' => 'Invalid action specified for company functions.']);
            break;
    }

    if ($response !== null) {
        echo $response;
    }
    exit; // Prevent further script execution
}
?>