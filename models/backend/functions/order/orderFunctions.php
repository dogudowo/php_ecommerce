<?php
// 訂單管理函數文件

// 引入數據庫連接
require_once __DIR__ . '/../../../../api/db.php';

// 檢查是否是 API 請求
if (basename($_SERVER['SCRIPT_NAME']) === 'orderFunctions.php') {
    // 設置 JSON 頭
    header('Content-Type: application/json');

    // 定義常量，表示此文件是從管理後台訪問的
    define('INCLUDED_FROM_ADMIN', true);

    // 處理訂單詳情請求
    handleOrderDetailsRequest();
    exit;
} else {
    // 防止直接訪問此文件
    if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
        header('Location: ../../../../login.php');
        exit;
    }
}

// 獲取所有訂單
function getAllOrders()
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT o.OrderID, o.OrderDate, o.Status, m.Name as MemberName, 
                              COUNT(oi.OptionID) as ItemCount, 
                              SUM(op.Price * oi.Quantity) as TotalAmount 
                              FROM Orders o 
                              JOIN Member m ON o.MembersID = m.MemberID 
                              JOIN OrderItem oi ON o.OrderID = oi.OrderID 
                              JOIN Options op ON oi.OptionID = op.OptionID 
                              GROUP BY o.OrderID 
                              ORDER BY o.OrderID DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching orders: " . $e->getMessage());
        return [];
    }
}

function findProductImageUrl(int $productId): string
{
    // 伺服器上的實體路徑
    $dir = __DIR__ . "/../../../../imgs/products/{$productId}";
    // glob 依序找 main.jpg, main.jpeg, main.png, main.gif
    error_log("Searching for images in: " . $dir);
    $files = glob($dir . '/main.{jpg,jpeg,png,gif}', GLOB_BRACE);
    error_log("Found files: " . print_r($files, true));
    if (!empty($files)) {
        // 只取第一個，轉成 URL 路徑
        $filename = basename($files[0]);
        return "/public_html/imgs/products/{$productId}/{$filename}";
    }
    // 找不到就用預設圖
    return "/public_html/imgs/products/default-placeholder.png";
}

function getOrderDetailsBackend(int $orderId, ?PDO $pdo = null): array
{
    $pdo = $pdo ?? getDBConnection();

    // 1. 獲取訂單基本信息（訂單ID、會員名稱、訂單日期）
    $orderInfoSql = <<<SQL
        SELECT 
            o.OrderID,
            m.Name as MemberName,
            o.OrderDate,
            o.Status
        FROM Orders o
        JOIN Member m ON o.MembersID = m.MemberID
        WHERE o.OrderID = :orderId
    SQL;
    $stmt = $pdo->prepare($orderInfoSql);
    $stmt->execute(['orderId' => $orderId]);
    $orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orderInfo) {
        return ['error' => '找不到訂單'];
    }

    $status = $orderInfo['Status'];

    // 2. 明細
    $detailSql = <<<SQL
        SELECT
            p.ProductName,
            p.ProductID,
            opt.Color,
            opt.Size,
            opt.SizeDescription,
            opt.Price,
            oi.Quantity,
            (opt.Price * oi.Quantity) AS Subtotal
        FROM OrderItem oi
        JOIN Options opt ON oi.OptionID   = opt.OptionID
        JOIN Product p   ON opt.ProductID = p.ProductID
        WHERE oi.OrderID = :orderId
    SQL;
    $stmt = $pdo->prepare($detailSql);
    $stmt->execute(['orderId' => $orderId]);

    $items = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $productId = (int) $row['ProductID'];
        // **直接從資料夾尋找**，並回傳 URL
        $row['image'] = findProductImageUrl($productId);
        $items[] = $row;
    }

    // 樣式 & 文字
    $statusClass = match ($status) {
        'Completed' => 'bg-success',
        'Pending' => 'bg-info text-dark',
        'Shipping' => 'bg-primary',
        'Cancel' => 'bg-warning',
        'abnormal' => 'bg-danger',
        default => 'bg-secondary',
    };
    $statusText = getOrderStatusList()[$status] ?? $status;

    return [
        'order_id' => $orderInfo['OrderID'],
        'member_name' => $orderInfo['MemberName'],
        'order_date' => $orderInfo['OrderDate'],
        'items' => $items,
        'total_amount' => array_sum(array_column($items, 'Subtotal')),
        'status' => $status,
        'statusClass' => $statusClass,
        'statusText' => $statusText,
    ];
}

// 更新訂單狀態
function updateOrderStatus($orderId, $status)
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("UPDATE Orders SET Status = ? WHERE OrderID = ?");
        $result = $stmt->execute([$status, $orderId]);
        return $result;
    } catch (PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        return false;
    }
}

// 獲取訂單狀態列表
function getOrderStatusList()
{
    return [
        'Completed' => '已完成',
        'Pending' => '處理中',
        'Shipping' => '運送中',
        'Cancel' => '已取消',
        'abnormal' => '異常'
    ];
}

// 獲取訂單狀態樣式
function getOrderStatusClass($status)
{
    return match ($status) {
        'Completed' => 'bg-success',
        'Pending' => 'bg-info text-dark',
        'Shipping' => 'bg-primary',
        'Cancel' => 'bg-warning',
        'abnormal' => 'bg-danger',
        default => 'bg-secondary'
    };
}

// 獲取訂單狀態文字
function getStatusText($status)
{
    $statusList = getOrderStatusList();
    return $statusList[$status] ?? $status;
}

// 處理訂單詳情請求
function handleOrderDetailsRequest()
{
    // 檢查請求方法
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    if ($requestMethod === 'GET') {
        // 處理獲取訂單詳情請求
        handleGetOrderDetails();
    } elseif ($requestMethod === 'POST') {
        // 處理更新訂單狀態請求
        handleUpdateOrderStatus();
    } else {
        // 不支持的請求方法
        echo json_encode([
            'success' => false,
            'message' => '不支持的請求方法: ' . $requestMethod
        ]);
    }
}

// 處理獲取訂單詳情請求
function handleGetOrderDetails()
{
    // 檢查是否提供了訂單ID
    if (!isset($_GET['orderId']) || empty($_GET['orderId'])) {
        echo json_encode([
            'success' => false,
            'message' => '缺少訂單ID參數'
        ]);
        return;
    }

    // 獲取訂單ID
    $orderId = (int) $_GET['orderId'];

    try {
        // 使用 getOrderDetailsBackend 函數獲取訂單詳情
        $orderDetails = getOrderDetailsBackend($orderId);

        // 返回訂單詳情
        echo json_encode([
            'success' => true,
            'data' => $orderDetails
        ]);
    } catch (Exception $e) {
        // 記錄錯誤
        error_log("Error getting order details: " . $e->getMessage());

        // 返回錯誤信息
        echo json_encode([
            'success' => false,
            'message' => '獲取訂單詳情時發生錯誤: ' . $e->getMessage()
        ]);
    }
}

// 處理更新訂單狀態請求
function handleUpdateOrderStatus()
{
    // 獲取 POST 數據
    $postData = json_decode(file_get_contents('php://input'), true);

    // 檢查是否提供了必要參數
    if (
        !isset($postData['orderId']) || empty($postData['orderId']) ||
        !isset($postData['status']) || empty($postData['status'])
    ) {
        echo json_encode([
            'success' => false,
            'message' => '缺少必要參數: orderId 或 status'
        ]);
        return;
    }

    // 獲取參數
    $orderId = (int) $postData['orderId'];
    $status = $postData['status'];

    try {
        // 更新訂單狀態
        $result = updateOrderStatus($orderId, $status);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => '訂單狀態已成功更新'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '更新訂單狀態失敗'
            ]);
        }
    } catch (Exception $e) {
        // 記錄錯誤
        error_log("Error updating order status: " . $e->getMessage());

        // 返回錯誤信息
        echo json_encode([
            'success' => false,
            'message' => '更新訂單狀態時發生錯誤: ' . $e->getMessage()
        ]);
    }
}