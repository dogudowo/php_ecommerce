<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../../api/error_handler.php';
require_once __DIR__ . '/../../../../api/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isDirectCall = basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__);

if ($isDirectCall) {
    header('Content-Type: application/json; charset=utf-8');
    if (!($_SESSION['logged_in'] ?? false)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '未授權的存取']);
        exit;
    }
    handleOrderApi();
    exit;
} elseif (!defined('INCLUDED_FROM_ADMIN') && !($_SESSION['logged_in'] ?? false)) {
    header('Location: ../../../../login.php');
    exit;
}

/** 取得訂單詳情 */
function getOrderDetailsBackend(int $orderId, ?PDO $pdo = null): array
{
    $pdo ??= getDBConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. 訂單基本資料
    $baseSql = "\n        SELECT  o.OrderID, m.Name AS MemberName, o.OrderDate, o.Status\n        FROM    Orders o\n        JOIN    Member m ON m.MemberID = o.MemberID\n        WHERE   o.OrderID = :id\n        LIMIT   1";
    $stmt = $pdo->prepare($baseSql);
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        return ['error' => '找不到訂單'];
    }

    // 2. 訂單明細
    $detailSql = "\n        SELECT  r.RecordID,\n                COALESCE(p.ProductID, 0)        AS ProductID,\n                r.ProductName,\n                r.ProductType,\n                r.ProductColor                 AS Color,\n                r.ProductSize                  AS Size,\n                r.ProductPrice                 AS Price,\n                i.Quantity,\n                (r.ProductPrice * i.Quantity)  AS Subtotal\n        FROM        OrderItem   i\n        JOIN        OrderRecord r ON r.RecordID = i.OrderRecordID\n        LEFT JOIN   Product     p ON p.ProductName = r.ProductName\n                                 AND p.Type        = r.ProductType\n        WHERE       i.OrderID   = :id";
    $stmt = $pdo->prepare($detailSql);
    $stmt->execute(['id' => $orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. 狀態
    $statusClass = getOrderStatusClass($order['Status']);
    $statusText = getStatusText($order['Status']);

    return [
        'order_id' => (int) $order['OrderID'],
        'member_name' => $order['MemberName'],
        'order_date' => $order['OrderDate'],
        'items' => $items,
        'total_amount' => array_sum(array_column($items, 'Subtotal')),
        'status' => $order['Status'],
        'statusClass' => $statusClass,
        'statusText' => $statusText,
    ];
}

/** 更新訂單狀態 */
function updateOrderStatus(int $orderId, string $status): bool
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare('UPDATE Orders SET Status = ? WHERE OrderID = ?');
        return $stmt->execute([$status, $orderId]);
    } catch (PDOException $e) {
        error_log('[updateOrderStatus] ' . $e->getMessage());
        return false;
    }
}

/** 訂單狀態列表（英文 → 中文） */
function getOrderStatusList(): array
{
    return [
        'Pending' => '處理中',
        'Shipping' => '已送達',
        'Completed' => '已完成',
        'Cancel' => '已取消',
        'abnormal' => '異常',
    ];
}

function getOrderStatusClass(string $status): string
{
    return [
        'Pending' => 'bg-info text-dark',
        'Shipping' => 'bg-primary',
        'Completed' => 'bg-success',
        'Cancel' => 'bg-warning',
        'abnormal' => 'bg-danger',
    ][$status] ?? 'bg-secondary';
}

function getStatusText(string $status): string
{
    return getOrderStatusList()[$status] ?? $status;
}
function handleOrderApi(): void
{
    $method = strtoupper($_SERVER['REQUEST_METHOD']);

    try {
        match ($method) {
            'GET' => handleGetOrderDetails(),
            'POST' => handleUpdateOrderStatus(),
            default => jsonResponse(['success' => false, 'message' => "不支持的請求方法: $method"], 405),
        };
    } catch (Throwable $e) {
        error_log('[handleOrderApi] ' . $e->getMessage());
        jsonResponse(['success' => false, 'message' => '服務端發生錯誤，請稍後再試'], 500);
    }
}

/* GET /?orderId=123 */
function handleGetOrderDetails(): void
{
    $orderId = isset($_GET['orderId']) ? (int) $_GET['orderId'] : 0;
    if ($orderId <= 0) {
        jsonResponse(['success' => false, 'message' => '缺少或無效的 orderId 參數'], 400);
    }

    $data = getOrderDetailsBackend($orderId);
    if (isset($data['error'])) {
        jsonResponse(['success' => false, 'message' => $data['error']], 404);
    }
    jsonResponse(['success' => true, 'data' => $data]);
}

/* POST JSON {orderId, status} */
function handleUpdateOrderStatus(): void
{
    $payload = json_decode(file_get_contents('php://input'), true) ?? [];
    $orderId = (int) ($payload['orderId'] ?? 0);
    $status = (string) ($payload['status'] ?? '');

    if ($orderId <= 0 || $status === '') {
        jsonResponse(['success' => false, 'message' => '缺少或無效的 orderId 或 status'], 400);
    }
    if (!array_key_exists($status, getOrderStatusList())) {
        jsonResponse(['success' => false, 'message' => '無效的訂單狀態'], 422);
    }

    $ok = updateOrderStatus($orderId, $status);
    jsonResponse([
        'success' => $ok,
        'message' => $ok ? '訂單狀態已更新' : '更新失敗',
    ], $ok ? 200 : 500);
}
function jsonResponse(array $data, int $httpCode = 200): never
{
    http_response_code($httpCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
?>