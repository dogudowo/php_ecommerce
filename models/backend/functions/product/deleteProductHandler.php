<?php
require_once __DIR__ . '/../../../../api/db.php';

// 設置 JSON 頭
header('Content-Type: application/json');

// 刪除商品
function deleteProduct($productId)
{
    $pdo = getDBConnection();
    try {
        $pdo->beginTransaction();

        // 先刪除商品選項
        $stmt = $pdo->prepare("DELETE FROM Options WHERE ProductID = ?");
        $stmt->execute([$productId]);

        // 再刪除商品
        $stmt = $pdo->prepare("DELETE FROM Product WHERE ProductID = ?");
        $stmt->execute([$productId]);

        $pdo->commit();

        return [
            'success' => true
        ];
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting product: " . $e->getMessage());
        return [
            'success' => false,
            'message' => "刪除商品失敗: " . $e->getMessage()
        ];
    }
}

// 處理請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['productId'])) {
        echo json_encode(['success' => false, 'message' => '缺少商品ID']);
        exit;
    }

    $productId = (int) $_POST['productId'];
    $result = deleteProduct($productId);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => '請使用POST方法']);
}
?>