<?php
require_once __DIR__ . '/../../../../api/db.php';

class StoreFunctions
{
    public static function getAllStores()
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM store");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getStoreById($tax_id)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM store WHERE tax_id = ?");
        $stmt->execute([$tax_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateStoreInfo($data)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE store SET name = ?, phone = ?, location = ?, opening_time = ?, url = ? WHERE tax_id = ?");
        return $stmt->execute([$data['name'], $data['phone'], $data['location'], $data['opening_time'], $data['url'], $data['tax_id']]);
    }

    public static function addStore($data)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO store (tax_id, name, phone, location, opening_time, url) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['tax_id'], $data['name'], $data['phone'], $data['location'], $data['opening_time'], $data['url']]);
    }

    public static function deleteStore($tax_id)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM store WHERE tax_id = ?");
        return $stmt->execute([$tax_id]);
    }

    // Request Handlers
    public static function handleGetAllStoresRequest()
    {
        header('Content-Type: application/json');
        try {
            $stores = self::getAllStores();
            return json_encode(['success' => true, 'data' => $stores]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function handleGetStoreRequest($getData)
    {
        header('Content-Type: application/json');
        try {
            if (!isset($getData['tax_id'])) {
                throw new Exception('缺少 tax_id 參數');
            }
            $tax_id = $getData['tax_id'];
            $store = self::getStoreById($tax_id);
            if ($store) {
                return json_encode(['success' => true, 'data' => $store]);
            } else {
                return json_encode(['success' => false, 'message' => '找不到指定門市']);
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => '取得門市資料失敗: ' . $e->getMessage()]);
        }
    }

    public static function handleAddStoreRequest($postData)
    {
        header('Content-Type: application/json');
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query('SELECT MAX(tax_id) AS max_tax_id FROM store');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_tax_id = ($result['max_tax_id'] ?? 0) + 1; // Handle case where table is empty

            if (empty($postData['name'])) {
                throw new Exception('門市名稱為必填欄位');
            }

            $data = [
                'tax_id' => $new_tax_id,
                'name' => $postData['name'],
                'phone' => $postData['phone'] ?? null,
                'location' => $postData['location'] ?? null,
                'opening_time' => $postData['opening_time'] ?? null,
                'url' => preg_match('/src="([^"]+)"/', $postData['map_url'] ?? '', $matches) ? $matches[1] : ($postData['map_url'] ?? null),
            ];

            $success = self::addStore($data);
            if ($success) {
                return json_encode(['success' => true, 'message' => '門市新增成功']);
            } else {
                throw new Exception('新增門市失敗');
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => '新增失敗: ' . $e->getMessage()]);
        }
    }

    public static function handleUpdateStoreRequest($postData)
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
                'url' => preg_match('/src="([^"]+)"/', $postData['map_url'] ?? '', $matches) ? $matches[1] : ($postData['map_url'] ?? null),
            ];

            $success = self::updateStoreInfo($data);
            if ($success) {
                return json_encode(['success' => true, 'message' => '門市更新成功']);
            } else {
                throw new Exception('更新門市失敗');
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => '更新失敗: ' . $e->getMessage()]);
        }
    }

    public static function handleDeleteStoreRequest($postData)
    {
        header('Content-Type: application/json');
        try {
            if (empty($postData['tax_id'])) {
                throw new Exception('缺少 tax_id 參數');
            }
            $tax_id = $postData['tax_id'];
            $success = self::deleteStore($tax_id);
            if ($success) {
                return json_encode(['success' => true, 'message' => '門市刪除成功']);
            } else {
                throw new Exception('刪除門市失敗');
            }
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => '刪除失敗: ' . $e->getMessage()]);
        }
    }
}

// Router logic to handle direct calls to this file
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    $response = null;

    switch ($action) {
        case 'getAllStores':
            $response = StoreFunctions::handleGetAllStoresRequest();
            break;
        case 'getStore':
            $response = StoreFunctions::handleGetStoreRequest($_GET);
            break;
        case 'addStore':
            $response = StoreFunctions::handleAddStoreRequest($_POST);
            break;
        case 'updateStore':
            $response = StoreFunctions::handleUpdateStoreRequest($_POST);
            break;
        case 'deleteStore':
            $response = StoreFunctions::handleDeleteStoreRequest($_POST);
            break;
        default:
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            $response = json_encode(['success' => false, 'message' => 'Invalid action specified for store functions.']);
            break;
    }

    if ($response !== null) {
        echo $response;
    }
    exit; // Prevent further script execution
}
?>