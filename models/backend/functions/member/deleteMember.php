<?php
require_once __DIR__ . '/../../../../api/error_handler.php';
// 刪除會員及相關資料的處理文件

// 引入資料庫連接
require_once __DIR__ . '/../../../../api/db.php';

// 設置響應頭為JSON
header('Content-Type: application/json');

// 獲取POST數據
$data = json_decode(file_get_contents('php://input'), true);

// 檢查是否提供了會員ID
if (!isset($data['memberId']) || empty($data['memberId'])) {
    echo json_encode(['success' => false, 'message' => '未提供會員ID']);
    exit;
}

$memberId = intval($data['memberId']);

// 獲取資料庫連接
$pdo = getDBConnection();

// 開始事務處理，確保所有刪除操作要麼全部成功，要麼全部失敗
try {
    $pdo->beginTransaction();

    // 1. 刪除購物車項目
    $stmt = $pdo->prepare("DELETE FROM CartItem WHERE MemberID = ?");
    $stmt->execute([$memberId]);

    // 1.1 檢查並刪除cart表中的相關記錄（如果存在）
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE MemberID = ?");
        $stmt->execute([$memberId]);
    } catch (PDOException $e) {
        // 如果cart表不存在，忽略錯誤
        if (strpos($e->getMessage(), "Table 'd1256977.cart' doesn't exist") === false) {
            // 如果是其他錯誤，則拋出
            throw $e;
        }
    }

    // 2. 獲取該會員的所有訂單ID
    $stmt = $pdo->prepare("SELECT OrderID FROM Orders WHERE MembersID = ?");
    $stmt->execute([$memberId]);
    $orders = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 如果有訂單，則處理訂單相關資料
    if (!empty($orders)) {
        // 將訂單ID轉換為問號佔位符字符串，用於IN子句
        $orderPlaceholders = implode(',', array_fill(0, count($orders), '?'));

        // 3. 獲取收據ID
        $stmt = $pdo->prepare("SELECT ReceiptID FROM Receipt WHERE OrderID IN ($orderPlaceholders)");
        $stmt->execute($orders);
        $receipts = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 如果有收據，則處理收據相關資料
        if (!empty($receipts)) {
            // 將收據ID轉換為問號佔位符字符串
            $receiptPlaceholders = implode(',', array_fill(0, count($receipts), '?'));

            // 4. 獲取保修ID
            $stmt = $pdo->prepare("SELECT WarrantyID FROM ReceiptItem WHERE ReceiptID IN ($receiptPlaceholders)");
            $stmt->execute($receipts);
            $warranties = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 如果有保修記錄，則處理保修相關資料
            if (!empty($warranties)) {
                // 將保修ID轉換為問號佔位符字符串
                $warrantyPlaceholders = implode(',', array_fill(0, count($warranties), '?'));

                // 5. 刪除維修記錄
                $stmt = $pdo->prepare("DELETE FROM Repairs WHERE WarrantyID IN ($warrantyPlaceholders)");
                $stmt->execute($warranties);

                // 6. 刪除保修記錄
                $stmt = $pdo->prepare("DELETE FROM Warranty WHERE WarrantyID IN ($warrantyPlaceholders)");
                $stmt->execute($warranties);
            }

            // 7. 刪除收據項目
            $stmt = $pdo->prepare("DELETE FROM ReceiptItem WHERE ReceiptID IN ($receiptPlaceholders)");
            $stmt->execute($receipts);

            // 8. 刪除收據
            $stmt = $pdo->prepare("DELETE FROM Receipt WHERE ReceiptID IN ($receiptPlaceholders)");
            $stmt->execute($receipts);
        }

        // 9. 刪除訂單項目
        $stmt = $pdo->prepare("DELETE FROM OrderItem WHERE OrderID IN ($orderPlaceholders)");
        $stmt->execute($orders);

        // 10. 刪除訂單
        $stmt = $pdo->prepare("DELETE FROM Orders WHERE OrderID IN ($orderPlaceholders)");
        $stmt->execute($orders);
    }

    // 11. 最後刪除會員資料
    $stmt = $pdo->prepare("DELETE FROM Member WHERE MemberID = ?");
    $stmt->execute([$memberId]);

    // 提交事務
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => '會員及相關資料已成功刪除']);

} catch (Exception $e) {
    // 發生錯誤時回滾事務
    $pdo->rollBack();
    error_log("刪除會員錯誤: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '刪除會員失敗: ' . $e->getMessage()]);
}