<?php
require_once 'APIs.php';

function testGetProductById()
{
    echo "Running tests for getProductById function...\n";

    // Test Case 1: Valid Product ID
    $validProductId = 1; // Replace with an actual valid ProductID from your database
    $product = getProductById($validProductId);

    if ($product !== null) {
        echo "Test Case 1: Valid Product ID\n";
        assert(isset($product['ProductID']), "ProductID should be set");
        assert(isset($product['ProductName']), "ProductName should be set");
        assert(isset($product['sizeOptions']), "sizeOptions should be set");
        assert(is_array($product['sizeOptions']), "sizeOptions should be an array");
        assert(isset($product['galleryImages']), "galleryImages should be set");
        assert(is_array($product['galleryImages']), "galleryImages should be an array");
        echo "Test Case 1 passed.\n";
    } else {
        echo "Test Case 1 failed: Product not found.\n";
    }

    // Test Case 2: Invalid Product ID
    $invalidProductId = 999999; // Replace with a non-existent ProductID
    $product = getProductById($invalidProductId);

    echo "Test Case 2: Invalid Product ID\n";
    assert($product === null, "Product should be null for an invalid ProductID");
    echo "Test Case 2 passed.\n";

    // Test Case 3: Product ID is 0
    $zeroProductId = 0;
    $product = getProductById($zeroProductId);

    echo "Test Case 3: Product ID is 0\n";
    assert($product === null, "Product should be null for ProductID 0");
    echo "Test Case 3 passed.\n";

    // Test Case 4: Negative Product ID
    $negativeProductId = -1;
    $product = getProductById($negativeProductId);

    echo "Test Case 4: Negative Product ID\n";
    assert($product === null, "Product should be null for a negative ProductID");
    echo "Test Case 4 passed.\n";

    // Test Case 5: Non-numeric Product ID
    $nonNumericProductId = "abc";
    $product = getProductById($nonNumericProductId);

    echo "Test Case 5: Non-numeric Product ID\n";
    assert($product === null, "Product should be null for a non-numeric ProductID");
    echo "Test Case 5 passed.\n";

    // Test Case 6: Float Product ID
    $floatProductId = 1.5; // Replace with a valid ProductID if 1 is valid
    $product = getProductById((int) $floatProductId);

    echo "Test Case 6: Float Product ID\n";
    if ($product !== null) {
        assert(isset($product['ProductID']), "ProductID should be set for a valid ProductID");
        assert(isset($product['galleryImages']), "galleryImages should be set for a valid ProductID");
        echo "Test Case 6 passed.\n";
    } else {
        echo "Test Case 6 failed: Product not found.\n";
    }

    echo "All tests completed.\n";
}

function testGetProductByIdGalleryImages()
{
    echo "測試 getProductById 函數是否返回 galleryImages 陣列\n";

    // 測試案例1：有效的產品ID，應包含 galleryImages
    $validProductId = 1; // 假設 ID=1 是有效的產品
    $product = getProductById($validProductId);

    if ($product !== null) {
        echo "返回的產品資料：\n";
        print_r($product);

        assert(
            isset($product['galleryImages']) && is_array($product['galleryImages']),
            "測試有效產品ID ($validProductId) 包含 galleryImages 陣列"
        );

        // 測試 galleryImages 陣列是否為非空
        assert(
            count($product['galleryImages']) > 0,
            "galleryImages 陣列應包含至少一個圖片路徑"
        );

        // 測試 galleryImages 中的每個元素是否包含 url 和 description
        foreach ($product['galleryImages'] as $image) {
            assert(
                isset($image['url']) && isset($image['description']),
                "galleryImages 中的每個元素應包含 url 和 description"
            );
        }

        echo "Test Case: Valid Product ID with galleryImages passed.\n";
    } else {
        echo "測試有效產品ID ($validProductId) 時返回 null，請確認資料庫中存在該產品\n";
        assert(false, "測試有效產品ID ($validProductId) 時返回 null");
    }

    echo "所有測試完成\n";
}

// Run the tests
testGetProductById();
testGetProductByIdGalleryImages();
?>