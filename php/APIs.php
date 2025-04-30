<?php
require_once __DIR__ . '/db_connect.php';

function getDbConnection(&$conn, &$closeConn)
{
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }
}
//獲取會員
function getMembers($conn = null)
{
    getDbConnection($conn, $closeConn);

    $sql = "SELECT 
                m.MemberID,
                m.Name as membername,
                m.Phone as phonenum,
                m.Email as memberemail,
                m.City,
                m.Address,
                COUNT(DISTINCT o.OrderID) as order_count,
                CASE 
                    WHEN m.IsAdmin = 1 THEN '管理員'
                    ELSE '會員'
                END as role
            FROM Member m
            LEFT JOIN Orders o ON m.MemberID = o.MembersID
            GROUP BY m.MemberID, m.Name, m.Phone, m.Email, m.City, m.Address, m.IsAdmin
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

function getMember($memberId, $conn = null)
{
    getDbConnection($conn, $closeConn);

    $stmt = $conn->prepare("SELECT MemberID, Name as membername, Phone as phonenum, Email as memberemail, City, Address, IsAdmin FROM Member WHERE MemberID = ?");
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        if ($closeConn) {
            $conn->close();
        }
        return null;
    }

    $member = $result->fetch_assoc();

    if ($closeConn) {
        $conn->close();
    }
    return $member;
}
//增加商品
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
        handleProductImages($productId, $mainImage, $galleryImages);

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
//獲取所有產品
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
//獲取單個產品
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
    $productDir = __DIR__ . "/../imgs/products/" . $product['ProductID'];
    $webPath = "imgs/products/" . $product['ProductID'];

    $product['mainImage'] = $webPath . "/main.jpg";

    $product['galleryImages'] = [];
    $i = 1;
    while (file_exists($productDir . "/gallery-" . $i . ".jpg")) {
        $product['galleryImages'][] = [
            'url' => $webPath . "/gallery-" . $i . ".jpg",
            'description' => "Gallery image $i for product " . $product['ProductName']
        ];
        $i++;
    }

    $conn->close();
    return $product;
}
function handleProductImages($productId, $mainImage, $galleryImages)
{
    $productDir = __DIR__ . "/../imgs/products/" . $productId;

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
function getOrders($conn = null)
{
    getDbConnection($conn, $closeConn);

    $sql = "SELECT 
                o.OrderID,
                m.Name as customer_name,
                SUM(oi.Quantity * opt.Price) as total_amount,
                o.Status,
                o.OrderDate,
                m.MemberID
            FROM Orders o
            JOIN Member m ON o.MembersID = m.MemberID
            JOIN OrderItem oi ON o.OrderID = oi.OrderID
            JOIN Options opt ON oi.OptionID = opt.OptionID
            GROUP BY o.OrderID, m.Name, o.Status, o.OrderDate, m.MemberID
            ORDER BY o.OrderDate DESC";

    $result = $conn->query($sql);

    if (!$result) {
        error_log("訂單查詢失敗：" . $conn->error);
        if ($closeConn) {
            $conn->close();
        }
        return [];
    }

    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }

    if ($closeConn) {
        $conn->close();
    }
    return $orders;
}
function getCartItems($memberId)
{
    $conn = db_connect();

    $stmt = $conn->prepare("
        SELECT 
            ci.Quantity,
            ci.OptionID,
            p.ProductName,
            p.ProductID,
            o.Price,
            o.Color,
            o.SizeDescription,
            o.Size
        FROM CartItem ci
        JOIN Options o ON ci.OptionID = o.OptionID
        JOIN Product p ON o.ProductID = p.ProductID
        WHERE ci.MemberID = ?
    ");
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    // 获取商品图片
    foreach ($items as &$item) {
        $productDir = "imgs/products/" . $item['ProductID'];
        $item['image'] = $productDir . "/main.jpg";
    }

    return $items;
}
function registerUser($name, $phone, $email, $password, $city = null, $address = null)
{
    $conn = db_connect();
    $conn->begin_transaction();

    try {
        // 哈希處理密碼
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 检查手机号是否已存在
        $stmt = $conn->prepare("SELECT MemberID FROM Member WHERE Phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => '該手機號碼已被使用'];
        }

        // 检查邮箱是否已存在
        $stmt = $conn->prepare("SELECT MemberID FROM Member WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => '電子郵件已被使用'];
        }

        // 获取新会员ID
        $result = $conn->query("SELECT MAX(MemberID) AS max_id FROM Member");
        $row = $result->fetch_assoc();
        $memberId = $row['max_id'] + 1;

        // 插入会员数据
        // 修改插入語句使用哈希後的密碼
        $stmt = $conn->prepare("INSERT INTO Member (MemberID, Name, Phone, Email, Password, City, Address, IsAdmin) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("issssss", $memberId, $name, $phone, $email, $hashedPassword, $city, $address);
        $stmt->execute();

        // 创建购物车
        $stmt = $conn->prepare("INSERT INTO Cart (MemberID) VALUES (?)");
        $stmt->bind_param("i", $memberId);
        $stmt->execute();

        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("註冊失敗：" . $e->getMessage());
        return ['success' => false, 'message' => '註冊失敗，請稍後再試'];
    } finally {
        $conn->close();
    }
}
function authenticateUser($phone, $password)
{
    $conn = db_connect();

    $stmt = $conn->prepare("SELECT MemberID, Name, Phone, Email, Password, IsAdmin FROM Member WHERE Phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // 驗證密碼哈希
        if (password_verify($password, $user['Password'])) {
            $conn->close();
            return [
                'success' => true,
                'user' => [
                    'id' => $user['MemberID'],
                    'name' => $user['Name'],
                    'phone' => $user['Phone'],
                    'email' => $user['Email'],
                    'role' => $user['IsAdmin'] ? 'admin' : 'member'
                ]
            ];
        }
    }

    $conn->close();
    return ['success' => false];
}
function addToCart($memberId, $optionId, $quantity, $conn = null)
{
    getDbConnection($conn, $closeConn);

    $conn->begin_transaction();
    try {
        // 檢查是否已有相同商品在購物車
        $stmt = $conn->prepare("SELECT Quantity FROM CartItem WHERE MemberID = ? AND OptionID = ?");
        $stmt->bind_param("ii", $memberId, $optionId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // 更新數量
            $row = $result->fetch_assoc();
            $newQuantity = $row['Quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE CartItem SET Quantity = ? WHERE MemberID = ? AND OptionID = ?");
            $stmt->bind_param("iii", $newQuantity, $memberId, $optionId);
        } else {
            // 新增商品
            $stmt = $conn->prepare("INSERT INTO CartItem (MemberID, OptionID, Quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $memberId, $optionId, $quantity);
        }
        $stmt->execute();

        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("加入購物車失敗：" . $e->getMessage());
        return ['success' => false, 'message' => '加入購物車失敗'];
    } finally {
        if ($closeConn) {
            $conn->close();
        }
    }
}
function removeFromCart($memberId, $optionId, $conn = null)
{
    getDbConnection($conn, $closeConn);

    try {
        $stmt = $conn->prepare("DELETE FROM CartItem WHERE MemberID = ? AND OptionID = ?");
        $stmt->bind_param("ii", $memberId, $optionId);
        $stmt->execute();
        return ['success' => true];
    } catch (Exception $e) {
        error_log("移除購物車商品失敗：" . $e->getMessage());
        return ['success' => false, 'message' => '移除商品失敗'];
    } finally {
        if ($closeConn) {
            $conn->close();
        }
    }
}
function updateCartQuantity($memberId, $optionId, $quantity, $conn = null)
{
    getDbConnection($conn, $closeConn);

    try {
        $stmt = $conn->prepare("UPDATE CartItem SET Quantity = ? WHERE MemberID = ? AND OptionID = ?");
        $stmt->bind_param("iii", $quantity, $memberId, $optionId);
        $stmt->execute();

        return ['success' => true];
    } catch (Exception $e) {
        error_log("更新購物車數量失敗：" . $e->getMessage());
        return ['success' => false, 'message' => '更新數量失敗'];
    } finally {
        if ($closeConn) {
            $conn->close();
        }
    }
}
function createOrder($memberId, $conn = null)
{
    getDbConnection($conn, $closeConn);

    $conn->begin_transaction();
    try {
        // 获取新订单ID
        $result = $conn->query("SELECT MAX(OrderID) AS max_id FROM Orders");
        $row = $result->fetch_assoc();
        $orderId = $row['max_id'] + 1;

        // 创建订单
        $stmt = $conn->prepare("INSERT INTO Orders (OrderID, MembersID, OrderDate, Status) VALUES (?, ?, CURDATE(), 'Pending')");
        $stmt->bind_param("ii", $orderId, $memberId);
        $stmt->execute();

        $conn->commit();
        return $orderId;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("创建订单失败: " . $e->getMessage());
        return false;
    } finally {
        if ($closeConn) {
            $conn->close();
        }
    }
}
function addOrderItem($orderId, $optionId, $quantity, $conn = null)
{
    getDbConnection($conn, $closeConn);

    try {
        // 检查库存
        $stmt = $conn->prepare("SELECT Stock FROM Options WHERE OptionID = ? FOR UPDATE");
        $stmt->bind_param("i", $optionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['Stock'] < $quantity) {
            throw new Exception("商品庫存不足");
        }

        // 更新库存
        $stmt = $conn->prepare("UPDATE Options SET Stock = Stock - ? WHERE OptionID = ?");
        $stmt->bind_param("ii", $quantity, $optionId);
        $stmt->execute();

        // 添加訂單項目
        $stmt = $conn->prepare("INSERT INTO OrderItem (OrderID, OptionID, Quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $orderId, $optionId, $quantity);
        $stmt->execute();

        return true;
    } catch (Exception $e) {
        error_log("添加訂單項目失敗: " . $e->getMessage());
        return false;
    } finally {
        if ($closeConn) {
            $conn->close();
        }
    }
}
function checkout($memberId)
{
    $conn = db_connect();
    $conn->begin_transaction();

    try {
        // 获取购物车商品
        $cartItems = getCartItems($memberId);
        if (empty($cartItems)) {
            return ['success' => false, 'message' => '購物車為空'];
        }

        // 检查所有商品库存
        foreach ($cartItems as $item) {
            $stmt = $conn->prepare("SELECT 
                o.Stock, 
                p.ProductName,
                o.Color,
                o.SizeDescription
                FROM Options o
                JOIN Product p ON o.ProductID = p.ProductID
                WHERE o.OptionID = ? FOR UPDATE");
            $stmt->bind_param("i", $item['OptionID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['Stock'] < $item['Quantity']) {
                $optionName = $row['ProductName'] . ' - ' . $row['Color'] . ' (' . $row['SizeDescription'] . ')';
                throw new Exception("商品 [{$optionName}] 庫存不足");
            }
        }

        // 创建订单
        $orderId = createOrder($memberId, $conn);
        if (!$orderId) {
            throw new Exception("無法創建訂單");
        }

        // 添加订单项并更新库存
        foreach ($cartItems as $item) {
            // 更新庫存
            $stmt = $conn->prepare("UPDATE Options SET Stock = Stock - ? WHERE OptionID = ?");
            $stmt->bind_param("ii", $item['Quantity'], $item['OptionID']);
            $stmt->execute();

            // 添加訂單項目
            $stmt = $conn->prepare("INSERT INTO OrderItem (OrderID, OptionID, Quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $orderId, $item['OptionID'], $item['Quantity']);
            $stmt->execute();
        }

        // 清空购物车
        $stmt = $conn->prepare("DELETE FROM CartItem WHERE MemberID = ?");
        $stmt->bind_param("i", $memberId);
        $stmt->execute();

        $conn->commit();
        return ['success' => true, 'orderId' => $orderId];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("結帳失敗: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '結帳失敗: ' . $e->getMessage(),
            'details' => $cartItems // 返回完整的購物車商品資訊
        ];
    } finally {
        $conn->close();
    }
}
function getMemberOrders($memberId, $conn = null)
{
    getDbConnection($conn, $closeConn);

    $sql = "SELECT 
                o.OrderID,
                o.OrderDate,
                o.Status,
                SUM(oi.Quantity * opt.Price) as TotalAmount
            FROM Orders o
            JOIN OrderItem oi ON o.OrderID = oi.OrderID
            JOIN Options opt ON oi.OptionID = opt.OptionID
            WHERE o.MembersID = ?
            GROUP BY o.OrderID, o.OrderDate, o.Status
            ORDER BY o.OrderDate DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }

    if ($closeConn) {
        $conn->close();
    }
    return $orders;
}
function getOrderDetails($orderId, $conn = null)
{
    getDbConnection($conn, $closeConn);

    $sql = "SELECT 
                p.ProductName,
                p.ProductID,
                opt.Color,
                opt.Size,
                opt.SizeDescription,
                opt.Price,
                oi.Quantity,
                (opt.Price * oi.Quantity) as Subtotal
            FROM OrderItem oi
            JOIN Options opt ON oi.OptionID = opt.OptionID
            JOIN Product p ON opt.ProductID = p.ProductID
            WHERE oi.OrderID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        // 獲取商品圖片
        $productDir = "imgs/products/" . $row['ProductID'];
        $row['image'] = $productDir . "/main.jpg";
        $items[] = $row;
    }

    // 獲取訂單狀態
    $stmt = $conn->prepare("SELECT Status FROM Orders WHERE OrderID = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $statusResult = $stmt->get_result();
    $statusRow = $statusResult->fetch_assoc();
    $status = $statusRow['Status'];

    // 狀態類別和文本
    $statusClass = match ($status) {
        'Completed' => 'bg-success',
        'Pending' => 'bg-info text-dark',
        'Cancel' => 'bg-warning',
        'abnormal' => 'bg-danger',
        default => 'bg-secondary'
    };
    $statusText = match ($status) {
        'Completed' => '已完成',
        'Pending' => '處理中',
        'Cancel' => '已取消',
        'abnormal' => '異常',
        default => $status
    };

    if ($closeConn) {
        $conn->close();
    }

    return [
        'items' => $items,
        'total_amount' => array_sum(array_column($items, 'Subtotal')),
        'status' => $status,
        'statusClass' => $statusClass,
        'statusText' => $statusText
    ];
}
function renderOrderTab($orders, $status, $title)
{
    // 修改過濾邏輯：進行中訂單顯示 Pending 和 Shipping 狀態，已完成訂單顯示其他狀態
    if ($status === 'Pending') {
        $filteredOrders = array_filter($orders, fn($order) => in_array($order['Status'], ['Pending', 'Shipping']));
    } else {
        $filteredOrders = array_filter($orders, fn($order) => !in_array($order['Status'], ['Pending', 'Shipping']));
    }

    $statusClasses = [
        'Pending' => 'bg-info',
        'Shipping' => 'bg-primary',
        'Completed' => 'bg-success',
        'Cancel' => 'bg-warning',
        'abnormal' => 'bg-danger'
    ];

    $statusTexts = [
        'Pending' => '處理中',
        'Shipping' => '運送中',
        'Completed' => '已完成',
        'Cancel' => '已取消',
        'abnormal' => '異常'
    ];
    ?>
    <div class="tab-pane fade <?= $status === 'Pending' ? 'show active' : '' ?>" id="<?= strtolower($status) ?>"
        role="tabpanel" aria-labelledby="<?= strtolower($status) ?>-tab">
        <?php if (!empty($filteredOrders)): ?>
            <div class="list-group">
                <?php foreach ($filteredOrders as $order):
                    $orderDetails = getOrderDetails($order['OrderID']);
                    ?>
                    <div class="list-group-item mb-4 border rounded">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">訂單號碼: ORD-<?= $order['OrderID'] ?></h5>
                            <small><?= $order['OrderDate'] ?></small>
                        </div>
                        <?php foreach ($orderDetails['items'] as $item): ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-3" style="width: 60px; height: 60px;">
                                    <img src="<?= $item['image'] ?>" alt="<?= $item['ProductName'] ?>" class="img-fluid"
                                        style="max-height: 100%;">
                                </div>
                                <div>
                                    <p class="mb-1"><?= $item['ProductName'] ?></p>
                                    <p class="mb-0 text-secondary">規格: <?= $item['Color'] ?>, <?= $item['SizeDescription'] ?></p>
                                    <p class="mb-0">數量: <?= $item['Quantity'] ?> 件</p>
                                    <p class="mb-0">單價: <span class="text-danger fw-bold">$<?= $item['Price'] ?></span></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <div>
                                <span class="badge fs-6 <?= $statusClasses[$order['Status']] ?? 'bg-secondary' ?>">
                                    <?= $statusTexts[$order['Status']] ?? $order['Status'] ?>
                                </span>
                            </div>
                            <div class="text-end">
                                <p class="mb-0">訂單金額: <span class="text-danger fw-bold">$<?= $order['TotalAmount'] ?></span></p>
                            </div>
                            <div>
                                <?php if ($status === 'Pending'): ?>
                                    <button class="btn btn-success payment-btn" data-order-id="<?= $order['OrderID'] ?>"
                                        data-order-amount="<?= number_format($order['TotalAmount']) ?>">
                                        付款
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-outline-secondary reorder-btn"
                                    data-order-id="<?= $order['OrderID'] ?>">再買一次</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="card-text mt-3">目前沒有<?= $title ?>的訂單。</p>
        <?php endif; ?>
    </div>
    <?php
}
function reorder($memberId, $orderId, $conn = null)
{
    getDbConnection($conn, $closeConn);

    $conn->begin_transaction();
    try {
        // 先清空購物車
        $stmt = $conn->prepare("DELETE FROM CartItem WHERE MemberID = ?");
        $stmt->bind_param("i", $memberId);
        $stmt->execute();

        // 獲取訂單商品
        $stmt = $conn->prepare("SELECT OptionID, Quantity FROM OrderItem WHERE OrderID = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        // 將商品加入購物車
        while ($row = $result->fetch_assoc()) {
            $addResult = addToCart($memberId, $row['OptionID'], $row['Quantity'], $conn);
            if (!$addResult['success']) {
                throw new Exception("無法將商品加入購物車");
            }
        }

        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("再買一次失敗：" . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    } finally {
        if ($closeConn) {
            $conn->close();
        }
    }
}
function getCarouselImages()
{
    $carouselDir = realpath(__DIR__ . '/../imgs/carousel/');
    if (!$carouselDir) {
        return [];
    }
    $images = glob($carouselDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    return array_map('basename', $images);
}
function addCarouselImage($imageFile)
{
    $targetDir = realpath(__DIR__ . '/../imgs/carousel/');
    if (!$targetDir) {
        return false;
    }

    $targetFile = $targetDir . '/' . basename($imageFile['name']);
    return move_uploaded_file($imageFile['tmp_name'], $targetFile);
}
function deleteCarouselImage($imageName)
{
    $targetDir = realpath(__DIR__ . '/../imgs/carousel/');
    if (!$targetDir) {
        error_log("Carousel directory not found");
        return false;
    }

    $targetFile = $targetDir . '/' . basename($imageName);
    if (!file_exists($targetFile)) {
        error_log("File not found: $targetFile");
        return false;
    }

    if (!is_writable($targetFile)) {
        error_log("Permission denied for file: $targetFile");
        return false;
    }

    return unlink($targetFile);
}
function updateOrderStatus($orderId, $newStatus, $memberId)
{
    $conn = db_connect();
    $conn->begin_transaction();

    try {
        // 首先验证订单是否存在
        $stmt = $conn->prepare("SELECT Status FROM Orders WHERE OrderID = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => '訂單不存在'];
        }

        // 更新订单状态
        $stmt = $conn->prepare("UPDATE Orders SET Status = ? WHERE OrderID = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
        $stmt->execute();

        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("更新訂單狀態失敗: " . $e->getMessage());
        return ['success' => false, 'message' => '更新訂單狀態失敗'];
    } finally {
        $conn->close();
    }
}
function generateReceipt($orderId)
{
    // 获取订单详情
    $orderDetails = getOrderDetails($orderId);

    // 生成收据HTML或PDF
    $receipt = '<div class="receipt">
                  <h2>收据 #ORD-' . $orderId . '</h2>
                  <p>日期: ' . date('Y-m-d H:i:s') . '</p>
                  // ... 其他收据内容 ...
                </div>';

    // 保存或发送收据
    file_put_contents(__DIR__ . '/receipts/ORD-' . $orderId . '.html', $receipt);
    return $receipt;
}
function updateMember($memberId, $name, $phone, $email, $city, $address, $isAdmin = null)
{
    $conn = db_connect();
    $conn->begin_transaction();

    try {
        // 檢查手機號碼是否已被其他會員使用
        $stmt = $conn->prepare("SELECT MemberID FROM Member WHERE Phone = ? AND MemberID != ?");
        $stmt->bind_param("si", $phone, $memberId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("該手機號碼已被其他會員使用");
        }

        // 檢查電子郵件是否已被其他會員使用
        $stmt = $conn->prepare("SELECT MemberID FROM Member WHERE Email = ? AND MemberID != ?");
        $stmt->bind_param("si", $email, $memberId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("該電子郵件已被其他會員使用");
        }

        // 準備SQL語句
        $sql = "UPDATE Member SET 
                Name = ?, 
                Phone = ?, 
                Email = ?, 
                City = ?, 
                Address = ?";

        $params = [$name, $phone, $email, $city, $address];
        $types = "sssss";

        // 如果是管理員且要修改身份
        if ($isAdmin !== null && $_SESSION['role'] === 'admin') {
            $sql .= ", IsAdmin = ?";
            $params[] = $isAdmin;
            $types .= "i";
        }

        $sql .= " WHERE MemberID = ?";
        $params[] = $memberId;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        // 檢查是否有資料被更新或只有身份變化
        if ($stmt->affected_rows === 0 && $isAdmin !== null) {
            // 檢查當前會員身份是否與更新值不同
            $checkStmt = $conn->prepare("SELECT IsAdmin FROM Member WHERE MemberID = ?");
            $checkStmt->bind_param("i", $memberId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $currentAdmin = $checkResult->fetch_assoc()['IsAdmin'];
                if ($currentAdmin != $isAdmin) {
                    // 只有身份變化，強制更新
                    $updateStmt = $conn->prepare("UPDATE Member SET IsAdmin = ? WHERE MemberID = ?");
                    $updateStmt->bind_param("ii", $isAdmin, $memberId);
                    $updateStmt->execute();

                    if ($updateStmt->affected_rows > 0) {
                        $conn->commit();
                        return ['success' => true];
                    }
                }
            }

        }

        $conn->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("更新會員資料失敗：" . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    } finally {
        $conn->close();
    }
}
// 处理AJAX请求
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    session_start();

    switch ($_GET['action']) {
        case 'addToCart':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode(addToCart($data['memberId'], $data['optionId'], $data['quantity']));
            break;
        case 'removeFromCart':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode(removeFromCart($data['memberId'], $data['optionId']));
            break;
        case 'updateCartQuantity':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode(updateCartQuantity($data['memberId'], $data['optionId'], $data['quantity']));
            break;
        case 'reorder':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode(reorder($data['memberId'], $data['orderId']));
            break;
        case 'checkout':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode(checkout($data['memberId']));
            break;
        case 'updateOrderStatus':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode(updateOrderStatus($data['order_id'], $data['new_status'], $data['memberId']));
            break;
        case 'getOrderDetails':
            if (!isset($_GET['order_id'])) {
                echo json_encode(['success' => false, 'message' => '缺少訂單ID']);
                break;
            }
            $orderId = (int) $_GET['order_id'];
            $orderDetails = getOrderDetails($orderId);
            if ($orderDetails) {
                echo json_encode([
                    'success' => true,
                    'items' => $orderDetails['items'],
                    'status' => $orderDetails['status'],
                    'statusClass' => $orderDetails['statusClass'],
                    'statusText' => $orderDetails['statusText'],
                    'total_amount' => $orderDetails['total_amount']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => '找不到訂單詳情']);
            }
            break;
        case 'updateMember':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = updateMember(
                $data['memberId'],
                $data['name'],
                $data['phone'],
                $data['email'],
                $data['city'],
                $data['address'],
                $data['isAdmin'] ?? null // ✅ 補上這一行
            );
            echo json_encode($result);
            break;
        case 'getMember':
            if (isset($_GET['memberId'])) {
                $memberId = (int) $_GET['memberId'];
                $member = getMember($memberId);
                if ($member) {
                    header('Content-Type: application/json');
                    echo json_encode($member);
                } else {
                    header('HTTP/1.1 404 Not Found');
                    echo json_encode(['error' => true, 'message' => 'Member not found']);
                }
            }
            break;
        case 'getMembers':
            $members = getMembers();
            echo json_encode($members);
            break;
        default:
            echo json_encode(['success' => false, 'message' => '無效的操作']);
    }
    exit;
}
?>