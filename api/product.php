<?php
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/db.php';

// 只在直接訪問時設置 JSON 頭，而不是在被引入時
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
}

function getProducts()
{
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("
            SELECT 
                p.*, 
                MIN(o.Price) AS MinPrice, 
                MAX(o.Price) AS MaxPrice 
            FROM Product p 
            LEFT JOIN Options o 
                ON p.ProductID = o.ProductID 
            WHERE p.isActive = 1 
            GROUP BY p.ProductID
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $products;
    } catch (PDOException $e) {
        return [];
    }
}

function getProductById(int $productId): ?array
{
    $db = getDBConnection();

    // 1. 取得基本商品欄位
    $stmt = $db->prepare("SELECT * FROM Product WHERE ProductID = :id");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        return null;
    }

    // 2. 取得價格範圍
    $stmt = $db->prepare("
        SELECT
            MIN(Price) AS MinPrice,
            MAX(Price) AS MaxPrice
        FROM Options
        WHERE ProductID = :id
    ");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $priceRange = $stmt->fetch(PDO::FETCH_ASSOC);
    $product['MinPrice'] = $priceRange['MinPrice'] ?? 0;
    $product['MaxPrice'] = $priceRange['MaxPrice'] ?? 0;

    // 3. 聚合尺寸選項與顏色（不包含 Stock 在 GROUP BY）
    $stmt = $db->prepare("
        SELECT
            o.Size,
            o.SizeDescription,
            o.Price,
            GROUP_CONCAT(DISTINCT o.Color ORDER BY o.Color SEPARATOR ',') AS Colors
        FROM Options o
        WHERE o.ProductID = :id
        GROUP BY o.Size, o.SizeDescription, o.Price
        ORDER BY o.Size ASC
    ");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $sizeOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. 針對每個顏色，查出對應的 OptionID 與 Stock
    foreach ($sizeOptions as &$opt) {
        $colors = explode(',', $opt['Colors'] ?? '');
        $opt['colors'] = array_map(function ($color) use ($db, $productId, $opt) {
            $s = $db->prepare("
                SELECT OptionID, Stock
                FROM Options
                WHERE ProductID = :pid
                  AND Size = :size
                  AND Color = :color
                LIMIT 1
            ");
            $s->bindParam(':pid', $productId, PDO::PARAM_INT);
            $s->bindParam(':size', $opt['Size'], PDO::PARAM_STR);
            $s->bindParam(':color', $color, PDO::PARAM_STR);
            $s->execute();
            return $s->fetch(PDO::FETCH_ASSOC);
        }, $colors);
    }
    unset($opt);
    $product['sizeOptions'] = $sizeOptions;

    // 5. 設定主圖（mainImage）欄位，若檔案不存在則 fallback default.jpg
    $defaultMain = "imgs/products/{$productId}/main.jpg";
    $product['mainImage'] = file_exists(__DIR__ . "/../{$defaultMain}")
        ? $defaultMain
        : 'imgs/products/default.jpg';

    // 6. 掃描並收集畫廊圖片
    $galleryImages = [];
    $i = 1;
    while (file_exists(__DIR__ . "/../imgs/products/{$productId}/gallery-{$i}.jpg")) {
        $galleryImages[] = [
            'url' => "imgs/products/{$productId}/gallery-{$i}.jpg"
        ];
        $i++;
    }
    $product['galleryImages'] = $galleryImages;

    return $product;
}

function getProductsByType($type)
{
    $allProducts = getProducts();
    $result = [];

    foreach ($allProducts as $p) {
        $details = getProductById($p['ProductID']);
        $p['details'] = $details;

        if (!isset($p['Type'])) {
            continue;
        }

        switch ($type) {
            case 'luggage':
                if (in_array(strtolower($p['Type']), ['aluminum', 'zipper'])) {
                    $result[] = $p;
                }
                break;
            case 'accessories':
                if (strtolower($p['Type']) === 'accessories') {
                    $result[] = $p;
                }
                break;
            case 'travel':
                if (strtolower($p['Type']) === 'travel') {
                    $result[] = $p;
                }
                break;
            case 'featured':
                if (strtolower($p['Type']) === 'featured') {
                    $result[] = $p;
                }
                break;
        }
    }

    return $result;
}

function renderProductCard(array $product)
{
    $details = $product['details'] ?? $product;

    $minPrice = $details['MinPrice'] ?? 0;
    $maxPrice = $details['MaxPrice'] ?? 0;

    $sizes = array_column($details['sizeOptions'] ?? [], 'Size');
    $filteredSizes = array_filter($sizes, fn($s) => $s != -1);

    $sizeClass = '';
    if (!empty($filteredSizes)) {
        $max = max($filteredSizes);
        if ($max > 28) {
            $sizeClass = 'large';
        } elseif ($max >= 25) {
            $sizeClass = 'medium';
        } elseif ($max >= 21) {
            $sizeClass = 'small';
        } else {
            $sizeClass = 'cabin';
        }
    }

    if (!isset($product['ProductID'])) {
        return;
    }

    // 直接使用 mainImage 欄位，無則 fallback 預設
    $img = $details['mainImage']
        ?? "imgs/products/{$product['ProductID']}/main.jpg";
    // 如果實際檔案不存在，再用 default.jpg
    if (!file_exists(__DIR__ . '/../' . $img)) {
        $img = 'imgs/products/default.jpg';
    }

    $type = htmlspecialchars($product['Type'] ?? 'unknown', ENT_QUOTES);
    $prodId = (int) $product['ProductID'];
    $prodName = htmlspecialchars($product['ProductName'] ?? '商品名稱未設定', ENT_QUOTES);

    ob_start();
    ?>
    <div class="col-lg-3 col-md-4 col-6 d-flex justify-content-center product-item mb-3" data-size="<?= $sizeClass ?>"
        data-material="<?= $type ?>" data-sizes="<?= htmlspecialchars(implode(',', $filteredSizes), ENT_QUOTES) ?>">
        <div class="card" style="width:100%; max-width:320px; border-radius:20px; transition:transform .2s;"
            onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <a href="productDetail.php?product_id=<?= $prodId ?>" class="text-decoration-none text-black">
                <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" class="card-img-top"
                    style="border-radius:20px 20px 0 0; object-fit:cover;" alt="<?= $prodName ?>">
                <div class="card-body pb-0 mb-2">
                    <h5 class="card-title mb-1"><?= $prodName ?></h5>
                    <?php
if (!empty($filteredSizes)): ?>
                        <p class="card-text mb-0">
                            尺寸：<?= implode(', ', $filteredSizes) ?> 吋
                        </p>
                    <?php
endif; ?>
                    <p class="mb-0">
                        價格：
                        <span class="text-decoration-line-through text-muted">
                            $<?= number_format(round(($maxPrice ?: 1000) * 1.2), 0) ?>
                        </span>
                    </p>
                    <p class="text-danger fs-4 mt-0">
                        <strong>$<?= number_format($minPrice ?: 800, 0) ?></strong>
                    </p>
                </div>
            </a>
            <div class="card-footer bg-transparent border-0 text-center pb-3">
                <a href="productDetail.php?product_id=<?= $prodId ?>" class="btn btn-success"
                    style="border-radius:20px; width:80%;">
                    查看詳情
                </a>
            </div>
        </div>
    </div>
    <?php
    $output = ob_get_clean();

    if (empty(trim($output))) {
        return '<div class="col-lg-3 col-md-4 mb-3">
                  <div class="alert alert-warning">商品資料載入中...</div>
                </div>';
    }
    return $output;
}

// 獲取所有商品（帶分頁、篩選和搜索功能）
function getAllProducts($page = 1, $limit = 10, $type = '', $status = '', $search = '')
{
    $pdo = getDBConnection();
    try {
        $params = [];
        $sql = "SELECT p.ProductID, p.Type, p.ProductName, p.Introdution, p.isActive, 
                SUM(o.Stock) as TotalStock 
                FROM Product p 
                LEFT JOIN Options o ON p.ProductID = o.ProductID ";

        // 添加篩選條件
        $whereClause = [];

        if (!empty($type)) {
            $whereClause[] = "p.Type = :type";
            $params[':type'] = $type;
        }

        if ($status !== '') {
            $whereClause[] = "p.isActive = :status";
            $params[':status'] = $status;
        }

        if (!empty($search)) {
            $whereClause[] = "(p.ProductName LIKE :search OR p.ProductID LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if (!empty($whereClause)) {
            $sql .= " WHERE " . implode(" AND ", $whereClause);
        }

        $sql .= " GROUP BY p.ProductID";

        // 計算總記錄數
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();

        // 添加分頁
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key == ':limit') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else if ($key == ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else if ($key == ':status') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();

        return [
            'products' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'totalRecords' => $totalRecords,
            'totalPages' => ceil($totalRecords / $limit),
            'currentPage' => $page
        ];
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        return [
            'products' => [],
            'totalRecords' => 0,
            'totalPages' => 0,
            'currentPage' => $page
        ];
    }
}

// 獲取商品選項
function getProductOptions($productId)
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT * FROM Options WHERE ProductID = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching product options: " . $e->getMessage());
        return [];
    }
}

// 獲取商品類型列表
function getProductTypes()
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT Type FROM Product");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log("Error fetching product types: " . $e->getMessage());
        return [];
    }
}

// 獲取單個商品詳情
function getProduct($productId)
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT * FROM Product WHERE ProductID = ?");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching product: " . $e->getMessage());
        return null;
    }
}

// 處理圖片上傳的輔助函數
function handleImageUpload($fileInputName, $productId, $imageType = 'main', $galleryOrder = null)
{
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . "/../imgs/products/{$productId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = '';
        $fileExtension = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            return ['success' => false, 'message' => '不支援的圖片格式。'];
        }

        if ($imageType === 'main') {
            $fileName = "main.{$fileExtension}";
            // 刪除舊的主圖片 (不同副檔名的情況)
            foreach ($allowedExtensions as $ext) {
                if (file_exists($uploadDir . "main.{$ext}") && "main.{$ext}" !== $fileName) {
                    unlink($uploadDir . "main.{$ext}");
                }
            }
        } elseif ($imageType === 'gallery' && $galleryOrder !== null) {
            $fileName = "gallery-" . $galleryOrder . ".{$fileExtension}";
            // 刪除舊的畫廊圖片 (相同順序但不同副檔名的情況)
            foreach ($allowedExtensions as $ext) {
                if (file_exists($uploadDir . "gallery-" . $galleryOrder . ".{$ext}") && "gallery-" . $galleryOrder . ".{$ext}" !== $fileName) {
                    unlink($uploadDir . "gallery-" . $galleryOrder . ".{$ext}");
                }
            }
        } else {
            return ['success' => false, 'message' => '無效的圖片類型或順序。'];
        }

        $destination = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $destination)) {
            return ['success' => true, 'filePath' => "imgs/products/{$productId}/{$fileName}"];
        } else {
            return ['success' => false, 'message' => '圖片上傳失敗。'];
        }
    }
    return ['success' => true, 'message' => '沒有提供圖片或上傳出錯。']; // 沒有檔案不是錯誤，除非是必須的
}

// 新增商品
function addProduct($data, $files)
{
    $pdo = getDBConnection();
    try {
        $pdo->beginTransaction();

        // 手動獲取新的 ProductID
        $stmtMaxId = $pdo->query("SELECT MAX(ProductID) as max_id FROM Product");
        $maxIdRow = $stmtMaxId->fetch(PDO::FETCH_ASSOC);
        $productId = ($maxIdRow && $maxIdRow['max_id'] !== null) ? (int) $maxIdRow['max_id'] + 1 : 1;

        $stmt = $pdo->prepare("INSERT INTO Product (ProductID, ProductName, Type, Introdution, isActive) VALUES (:id, :name, :type, :intro, :active)");
        $stmt->execute([
            ':id' => $productId,
            ':name' => $data['productName'],
            ':type' => $data['productType'],
            ':intro' => $data['productIntro'],
            ':active' => $data['productActive']
        ]);
        // $productId 已經手動設定，不再使用 lastInsertId()

        // 處理主圖片上傳
        if (isset($files['mainImage'])) {
            handleImageUpload('mainImage', $productId, 'main');
        }

        // 處理畫廊圖片上傳
        if (isset($files['galleryImages'])) {
            $galleryImages = $files['galleryImages'];
            $galleryOrders = $data['galleryOrders'] ?? []; // 從 POST 獲取順序
            if (is_array($galleryImages['name'])) { // 多檔案上傳
                for ($i = 0; $i < count($galleryImages['name']); $i++) {
                    $_FILES['currentGalleryImage'] = [
                        'name' => $galleryImages['name'][$i],
                        'type' => $galleryImages['type'][$i],
                        'tmp_name' => $galleryImages['tmp_name'][$i],
                        'error' => $galleryImages['error'][$i],
                        'size' => $galleryImages['size'][$i]
                    ];
                    // 使用 galleryOrders 陣列中的順序，如果存在且有效
                    $order = isset($galleryOrders[$i]) ? (int) $galleryOrders[$i] : ($i + 1);
                    handleImageUpload('currentGalleryImage', $productId, 'gallery', $order);
                }
            }
        }

        // 處理商品選項
        if (isset($data['sizes'])) {
            $options = json_decode($data['sizes'], true);
            if (is_array($options)) {
                // 手動獲取起始 OptionID
                $stmtMaxOptId = $pdo->query("SELECT MAX(OptionID) as max_opt_id FROM Options");
                $maxOptIdRow = $stmtMaxOptId->fetch(PDO::FETCH_ASSOC);
                $currentOptionId = ($maxOptIdRow && $maxOptIdRow['max_opt_id'] !== null) ? (int) $maxOptIdRow['max_opt_id'] + 1 : 1;

                $stmtOpt = $pdo->prepare("INSERT INTO Options (OptionID, ProductID, Size, SizeDescription, Price, Color, Stock) VALUES (:optid, :pid, :size, :desc, :price, :color, :stock)");
                foreach ($options as $opt) {
                    $stmtOpt->execute([
                        ':optid' => $currentOptionId,
                        ':pid' => $productId,
                        ':size' => $opt['Size'],
                        ':desc' => $opt['SizeDescription'],
                        ':price' => $opt['Price'],
                        ':color' => $opt['Color'],
                        ':stock' => $opt['Stock']
                    ]);
                    $currentOptionId++; // 為下一個選項遞增ID
                }
            }
        }

        $pdo->commit();
        return ['success' => true, 'message' => '商品新增成功', 'productId' => $productId];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Add product error: " . $e->getMessage());
        return ['success' => false, 'message' => '新增商品失敗: ' . $e->getMessage()];
    }
}

// 更新商品
function updateProduct($data, $files)
{
    $pdo = getDBConnection();
    try {
        $pdo->beginTransaction();
        $productId = $data['productId'];

        $stmt = $pdo->prepare("UPDATE Product SET ProductName = :name, Type = :type, Introdution = :intro, isActive = :active WHERE ProductID = :id");
        $stmt->execute([
            ':id' => $productId,
            ':name' => $data['productName'],
            ':type' => $data['productType'],
            ':intro' => $data['productIntro'],
            ':active' => $data['productActive']
        ]);

        // 處理主圖片上傳
        if (isset($files['mainImage']) && $files['mainImage']['error'] === UPLOAD_ERR_OK) {
            handleImageUpload('mainImage', $productId, 'main');
        }

        // 處理畫廊圖片上傳 - 這部分需要更完善的邏輯來決定是新增、替換還是刪除舊圖片
        // 簡化處理：如果上傳了新的畫廊圖片，先刪除舊的，再新增新的
        if (isset($files['galleryImages']) && !empty($files['galleryImages']['name'][0])) {
            // 刪除舊的畫廊圖片
            $oldGalleryImages = glob(__DIR__ . "/../imgs/products/{$productId}/gallery-*.{jpg,jpeg,png,gif}", GLOB_BRACE);
            foreach ($oldGalleryImages as $oldImage) {
                unlink($oldImage);
            }
            // 上傳新的畫廊圖片
            $galleryImages = $files['galleryImages'];
            $galleryOrders = $data['galleryOrders'] ?? [];
            if (is_array($galleryImages['name'])) {
                for ($i = 0; $i < count($galleryImages['name']); $i++) {
                    $_FILES['currentGalleryImage'] = [
                        'name' => $galleryImages['name'][$i],
                        'type' => $galleryImages['type'][$i],
                        'tmp_name' => $galleryImages['tmp_name'][$i],
                        'error' => $galleryImages['error'][$i],
                        'size' => $galleryImages['size'][$i]
                    ];
                    $order = isset($galleryOrders[$i]) ? (int) $galleryOrders[$i] : ($i + 1);
                    handleImageUpload('currentGalleryImage', $productId, 'gallery', $order);
                }
            }
        }

        // 更新商品選項：先刪除舊的，再新增新的
        $stmtDelOpt = $pdo->prepare("DELETE FROM Options WHERE ProductID = :pid");
        $stmtDelOpt->execute([':pid' => $productId]);

        if (isset($data['sizes'])) {
            $options = json_decode($data['sizes'], true);
            if (is_array($options)) {
                // 手動獲取起始 OptionID
                $stmtMaxOptId = $pdo->query("SELECT MAX(OptionID) as max_opt_id FROM Options");
                $maxOptIdRow = $stmtMaxOptId->fetch(PDO::FETCH_ASSOC);
                $currentOptionId = ($maxOptIdRow && $maxOptIdRow['max_opt_id'] !== null) ? (int) $maxOptIdRow['max_opt_id'] + 1 : 1;

                $stmtOpt = $pdo->prepare("INSERT INTO Options (OptionID, ProductID, Size, SizeDescription, Price, Color, Stock) VALUES (:optid, :pid, :size, :desc, :price, :color, :stock)");
                foreach ($options as $opt) {
                    $stmtOpt->execute([
                        ':optid' => $currentOptionId,
                        ':pid' => $productId,
                        ':size' => $opt['Size'],
                        ':desc' => $opt['SizeDescription'],
                        ':price' => $opt['Price'],
                        ':color' => $opt['Color'],
                        ':stock' => $opt['Stock']
                    ]);
                    $currentOptionId++; // 為下一個選項遞增ID
                }
            }
        }

        $pdo->commit();
        return ['success' => true, 'message' => '商品更新成功'];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Update product error: " . $e->getMessage());
        return ['success' => false, 'message' => '更新商品失敗: ' . $e->getMessage()];
    }
}

// 刪除商品
function deleteProduct($productId)
{
    $pdo = getDBConnection();
    try {
        $pdo->beginTransaction();

        // 刪除商品選項
        $stmtOpt = $pdo->prepare("DELETE FROM Options WHERE ProductID = :pid");
        $stmtOpt->execute([':pid' => $productId]);

        // 刪除商品
        $stmtProd = $pdo->prepare("DELETE FROM Product WHERE ProductID = :pid");
        $stmtProd->execute([':pid' => $productId]);

        // 刪除商品圖片目錄 (可選)
        $productImageDir = __DIR__ . "/../imgs/products/{$productId}";
        if (is_dir($productImageDir)) {
            // 簡單遞迴刪除目錄函數
            function deleteDir($dirPath)
            {
                if (!is_dir($dirPath)) {
                    throw new InvalidArgumentException("$dirPath must be a directory");
                }
                if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                    $dirPath .= '/';
                }
                $files = glob($dirPath . '*', GLOB_MARK);
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
                rmdir($dirPath);
            }
            try {
                deleteDir($productImageDir);
            } catch (Exception $e) {
                error_log("Error deleting product image directory {$productImageDir}: " . $e->getMessage());
                // 目錄刪除失敗不應阻止交易提交，但應記錄錯誤
            }
        }

        $pdo->commit();
        return ['success' => true, 'message' => '商品刪除成功'];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Delete product error: " . $e->getMessage());
        return ['success' => false, 'message' => '刪除商品失敗: ' . $e->getMessage()];
    }
}

// API 端點處理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'message' => '未指定操作']);
        exit;
    }
    $action = $_POST['action'];
    switch ($action) {
        case 'addProduct':
            // $_POST 包含表單欄位, $_FILES 包含上傳的檔案
            $result = addProduct($_POST, $_FILES);
            echo json_encode($result);
            break;
        case 'updateProduct':
            $result = updateProduct($_POST, $_FILES);
            echo json_encode($result);
            break;
        case 'deleteProduct': // 確保 action 名稱與前端一致
            if (!isset($_POST['productId'])) {
                echo json_encode(['success' => false, 'message' => '缺少商品ID']);
                exit;
            }
            $result = deleteProduct((int) $_POST['productId']);
            echo json_encode($result);
            break;
        default:
            echo json_encode(['success' => false, 'message' => '無效的POST操作']);
    }
    exit;
} elseif (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'getProduct':
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'message' => '缺少商品ID']);
                exit;
            }
            $productId = (int) $_GET['id'];
            $product = getProductById($productId);
            if ($product) {
                echo json_encode(['success' => true, 'product' => $product]);
            } else {
                echo json_encode(['success' => false, 'message' => '找不到商品']);
            }
            break;

        // GET 請求的 updateProduct 和 delete 保持原樣或移除，因為我們現在用 POST
        // case 'updateProduct': ... (此處的 GET 請求邏輯可以考慮移除或修改)
        // case 'delete': ... (此處的 GET 請求邏輯可以考慮移除或修改)


        case 'getProducts':
            $type = $_GET['type'] ?? '';
            $products = getProductsByType($type);
            echo json_encode(['success' => true, 'data' => $products]);
            break;

        case 'getProductById':
            if (!isset($_GET['product_id'])) {
                echo json_encode(['success' => false, 'message' => 'Missing product_id parameter']);
                exit;
            }
            $product = getProductById($_GET['product_id']);
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }
            echo json_encode([
                'success' => true,
                'data' => $product
            ]);
            break;

        case 'renderProductCard':
            if (!isset($_GET['productId'])) {
                echo json_encode(['success' => false, 'message' => 'Missing productId parameter']);
                exit;
            }
            $product = getProductById($_GET['productId']);
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit;
            }
            echo json_encode([
                'success' => true,
                'html' => renderProductCard($product)
            ]);
            break;

        case 'filter':
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $type = isset($_GET['type']) ? trim($_GET['type']) : '';
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';

            try {
                $result = getAllProducts($page, 10, $type, $status, $search);
                echo json_encode([
                    'success' => true,
                    'products' => $result['products'],
                    'totalPages' => $result['totalPages'],
                    'currentPage' => $result['currentPage']
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>