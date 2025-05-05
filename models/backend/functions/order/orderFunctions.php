<?php
require_once __DIR__ . '/../../../../api/db.php';

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
                              ORDER BY o.OrderDate DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching orders: " . $e->getMessage());
        return [];
    }
}

// 獲取訂單詳情
function getOrderDetails($orderId)
{
    $pdo = getDBConnection();
    try {
        // 獲取訂單基本信息
        $stmt = $pdo->prepare("SELECT o.*, m.Name as MemberName, m.Email, m.Phone, m.City, m.Address 
                              FROM Orders o 
                              JOIN Member m ON o.MembersID = m.MemberID 
                              WHERE o.OrderID = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // 獲取訂單項目
        $stmt = $pdo->prepare("SELECT oi.*, op.Color, op.Size, op.SizeDescription, op.Price, 
                              p.ProductName, p.Type 
                              FROM OrderItem oi 
                              JOIN Options op ON oi.OptionID = op.OptionID 
                              JOIN Product p ON op.ProductID = p.ProductID 
                              WHERE oi.OrderID = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 獲取付款信息
        $stmt = $pdo->prepare("SELECT r.* 
                              FROM Receipt r 
                              WHERE r.OrderID = ?");
        $stmt->execute([$orderId]);
        $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'order' => $order,
            'items' => $items,
            'receipt' => $receipt
        ];
    } catch (PDOException $e) {
        error_log("Error fetching order details: " . $e->getMessage());
        return null;
    }
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
?>