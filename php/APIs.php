<?php
// 確保引用此檔案的程式碼使用正確的路徑，例如：require_once 'php/getMember.php';
require_once 'db_connect.php';

function getMembers($conn = null)
{
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

    $sql = "SELECT 
                m.MemberID,
                m.Name as membername,
                m.Phone as phonenum,
                m.Email as memberemail,
                COUNT(DISTINCT o.OrderID) as order_count,
                CASE 
                    WHEN m.IsAdmin = 1 THEN '管理員'
                    ELSE '會員'
                END as role
            FROM Member m
            LEFT JOIN Orders o ON m.MemberID = o.MembersID
            GROUP BY m.MemberID, m.Name, m.Phone, m.Email, m.IsAdmin
            ORDER BY m.MemberID ASC";

    $result = $conn->query($sql);

    if (!$result) {
        error_log("資料庫查詢失敗：" . $conn->error);
        if ($closeConn) {
            $conn->close();
        }
        return [];
    }

    $members = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
    }

    if ($closeConn) {
        $conn->close();
    }
    return $members;
}

function addProduct($productName, $productType, $mainImage, $galleryImages, $sizes, $sizeDescriptions, $colors, $stocks, $prices, $productDescription = '')
{
    $conn = db_connect();
    $conn->begin_transaction();

    try {
        // 手動生成 ProductID
        $result = $conn->query("SELECT MAX(ProductID) AS max_id FROM Product");
        $row = $result->fetch_assoc();
        $productId = $row['max_id'] + 1;

        // 新增商品 (增加 Introduction 欄位)
        $stmt = $conn->prepare("INSERT INTO Product (ProductID, Type, ProductName, Introdution) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $productId, $productType, $productName, $productDescription);
        $stmt->execute();

        // 儲存圖片
        handleProductImages($productName, $mainImage, $galleryImages);

        // 新增尺寸、顏色、庫存
        $stmt = $conn->prepare("INSERT INTO Options (OptionID, ProductID, Color, Size, SizeDescription, Price, Stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($sizes as $sizeIndex => $size) {
            $sizePrice = $prices[$sizeIndex];
            $sizeDesc = $sizeDescriptions[$sizeIndex] ?? null;

            foreach ($colors[$sizeIndex] as $colorIndex => $color) {
                $result = $conn->query("SELECT MAX(OptionID) AS max_id FROM Options");
                $row = $result->fetch_assoc();
                $optionId = $row['max_id'] + 1;

                $stock = $stocks[$sizeIndex][$colorIndex];
                $stmt->bind_param("iisssii", $optionId, $productId, $color, $size, $sizeDesc, $sizePrice, $stock);
                $stmt->execute();
            }
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("新增商品失敗：" . $e->getMessage());
        return false;
    } finally {
        $conn->close();
    }
}

function getProducts()
{
    $conn = db_connect();

    $sql = "SELECT 
                p.ProductID,
                p.ProductName,
                p.Type,
                p.Introdution as Description,
                MIN(o.Price) as MinPrice,
                MAX(o.Price) as MaxPrice,
                SUM(o.Stock) as TotalStock
            FROM Product p
            LEFT JOIN Options o ON p.ProductID = o.ProductID
            GROUP BY p.ProductID, p.ProductName, p.Type, p.Introdution
            ORDER BY p.ProductID";

    $result = $conn->query($sql);

    if (!$result) {
        error_log("資料庫查詢失敗：" . $conn->error);
        $conn->close();
        return [];
    }

    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }

    $conn->close();
    return $products;
}

function getProductById($productId)
{
    $conn = db_connect();

    // 獲取產品基本信息
    $stmt = $conn->prepare("SELECT ProductID, ProductName, Type, Introdution as Description FROM Product WHERE ProductID = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $conn->close();
        return null;
    }

    $product = $result->fetch_assoc();

    // 獲取該產品的所有選項
    $stmt = $conn->prepare("SELECT OptionID, Color, Size, SizeDescription, Price, Stock FROM Options WHERE ProductID = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $optionsResult = $stmt->get_result();

    $product['options'] = [];
    $sizeOptions = [];

    while ($option = $optionsResult->fetch_assoc()) {
        $size = $option['Size'];

        if (!isset($sizeOptions[$size])) {
            $sizeOptions[$size] = [
                'size' => $size,
                'price' => $option['Price'],
                'sizeDescription' => $option['SizeDescription'],
                'colors' => []
            ];
        }

        $sizeOptions[$size]['colors'][] = [
            'color' => $option['Color'],
            'stock' => $option['Stock'],
            'optionId' => $option['OptionID']
        ];
    }

    $product['sizeOptions'] = array_values($sizeOptions);

    // 檢查產品圖片是否存在
    $productDir = "/Applications/XAMPP/xamppfiles/htdocs/public_html/imgs/products/" . $product['ProductName'];
    $webPath = "/public_html/imgs/products/" . $product['ProductName'];

    $product['mainImage'] = file_exists($productDir . "/main.jpg") ? $webPath . "/main.jpg" : "https://via.placeholder.com/150";

    $product['galleryImages'] = [];
    $i = 1;
    while (file_exists($productDir . "/gallery-" . $i . ".jpg")) {
        $product['galleryImages'][] = $webPath . "/gallery-" . $i . ".jpg";
        $i++;
    }

    $conn->close();
    return $product;
}

function handleProductImages($productName, $mainImage, $galleryImages) {
    $productDir = "/Applications/XAMPP/xamppfiles/htdocs/public_html/imgs/products/" . $productName;
    
    if (!file_exists($productDir)) {
        if (!mkdir($productDir, 0777, true)) {
            throw new Exception("無法建立目錄：$productDir");
        }
    }

    if (!empty($mainImage['tmp_name'])) {
        if (!move_uploaded_file($mainImage['tmp_name'], "$productDir/main.jpg")) {
            throw new Exception("無法儲存主要圖片");
        }
    }

    if (!empty($galleryImages['tmp_name'])) {
        foreach ($galleryImages['tmp_name'] as $index => $tmpName) {
            if (!empty($tmpName)) {
                if (!move_uploaded_file($tmpName, "$productDir/gallery-" . ($index + 1) . ".jpg")) {
                    throw new Exception("無法儲存相簿圖片");
                }
            }
        }
    }
}

function validateProductInput($input) {
    if (empty($input['productName']) || empty($input['productType'])) {
        throw new Exception('缺少必要欄位');
    }
    
    if (empty($input['colors']) || empty($input['stocks']) || empty($input['prices'])) {
        throw new Exception('缺少商品規格資訊');
    }
}
?>