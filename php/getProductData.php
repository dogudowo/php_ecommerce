<?php
// 啟用錯誤顯示，方便調試
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

// 修正路徑引入
require_once 'APIs.php';

// 設置響應內容類型為 JSON
header('Content-Type: application/json');

// 驗證是否有傳入 ID 參數
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => '缺少有效的商品 ID'
    ]);
    exit;
}

$productId = (int) $_GET['id'];

// 取得產品資料
$product = getProductById($productId);

if ($product === null) {
    echo json_encode([
        'success' => false,
        'message' => '找不到指定的商品'
    ]);
    exit;
}

// 確保產品有 success 鍵
$product['success'] = true;

// 回傳 JSON 格式的產品資料
echo json_encode($product);
?>