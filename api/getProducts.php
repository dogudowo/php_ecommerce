<?php
require_once __DIR__ . '/error_handler.php';
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// 只接受 GET、且必須帶 action 參數
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    try {
        switch ($_GET['action']) {
            case 'getProducts':
                // 取得所有商品
                $products = getProductsBackend();
                $response = [
                    'success' => true,
                    'products' => array_map(function ($product) {
                        return [
                            'ProductID' => $product['ProductID'],
                            'ProductName' => $product['ProductName'],
                            'Type' => $product['Type'],
                            'TotalStock' => calculateTotalStock($product['ProductID']),
                            'isActive' => (int) $product['isActive'],
                        ];
                    }, $products)
                ];
                break;

            case 'getProduct':
                // 取得單一商品
                if (!isset($_GET['id'])) {
                    http_response_code(400);
                    $response = [
                        'success' => false,
                        'message' => '缺少商品ID參數',
                    ];
                    break;
                }

                $product = getProductByIdBackend($_GET['id']);
                if (!$product) {
                    http_response_code(404);
                    $response = [
                        'success' => false,
                        'message' => '找不到指定商品',
                    ];
                    break;
                }

                $response = [
                    'success' => true,
                    'product' => [
                        'ProductID' => $product['ProductID'],
                        'ProductName' => $product['ProductName'],
                        'Type' => $product['Type'],
                        'TotalStock' => calculateTotalStock($product['ProductID']),
                        'isActive' => (int) $product['isActive'],
                        'Introdution' => $product['Introdution'],
                        'mainImage' => $product['mainImage'], // 新增 mainImage
                        'galleryImage' => $product['galleryImage'], // 新增 galleryImage
                        'sizeOptions' => $product['sizeOptions']
                    ]
                ];
                break;

            default:
                // action 不合法
                http_response_code(400);
                $response = [
                    'success' => false,
                    'message' => '無效的 action 參數',
                ];
                break;
        }

        // 回傳結果
        echo json_encode($response);
    } catch (Exception $e) {
        // 處理例外
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '獲取商品資料時發生錯誤: ' . $e->getMessage(),
        ]);
    }
} else {
    // 非法請求
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => '僅接受帶有 action 的 GET 請求',
    ]);
}
exit();

/**
 * 獲取後台商品列表
 * 
 * @return array
 */
function getProductsBackend()
{
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT ProductID, ProductName, Type, Introdution, isActive FROM Product");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 根據商品ID獲取單一商品資料
 * 
 * @param int $productId
 * @return array|null
 */
function getProductByIdBackend($productId)
{
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT ProductID, ProductName, Type, Introdution, isActive FROM Product WHERE ProductID = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // 獲取商品圖片
        $product['mainImage'] = file_exists(__DIR__ . '/../imgs/products/' . $productId . '/main.jpg')
            ? 'imgs/products/' . $productId . '/main.jpg'
            : null;

        // 獲取商品尺寸選項
        $stmt = $db->prepare("SELECT OptionID, Color, Size, SizeDescription, Price, Stock FROM `Options` WHERE ProductID = ?");
        $stmt->execute([$productId]);
        $product['sizeOptions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 獲取商品圖庫圖片
        $productDir = __DIR__ . '/../imgs/products/' . $productId . '/';
        $product['galleryImage'] = [];

        if (file_exists($productDir)) {
            $files = array_diff(scandir($productDir), array('.', '..'));
            foreach ($files as $file) {
                if (strpos($file, 'gallery-') === 0) {
                    $product['galleryImage'][] = 'imgs/products/' . $productId . '/' . $file;
                }
            }
        }
    }

    return $product;
}

/**
 * 計算商品總庫存
 *
 * @param int $productId
 * @return int
 */
function calculateTotalStock($productId)
{
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT SUM(Stock) AS TotalStock FROM `Options` WHERE ProductID = ?");
    $stmt->execute([$productId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return isset($result['TotalStock']) ? (int) $result['TotalStock'] : 0;
}