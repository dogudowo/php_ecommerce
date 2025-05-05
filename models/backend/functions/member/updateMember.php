<?php
require_once __DIR__ . '/memberFunction.php';

header('Content-Type: application/json');

// 檢查請求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不支援的請求方法']);
    exit;
}

// 獲取POST數據
$data = json_decode(file_get_contents('php://input'), true);

// 檢查必要參數
if (!isset($data['memberId']) || empty($data['memberId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '缺少會員ID']);
    exit;
}

// 驗證並準備數據
$memberId = intval($data['memberId']);
$name = isset($data['name']) ? trim($data['name']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$city = isset($data['city']) ? trim($data['city']) : '';
$address = isset($data['address']) ? trim($data['address']) : '';
$isAdmin = isset($data['isAdmin']) ? intval($data['isAdmin']) : 0;

// 基本驗證
if (empty($name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '會員姓名不能為空']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '無效的電子郵件地址']);
    exit;
}

try {
    // 連接數據庫
    $db = getDBConnection();

    // 檢查會員是否存在
    $stmt = $db->prepare('SELECT MemberID FROM Member WHERE MemberID = ?');
    $stmt->execute([$memberId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '找不到會員資料']);
        exit;
    }

    // 更新會員資料
    $stmt = $db->prepare('UPDATE Member SET Name = ?, Email = ?, Phone = ?, City = ?, Address = ?, IsAdmin = ? WHERE MemberID = ?');
    $result = $stmt->execute([$name, $email, $phone, $city, $address, $isAdmin, $memberId]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => '會員資料已成功更新']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => '更新會員資料失敗']);
    }
} catch (PDOException $e) {
    // 記錄錯誤
    error_log('updateMember.php', $e->getMessage());

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '系統錯誤，請稍後再試']);
}