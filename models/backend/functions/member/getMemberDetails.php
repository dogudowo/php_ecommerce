<?php
require_once __DIR__ . '/../../../../api/error_handler.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../api/db.php';

$response = ['success' => false, 'member' => null, 'message' => ''];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response['message'] = '未提供會員ID。';
    echo json_encode($response);
    exit;
}

$memberId = $_GET['id'];

try {
    $pdo = getDbConnection();
    $sql = "SELECT MemberID, Name, Email, Phone, City, Address, IsAdmin 
            FROM Member 
            WHERE MemberID = :memberId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':memberId', $memberId, PDO::PARAM_INT);
    $stmt->execute();

    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        $response['success'] = true;
        $response['member'] = $member;
    } else {
        $response['message'] = '找不到指定的會員。';
    }

} catch (PDOException $e) {
    error_log("Database error in getMemberDetails.php: " . $e->getMessage());
    $response['message'] = '資料庫錯誤，無法獲取會員詳細資料。';
} catch (Exception $e) {
    error_log("General error in getMemberDetails.php: " . $e->getMessage());
    $response['message'] = '發生未知錯誤，無法獲取會員詳細資料。';
}

echo json_encode($response);
?>