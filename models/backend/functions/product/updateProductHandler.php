<?php
require_once __DIR__ . '/../../../../api/db.php';

// 設置 JSON 頭
header('Content-Type: application/json');

// 更新商品
function updateProduct($productId, $data)
{
    $pdo = getDBConnection();
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE Product SET Type = ?, ProductName = ?, Introdution = ?, isActive = ? WHERE ProductID = ?");
        $stmt->execute([$data['type'], $data['name'], $data['intro'], $data['active'], $productId]);

        $pdo->commit();

        return [
            'success' => true
        ];
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating product: " . $e->getMessage());
        return [
            'success' => false,
            'message' => "更新商品失敗: " . $e->getMessage()
        ];
    }
}

// 添加商品選項
function addProductOption($productId, $data)
{
    $pdo = getDBConnection();
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO Options (ProductID, Color, Size, SizeDescription, Price, Stock) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$productId, $data['color'], $data['size'], $data['sizeDescription'], $data['price'], $data['stock']]);

        $optionId = $pdo->lastInsertId();
        $pdo->commit();

        return [
            'success' => true,
            'optionId' => $optionId
        ];
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error adding product option: " . $e->getMessage());
        return [
            'success' => false,
            'message' => "添加商品選項失敗: " . $e->getMessage()
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
    $data = [
        'name' => $_POST['productName'] ?? '',
        'type' => $_POST['productType'] ?? '',
        'intro' => $_POST['productIntro'] ?? '',
        'active' => $_POST['productActive'] ?? '0'
    ];

    $result = updateProduct($productId, $data);

    // 處理尺寸和顏色選項
    if ($result['success'] && isset($_POST['sizes'])) {
        $sizes = json_decode($_POST['sizes'], true);
        if (is_array($sizes)) {
            // 更新現有選項或添加新選項
            $pdo = getDBConnection();
            foreach ($sizes as $size) {
                if (empty($size['size']) || empty($size['price']))
                    continue;

                foreach ($size['colors'] as $color) {
                    if (empty($color['color']) || empty($color['stock']))
                        continue;

                    addProductOption($productId, [
                        'size' => $size['size'],
                        'sizeDescription' => $size['sizeDescription'] ?? '',
                        'price' => $size['price'],
                        'color' => $color['color'],
                        'stock' => $color['stock']
                    ]);
                }
            }
        }
    }

    // 處理主圖片
    if ($result['success'] && isset($_FILES['mainImage']) && $_FILES['mainImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/../../../imgs/products/{$productId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $mainImagePath = $uploadDir . "main.jpg";
        move_uploaded_file($_FILES['mainImage']['tmp_name'], $mainImagePath);
    }

    // 處理畫廊圖片
    if ($result['success'] && isset($_FILES['galleryImages'])) {
        $uploadDir = __DIR__ . "/../../../imgs/products/{$productId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 刪除現有的畫廊圖片
        $galleryPattern = $uploadDir . "gallery-*.jpg";
        foreach (glob($galleryPattern) as $file) {
            unlink($file);
        }

        // 上傳新的畫廊圖片
        $galleryCount = count($_FILES['galleryImages']['name']);
        for ($i = 0; $i < $galleryCount; $i++) {
            if ($_FILES['galleryImages']['error'][$i] === UPLOAD_ERR_OK) {
                $galleryImagePath = $uploadDir . "gallery-" . ($i + 1) . ".jpg";
                move_uploaded_file($_FILES['galleryImages']['tmp_name'][$i], $galleryImagePath);
            }
        }
    }

    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => '請使用POST方法']);
}
?>