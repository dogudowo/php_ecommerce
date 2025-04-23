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

function addProduct($productName, $productType, $mainImage, $galleryImages, $sizes, $colors, $stocks, $prices, $productDescription = '')
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
        $productDir = "/Applications/XAMPP/xamppfiles/htdocs/public_html/imgs/products/" . $productName;
        if (!file_exists($productDir)) {
            if (!mkdir($productDir, 0777, true)) {
                throw new Exception("無法建立目錄：$productDir ，請檢查目錄權限。");
            }
        }
        if (!empty($mainImage['tmp_name'])) {
            if (!move_uploaded_file($mainImage['tmp_name'], "$productDir/main.jpg")) {
                throw new Exception("無法儲存主要圖片到目錄：$productDir");
            }
        }
        if (!empty($galleryImages['tmp_name'])) {
            foreach ($galleryImages['tmp_name'] as $index => $tmpName) {
                if (!empty($tmpName)) {
                    if (!move_uploaded_file($tmpName, "$productDir/gallery-" . ($index + 1) . ".jpg")) {
                        throw new Exception("無法儲存相簿圖片到目錄：$productDir");
                    }
                }
            }
        }

        // 新增尺寸、顏色、庫存
        $stmt = $conn->prepare("INSERT INTO Options (OptionID, ProductID, Color, Size, Price, Stock) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($sizes as $sizeIndex => $size) {
            $sizePrice = $prices[$sizeIndex]; // 每個尺寸對應一個價格

            foreach ($colors[$sizeIndex] as $colorIndex => $color) {
                $result = $conn->query("SELECT MAX(OptionID) AS max_id FROM Options");
                $row = $result->fetch_assoc();
                $optionId = $row['max_id'] + 1;

                $stock = $stocks[$sizeIndex][$colorIndex];
                $stmt->bind_param("iissii", $optionId, $productId, $color, $size, $sizePrice, $stock);
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

?>