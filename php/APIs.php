<?php
// 確保引用此檔案的程式碼使用正確的路徑，例如：require_once 'php/getMember.php';
require_once __DIR__ . '/db_connect.php';

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
    $productDir = __DIR__ . "/../imgs/products/" . $product['ProductName'];
    $webPath = "/public_html/imgs/products/" . $product['ProductName'];

    $product['mainImage'] = file_exists($productDir . "/main.jpg") ? $webPath . "/main.jpg" : "https://via.placeholder.com/150";

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

function handleProductImages($productName, $mainImage, $galleryImages)
{
    $productDir = $productDir = __DIR__ . "/../imgs/products/" . $productName;

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

function validateProductInput($input)
{
    if (empty($input['productName']) || empty($input['productType'])) {
        throw new Exception('缺少必要欄位');
    }

    if (empty($input['colors']) || empty($input['stocks']) || empty($input['prices'])) {
        throw new Exception('缺少商品規格資訊');
    }
}

function getOrders($conn = null)
{
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

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

function getCart($memberId, $conn = null)
{
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

    $sql = "SELECT * FROM Cart WHERE MemberID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cart = null;
    if ($result->num_rows > 0) {
        $cart = $result->fetch_assoc();
    }

    if ($closeConn) {
        $conn->close();
    }
    return $cart;
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
        $productDir = "/public_html/imgs/products/" . $item['ProductName'];
        $item['image'] = file_exists($_SERVER['DOCUMENT_ROOT'] . $productDir . "/main.jpg")
            ? $productDir . "/main.jpg"
            : "https://via.placeholder.com/50";
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

// 處理AJAX請求
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
        default:
            echo json_encode(['success' => false, 'message' => '無效的操作']);
    }
    exit;
}

function addToCart($memberId, $optionId, $quantity, $conn = null)
{
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

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
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

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
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

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
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

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
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO OrderItem (OrderID, OptionID, Quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $orderId, $optionId, $quantity);
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        error_log("添加订单项失败: " . $e->getMessage());
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
            return ['success' => false, 'message' => '购物车为空'];
        }

        // 创建订单
        $orderId = createOrder($memberId, $conn);
        if (!$orderId) {
            throw new Exception("无法创建订单");
        }

        // 添加订单项
        foreach ($cartItems as $item) {
            if (!addOrderItem($orderId, $item['OptionID'], $item['Quantity'], $conn)) {
                throw new Exception("添加订单项失败");
            }
        }

        // 清空购物车
        $stmt = $conn->prepare("DELETE FROM CartItem WHERE MemberID = ?");
        $stmt->bind_param("i", $memberId);
        $stmt->execute();

        $conn->commit();
        return ['success' => true, 'orderId' => $orderId];
    } catch (Exception $e) {
        $conn->rollback();
        error_log("结账失败: " . $e->getMessage());
        return ['success' => false, 'message' => '结账失败: ' . $e->getMessage()];
    } finally {
        $conn->close();
    }
}

// 处理AJAX请求部分添加结账处理
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
        case 'checkout':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode(checkout($data['memberId']));
            break;
        default:
            echo json_encode(['success' => false, 'message' => '无有效操作']);
    }
    exit;
}
function getMemberOrders($memberId, $conn = null)
{
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

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
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

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
        $productDir = "/public_html/imgs/products/" . $row['ProductName'];
        $row['image'] = file_exists($_SERVER['DOCUMENT_ROOT'] . $productDir . "/main.jpg")
            ? $productDir . "/main.jpg"
            : "https://via.placeholder.com/150";
        $items[] = $row;
    }

    if ($closeConn) {
        $conn->close();
    }
    return $items;
}

function renderOrderTab($orders, $status, $title)
{
    $filteredOrders = array_filter($orders, fn($order) => $order['Status'] === $status);
    ?>
    <div class="tab-pane fade <?php echo $status === 'Pending' ? 'show active' : ''; ?>"
        id="<?php echo strtolower($status); ?>" role="tabpanel" aria-labelledby="<?php echo strtolower($status); ?>-tab">
        <?php if (!empty($filteredOrders)): ?>
            <div class="list-group">
                <?php foreach ($filteredOrders as $order): ?>
                    <div class="mb-4 border rounded">
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">訂單號碼: ORD-<?php echo $order['OrderID']; ?></h5>
                                <small><?php echo $order['OrderDate']; ?></small>
                            </div>
                            <?php foreach (getOrderDetails($order['OrderID']) as $item): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3" style="width: 60px; height: 60px;">
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['ProductName']; ?>"
                                            class="img-fluid" style="max-height: 100%;">
                                    </div>
                                    <div>
                                        <p class="mb-1"><?php echo $item['ProductName']; ?></p>
                                        <p class="mb-0 text-secondary">規格:
                                            <?php echo $item['Color'] . ', ' . $item['SizeDescription']; ?>
                                        </p>
                                        <p class="mb-0">數量: <?php echo $item['Quantity']; ?> 件</p>
                                        <p class="mb-0">價格: <span class="text-danger fw-bold">$<?php echo $item['Price']; ?></span>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <div><span class="badge fs-6
                                    <?php
                                    switch ($order['Status']) {
                                        case 'Pending':
                                            echo 'bg-info';
                                            $statusText = '處理中';
                                            break;
                                        case 'Completed':
                                            echo 'bg-success';
                                            $statusText = '已完成';
                                            break;
                                        case 'Cancel':
                                            echo 'bg-warning';
                                            $statusText = '已取消';
                                            break;
                                        default:
                                            echo 'bg-secondary';
                                            $statusText = $order['Status'];
                                    }
                                    ?>">
                                        <?php echo $statusText; ?>
                                    </span></div>
                                <div class="text-end">
                                    <p class="mb-0">訂單金額: <span
                                            class="text-danger fw-bold">$<?php echo $order['TotalAmount']; ?></span>
                                    </p>
                                </div>
                                <button class="btn btn-outline-secondary reorder-btn"
                                    data-order-id="<?php echo $order['OrderID']; ?>">再買一次</button>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="card-text mt-3">目前沒有<?php echo $title; ?>的訂單。</p>
        <?php endif; ?>
    </div>
    <?php
}

function reorder($memberId, $orderId, $conn = null)
{
    $closeConn = false;
    if ($conn === null) {
        $conn = db_connect();
        $closeConn = true;
    }

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
?>