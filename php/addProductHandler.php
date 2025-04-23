<?php
header('Content-Type: application/json');
require_once '/Applications/XAMPP/xamppfiles/htdocs/public_html/php/APIs.php';

try {
    // 記錄接收到的資料
    error_log("接收到新增商品請求");

    // 檢查必要欄位
    if (empty($_POST['productName']) || empty($_POST['productType']) || empty($_FILES['mainImage']['tmp_name'])) {
        throw new Exception('缺少必要欄位');
    }

    if (empty($_POST['sizes']) || empty($_POST['colors']) || empty($_POST['stocks']) || empty($_POST['prices'])) {
        throw new Exception('缺少商品規格資訊');
    }

    // 取得商品敘述（如果存在）
    $productDescription = isset($_POST['productDescription']) ? $_POST['productDescription'] : '';

    // 呼叫 addProduct 函數，並加入商品敘述參數
    $success = addProduct(
        $_POST['productName'],
        $_POST['productType'],
        $_FILES['mainImage'],
        isset($_FILES['galleryImages']) ? $_FILES['galleryImages'] : [],
        $_POST['sizes'],
        $_POST['sizeDescriptions'] ?? [],
        $_POST['colors'],
        $_POST['stocks'],
        $_POST['prices'],
        $productDescription  // 增加這個參數
    );

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '新增商品失敗，請查看伺服器日誌']);
    }
} catch (Exception $e) {
    error_log('新增商品時發生錯誤: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>