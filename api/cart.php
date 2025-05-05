<?php
require_once __DIR__ . '/db.php';

// 只在直接訪問時設置 JSON 頭，而不是在被引入時
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'getCartItems':
            if (!isset($_GET['memberId'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => '缺少memberId參數']);
                exit;
            }
            $memberId = $_GET['memberId'];
            $items = getCartItems($memberId);
            echo json_encode([
                'success' => true,
                'data' => $items,
                'isEmpty' => empty($items)
            ]);
            exit;

        case 'getCartTotal':
            if (!isset($_GET['memberId'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => '缺少memberId參數']);
                exit;
            }
            $memberId = $_GET['memberId'];
            $total = getCartTotal($memberId);
            echo json_encode([
                'success' => true,
                'total' => $total
            ]);
            exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($_GET['action']) {
        case 'addToCart':
            if (!isset($input['memberId']) || !isset($input['optionId']) || !isset($input['quantity'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => '缺少必要參數']);
                exit;
            }
            $result = addToCart($input['memberId'], $input['optionId'], $input['quantity']);
            echo json_encode($result);
            exit;

        case 'removeFromCart':
            if (!isset($input['memberId']) || !isset($input['optionId'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => '缺少必要參數']);
                exit;
            }
            $result = removeFromCart($input['memberId'], $input['optionId']);
            echo json_encode($result);
            exit;

        case 'updateCartQuantity':
            if (!isset($input['memberId']) || !isset($input['optionId']) || !isset($input['quantity'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => '缺少必要參數']);
                exit;
            }
            $result = updateCartQuantity($input['memberId'], $input['optionId'], $input['quantity']);
            echo json_encode($result);
            exit;
    }
}

function getCartItems($memberId)
{
    $conn = db_connect();

    try {
        $stmt = $conn->prepare("SELECT 
                ci.Quantity, 
                ci.OptionID, 
                p.ProductName, 
                p.ProductID, 
                o.Price, 
                o.Color, 
                o.SizeDescription, 
                o.Size 
            FROM CartItem ci 
            JOIN Options o ON ci.OptionID = o.OptionID 
            JOIN Product p ON o.ProductID = p.ProductID 
            WHERE ci.MemberID = ?");
        $stmt->execute([$memberId]);

        $items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = $row;
        }

        // 獲取商品圖片
        foreach ($items as &$item) {
            $productDir = __DIR__ . "/../imgs/products/" . $item['ProductID'];
            $mainImage = glob($productDir . "/*main*.{jpg,jpeg,png,gif}", GLOB_BRACE);
            $item['image'] = $mainImage ? str_replace(__DIR__ . '/../', '', $mainImage[0]) : 'imgs/default-product.jpg';
        }

        return $items;
    } catch (Exception $e) {
        error_log("獲取購物車商品失敗：" . $e->getMessage());
        return [];
    }
}

function addToCart($memberId, $optionId, $quantity)
{
    $conn = db_connect();
    $conn->beginTransaction();

    try {
        // 檢查是否已有相同商品在購物車
        $stmt = $conn->prepare("SELECT Quantity FROM CartItem WHERE MemberID = ? AND OptionID = ?");
        $stmt->execute([$memberId, $optionId]);

        if ($stmt->rowCount() > 0) {
            // 更新數量
            $row = $stmt->fetch();
            $newQuantity = $row['Quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE CartItem SET Quantity = ? WHERE MemberID = ? AND OptionID = ?");
            $stmt->execute([$newQuantity, $memberId, $optionId]);
        } else {
            // 新增商品
            $stmt = $conn->prepare("INSERT INTO CartItem (MemberID, OptionID, Quantity) VALUES (?, ?, ?)");
            $stmt->execute([$memberId, $optionId, $quantity]);
        }

        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("加入購物車失敗：" . $e->getMessage());
        return ['success' => false, 'message' => '加入購物車失敗'];
    }
}

function removeFromCart($memberId, $optionId)
{
    $conn = db_connect();

    try {
        $stmt = $conn->prepare("DELETE FROM CartItem WHERE MemberID = ? AND OptionID = ?");
        $stmt->execute([$memberId, $optionId]);
        return ['success' => true];
    } catch (Exception $e) {
        error_log("移除購物車商品失敗：" . $e->getMessage());
        return ['success' => false, 'message' => '移除商品失敗'];
    }
}

function updateCartQuantity($memberId, $optionId, $quantity)
{
    $conn = db_connect();

    try {
        $stmt = $conn->prepare("UPDATE CartItem SET Quantity = ? WHERE MemberID = ? AND OptionID = ?");
        $stmt->execute([$quantity, $memberId, $optionId]);
        return ['success' => true];
    } catch (Exception $e) {
        error_log("更新購物車數量失敗：" . $e->getMessage());
        return ['success' => false, 'message' => '更新數量失敗'];
    }
}

function getCartTotal($memberId)
{
    $conn = db_connect();

    try {
        $stmt = $conn->prepare("SELECT SUM(ci.Quantity * o.Price) as total 
                                FROM CartItem ci 
                                JOIN Options o ON ci.OptionID = o.OptionID 
                                WHERE ci.MemberID = ?");
        $stmt->execute([$memberId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        error_log("計算購物車總金額失敗：" . $e->getMessage());
        return 0;
    }
}
?>