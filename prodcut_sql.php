<?php
// 連接 MySQL 資料庫
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 獲取產品資料
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

$products = [];

while ($row = $result->fetch_assoc()) {
    $productId = $row['id'];

    // 獲取 variants
    $variants = [];
    $variantResult = $conn->query("SELECT * FROM variants WHERE product_id = $productId");
    while ($variantRow = $variantResult->fetch_assoc()) {
        $variants[] = $variantRow;
    }

    // 獲取 colors
    $colors = [];
    $colorResult = $conn->query("SELECT * FROM colors WHERE product_id = $productId");
    while ($colorRow = $colorResult->fetch_assoc()) {
        $colors[] = $colorRow;
    }

    // 獲取 galleryImages
    $galleryImages = [];
    $galleryResult = $conn->query("SELECT * FROM gallery_images WHERE product_id = $productId");
    while ($galleryRow = $galleryResult->fetch_assoc()) {
        $galleryImages[] = $galleryRow['image_url'];
    }

    $products[] = [
        'id' => $row['id'],
        'category' => $row['category'],
        'product' => [
            'name' => $row['name'],
            'image' => $row['image'],
            'description' => $row['description'],
            'variants' => $variants,
            'colors' => $colors,
            'galleryImages' => $galleryImages
        ]
    ];
}

// 返回 JSON 格式
header('Content-Type: application/json');
echo json_encode($products);

$conn->close();
?>