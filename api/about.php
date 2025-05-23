<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/error_handler.php';


// Company related functions
function getCompanyInfo()
{
    $db = getDBConnection();
    $query = "SELECT tax_id, name, email, phone, fax FROM company";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addCompany($tax_id, $name, $email = null, $phone = null, $fax = null)
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO company (tax_id, name, email, phone, fax) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$tax_id, $name, $email, $phone, $fax]);
}

function getCompanyById($tax_id)
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('SELECT * FROM company WHERE tax_id = :tax_id LIMIT 1');
    $stmt->bindParam(':tax_id', $tax_id, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateCompanyInfo($data)
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE company SET name = ?, email = ?, phone = ?, fax = ? WHERE tax_id = ?");
    return $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['fax'], $data['tax_id']]);
}

function deleteCompany($tax_id)
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM company WHERE tax_id = ?");
    return $stmt->execute([$tax_id]);
}

// Store related functions
function getStoreLocations()
{
    $db = getDBConnection();
    $query = "SELECT * FROM store";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $locations = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $locations[] = [
            'name' => $row['name'] ?? null,
            'service_time' => [$row['opening_time'] ?? null],
            'phone' => $row['phone'] ?? null,
            'address' => $row['location'] ?? null,
            'map_url' => $row['url'] ?? null
        ];
    }

    return $locations;
}

function getAllStores()
{
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM store");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStoreById($tax_id)
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM store WHERE tax_id = ?");
    $stmt->execute([$tax_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateStoreInfo($data)
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE store SET name = ?, phone = ?, location = ?, opening_time = ?, url = ? WHERE tax_id = ?");
    return $stmt->execute([$data['name'], $data['phone'], $data['location'], $data['opening_time'], $data['url'], $data['tax_id']]);
}

function addStore($data)
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO store (tax_id, name, phone, location, opening_time, url) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$data['tax_id'], $data['name'], $data['phone'], $data['location'], $data['opening_time'], $data['url']]);
}

function deleteStore($tax_id)
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM store WHERE tax_id = ?");
    return $stmt->execute([$tax_id]);
}

// Request Handlers
function handleGetAllCompaniesRequest()
{
    header('Content-Type: application/json');
    try {
        $companies = getCompanyInfo();
        return json_encode(['success' => true, 'data' => $companies]);
    } catch (Exception $e) {
        return json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetCompanyRequest($getData)
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
        $company = getCompanyById($tax_id);

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

function handleAddCompanyRequest($postData)
{
    header('Content-Type: application/json');
    try {
        if (empty($postData['tax_id']) || empty($postData['name'])) {
            throw new Exception('統一編號和公司名稱為必填欄位');
        }

        $result = addCompany(
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

function handleUpdateCompanyRequest($postData)
{
    header('Content-Type: application/json');
    $data = [
        'tax_id' => $postData['tax_id'] ?? null,
        'name' => $postData['name'] ?? null,
        'email' => $postData['email'] ?? null,
        'phone' => $postData['phone'] ?? null,
        'fax' => $postData['fax'] ?? null
    ];

    try {
        if (empty($data['tax_id']) || empty($data['name'])) {
            throw new Exception('統一編號和公司名稱為必填欄位');
        }
        $success = updateCompanyInfo($data);
        if ($success) {
            return json_encode(['success' => true, 'message' => '公司資料更新成功']);
        } else {
            throw new Exception('更新公司失敗');
        }
    } catch (Exception $e) {
        return json_encode(['success' => false, 'message' => '更新失敗: ' . $e->getMessage()]);
    }
}

function handleDeleteCompanyRequest($postData)
{
    header('Content-Type: application/json');
    try {
        if (empty($postData['tax_id'])) {
            throw new Exception('統一編號為必填欄位');
        }

        $result = deleteCompany($postData['tax_id']);

        if ($result) {
            return json_encode(['success' => true, 'message' => '公司刪除成功']);
        } else {
            throw new Exception('刪除公司失敗');
        }
    } catch (Exception $e) {
        return json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetAllStoresRequest()
{
    header('Content-Type: application/json');
    try {
        $stores = getAllStores();
        return json_encode(['success' => true, 'data' => $stores]);
    } catch (Exception $e) {
        return json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleGetStoreRequest($getData)
{
    header('Content-Type: application/json');
    try {
        if (!isset($getData['tax_id'])) {
            throw new Exception('缺少 tax_id 參數');
        }
        $tax_id = $getData['tax_id'];
        $store = getStoreById($tax_id);
        if ($store) {
            return json_encode(['success' => true, 'data' => $store]);
        } else {
            return json_encode(['success' => false, 'message' => '找不到指定門市']);
        }
    } catch (Exception $e) {
        return json_encode(['success' => false, 'message' => '取得門市資料失敗: ' . $e->getMessage()]);
    }
}

function handleAddStoreRequest($postData)
{
    header('Content-Type: application/json');
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query('SELECT MAX(tax_id) AS max_tax_id FROM store');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_tax_id = ($result['max_tax_id'] ?? 0) + 1;

        if (empty($postData['name'])) {
            throw new Exception('門市名稱為必填欄位');
        }

        $data = [
            'tax_id' => $new_tax_id,
            'name' => $postData['name'],
            'phone' => $postData['phone'] ?? null,
            'location' => $postData['location'] ?? null,
            'opening_time' => $postData['opening_time'] ?? null,
            'url' => preg_match('/src="([^"]+)"/', $postData['url'] ?? '', $matches) ? $matches[1] : ($postData['url'] ?? null),
        ];

        $success = addStore($data);
        if ($success) {
            return json_encode(['success' => true, 'message' => '門市新增成功']);
        } else {
            throw new Exception('新增門市失敗');
        }
    } catch (Exception $e) {
        return json_encode(['success' => false, 'message' => '新增失敗: ' . $e->getMessage()]);
    }
}

function handleUpdateStoreRequest($postData)
{
    header('Content-Type: application/json');
    try {
        if (empty($postData['tax_id']) || empty($postData['name'])) {
            throw new Exception('統一編號和門市名稱為必填欄位');
        }
        $data = [
            'tax_id' => $postData['tax_id'],
            'name' => $postData['name'],
            'phone' => $postData['phone'] ?? null,
            'location' => $postData['location'] ?? null,
            'opening_time' => $postData['opening_time'] ?? null,
            'url' => preg_match('/src="([^"]+)"/', $postData['url'] ?? '', $matches) ? $matches[1] : ($postData['url'] ?? null),
        ];

        $success = updateStoreInfo($data);
        if ($success) {
            return json_encode(['success' => true, 'message' => '門市更新成功']);
        } else {
            throw new Exception('更新門市失敗');
        }
    } catch (Exception $e) {
        return json_encode(['success' => false, 'message' => '更新失敗: ' . $e->getMessage()]);
    }
}

function handleDeleteStoreRequest($postData)
{
    header('Content-Type: application/json');
    try {
        if (empty($postData['tax_id'])) {
            throw new Exception('缺少 tax_id 參數');
        }
        $tax_id = $postData['tax_id'];
        $success = deleteStore($tax_id);
        if ($success) {
            return json_encode(['success' => true, 'message' => '門市刪除成功']);
        } else {
            throw new Exception('刪除門市失敗');
        }
    } catch (Exception $e) {
        return json_encode(['success' => false, 'message' => '刪除失敗: ' . $e->getMessage()]);
    }
}

// Router logic to handle direct calls to this file
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    $response = null;

    switch ($action) {
        case 'getAllCompanies':
            $response = handleGetAllCompaniesRequest();
            break;
        case 'getCompany':
            $response = handleGetCompanyRequest($_GET);
            break;
        case 'addCompany':
            $response = handleAddCompanyRequest($_POST);
            break;
        case 'updateCompany':
            $response = handleUpdateCompanyRequest($_POST);
            break;
        case 'deleteCompany':
            $response = handleDeleteCompanyRequest($_POST);
            break;
        case 'getAllStores':
            $response = handleGetAllStoresRequest();
            break;
        case 'getStore':
            $response = handleGetStoreRequest($_GET);
            break;
        case 'addStore':
            $response = handleAddStoreRequest($_POST);
            break;
        case 'updateStore':
            $response = handleUpdateStoreRequest($_POST);
            break;
        case 'deleteStore':
            $response = handleDeleteStoreRequest($_POST);
            break;
        default:
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            $response = json_encode(['success' => false, 'message' => 'Invalid action specified for about functions.']);
            break;
    }

    if ($response !== null) {
        echo $response;
    }
    exit; // Prevent further script execution
}

?>