<?php
require_once __DIR__ . '/error_handler.php';
session_start();
header('Content-Type: application/json');

// 檢查用戶是否登入（除了某些特定操作外）
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // 允許的操作列表（不需要登入的操作）
    $allowedActions = [];

    // 如果當前操作不在允許列表中，則返回錯誤
    if (!isset($_GET['action']) || !in_array($_GET['action'], $allowedActions)) {
        echo json_encode([
            'success' => false,
            'message' => '請先登入'
        ]);
        exit;
    }
}

// 資料庫連接
require_once __DIR__ . '/db.php';

// 獲取購物車商品
function getCartItems($memberId)
{
    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("SELECT c.OptionID, c.Quantity, p.ProductName, p.ProductID, o.Color, o.Size, o.SizeDescription, o.Price 
                               FROM CartItem c 
                               JOIN Options o ON c.OptionID = o.OptionID 
                               JOIN Product p ON o.ProductID = p.ProductID 
                               WHERE c.MemberID = ?");
        $stmt->execute([$memberId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data' => $items
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => '獲取購物車失敗: ' . $e->getMessage()
        ];
    }
}

// 添加商品到購物車
function addToCart($memberId, $optionId, $quantity)
{
    $conn = getDBConnection();

    try {
        // 檢查商品是否已在購物車中
        $stmt = $conn->prepare("SELECT * FROM CartItem WHERE MemberID = ? AND OptionID = ?");
        $stmt->execute([$memberId, $optionId]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            // 更新數量
            $newQuantity = $existingItem['Quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE CartItem SET Quantity = ? WHERE MemberID = ? AND OptionID = ?");
            $stmt->execute([$newQuantity, $memberId, $optionId]);
        } else {
            // 添加新商品
            $stmt = $conn->prepare("INSERT INTO CartItem (MemberID, OptionID, Quantity) VALUES (?, ?, ?)");
            $stmt->execute([$memberId, $optionId, $quantity]);
        }

        return [
            'success' => true,
            'message' => '商品已添加到購物車'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => '添加商品失敗: ' . $e->getMessage()
        ];
    }
}

// 從購物車移除商品
function removeFromCart($memberId, $optionId)
{
    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("DELETE FROM CartItem WHERE MemberID = ? AND OptionID = ?");
        $stmt->execute([$memberId, $optionId]);

        return [
            'success' => true,
            'message' => '商品已從購物車移除'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => '移除商品失敗: ' . $e->getMessage()
        ];
    }
}

// 更新購物車商品數量
function updateCartQuantity($memberId, $optionId, $quantity)
{
    $conn = getDBConnection();

    try {
        // 檢查數量是否有效
        if ($quantity <= 0) {
            return [
                'success' => false,
                'message' => '數量必須大於0'
            ];
        }

        $stmt = $conn->prepare("UPDATE CartItem SET Quantity = ? WHERE MemberID = ? AND OptionID = ?");
        $stmt->execute([$quantity, $memberId, $optionId]);

        return [
            'success' => true,
            'message' => '數量已更新'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => '更新數量失敗: ' . $e->getMessage()
        ];
    }
}

// 獲取購物車總金額
function getCartTotal($memberId)
{
    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("SELECT SUM(c.Quantity * o.Price) as total 
                               FROM CartItem c 
                               JOIN Options o ON c.OptionID = o.OptionID 
                               WHERE c.MemberID = ?");
        $stmt->execute([$memberId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'total' => floatval($result['total'] ?? 0)
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => '獲取總金額失敗: ' . $e->getMessage()
        ];
    }
}

// 清空購物車
function clearCart($memberId)
{
    $conn = getDBConnection();

    try {
        $stmt = $conn->prepare("DELETE FROM CartItem WHERE MemberID = ?");
        $stmt->execute([$memberId]);

        return [
            'success' => true,
            'message' => '購物車已清空'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => '清空購物車失敗: ' . $e->getMessage()
        ];
    }
}

// 處理請求
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $memberId = isset($_GET['memberId']) ? $_GET['memberId'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

    // 檢查會員ID是否有效
    if (!$memberId && $action != 'getCartCount') {
        echo json_encode([
            'success' => false,
            'message' => '無效的會員ID'
        ]);
        exit;
    }

    // 根據操作執行相應的函數
    switch ($action) {
        case 'getCartItems':
            echo json_encode(getCartItems($memberId));
            break;

        case 'addToCart':
            // 從POST數據中獲取參數
            $data = json_decode(file_get_contents('php://input'), true);
            $optionId = $data['optionId'] ?? null;
            $quantity = $data['quantity'] ?? 1;

            if (!$optionId) {
                echo json_encode([
                    'success' => false,
                    'message' => '無效的商品選項ID'
                ]);
                exit;
            }

            echo json_encode(addToCart($memberId, $optionId, $quantity));
            break;

        case 'removeFromCart':
            // 從POST數據中獲取參數
            $data = json_decode(file_get_contents('php://input'), true);
            $optionId = $data['optionId'] ?? null;

            if (!$optionId) {
                echo json_encode([
                    'success' => false,
                    'message' => '無效的商品選項ID'
                ]);
                exit;
            }

            echo json_encode(removeFromCart($memberId, $optionId));
            break;

        case 'updateCartQuantity':
            // 從POST數據中獲取參數
            $data = json_decode(file_get_contents('php://input'), true);
            $optionId = $data['optionId'] ?? null;
            $quantity = $data['quantity'] ?? null;

            if (!$optionId || !$quantity) {
                echo json_encode([
                    'success' => false,
                    'message' => '無效的參數'
                ]);
                exit;
            }

            echo json_encode(updateCartQuantity($memberId, $optionId, $quantity));
            break;

        case 'getCartTotal':
            echo json_encode(getCartTotal($memberId));
            break;

        case 'clearCart':
            echo json_encode(clearCart($memberId));
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => '無效的操作'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => '未指定操作'
    ]);
}