<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

// 確認必要參數
if (!isset($_POST['productId']) || !isset($_POST['productName']) || !isset($_POST['productType'])) {
    echo json_encode([
        'success' => false,
        'message' => '缺少必要參數'
    ]);
    exit;
}

$productId = (int) $_POST['productId'];
$productName = $_POST['productName'];
$productType = $_POST['productType'];
$productDescription = $_POST['productDescription'] ?? '';
$sizes = $_POST['edit_sizes'] ?? [];
$prices = $_POST['edit_prices'] ?? [];
$optionIds = $_POST['edit_optionIds'] ?? [];
$colors = $_POST['edit_colors'] ?? [];
$stocks = $_POST['edit_stocks'] ?? [];
$sizeDescriptions = $_POST['edit_sizeDescriptions'] ?? [];

// 建立資料庫連線
$conn = db_connect();
$conn->begin_transaction();

try {
    // 更新產品基本資訊
    $stmt = $conn->prepare("UPDATE Product SET ProductName = ?, Type = ?, Introdution = ? WHERE ProductID = ?");
    $stmt->bind_param("sssi", $productName, $productType, $productDescription, $productId);
    $stmt->execute();

    // 處理產品選項
    foreach ($sizes as $sizeIndex => $size) {
        $price = $prices[$sizeIndex];
    
        // 處理空尺寸值，設為-1
        $size = empty($size) ? "-1" : $size;
    
        foreach ($colors[$sizeIndex] as $colorIndex => $color) {
            $stock = $stocks[$sizeIndex][$colorIndex];
            $optionId = $optionIds[$sizeIndex][$colorIndex];
    
            if ($optionId === 'new') {
                // 新增選項
                $result = $conn->query("SELECT MAX(OptionID) AS max_id FROM Options");
                $row = $result->fetch_assoc();
                $newOptionId = $row['max_id'] + 1;
    
                $stmt = $conn->prepare("INSERT INTO Options (OptionID, ProductID, Color, Size, SizeDescription, Price, Stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $sizeDesc = $sizeDescriptions[$sizeIndex] ?? null;
                $stmt->bind_param("iisssii", $newOptionId, $productId, $color, $size, $sizeDesc, $price, $stock);
                $stmt->execute();
            } else {
                // 更新既有選項
                $stmt = $conn->prepare("UPDATE Options SET Color = ?, Size = ?, SizeDescription = ?, Price = ?, Stock = ? WHERE OptionID = ?");
                $sizeDesc = $sizeDescriptions[$sizeIndex] ?? null;
                $stmt->bind_param("ssssii", $color, $size, $sizeDesc, $price, $stock, $optionId);
                $stmt->execute();
            }
        }
    }

    // 處理圖片上傳
    $productDir = "/Applications/XAMPP/xamppfiles/htdocs/public_html/imgs/products/" . $productName;

    // 如果目錄不存在就建立
    if (!file_exists($productDir)) {
        if (!mkdir($productDir, 0777, true)) {
            throw new Exception("無法建立目錄：$productDir");
        }
    }

    // 處理主要圖片
    if (isset($_FILES['mainImage']) && $_FILES['mainImage']['size'] > 0 && $_FILES['mainImage']['error'] === UPLOAD_ERR_OK) {
        if (!move_uploaded_file($_FILES['mainImage']['tmp_name'], "$productDir/main.jpg")) {
            throw new Exception("無法儲存主要圖片到目錄：$productDir");
        }
    }

    // 處理相簿圖片
    if (isset($_FILES['galleryImages']) && is_array($_FILES['galleryImages']['name'])) {
        $galleryCount = count($_FILES['galleryImages']['name']);

        // 找出已有的相簿圖片數量
        $existingCount = 0;
        while (file_exists("$productDir/gallery-" . ($existingCount + 1) . ".jpg")) {
            $existingCount++;
        }

        for ($i = 0; $i < $galleryCount; $i++) {
            if ($_FILES['galleryImages']['size'][$i] > 0 && $_FILES['galleryImages']['error'][$i] === UPLOAD_ERR_OK) {
                $imgIndex = $existingCount + $i + 1;
                if (!move_uploaded_file($_FILES['galleryImages']['tmp_name'][$i], "$productDir/gallery-$imgIndex.jpg")) {
                    throw new Exception("無法儲存相簿圖片到目錄：$productDir");
                }
            }
        }
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => '商品更新成功'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => '商品更新失敗：' . $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>