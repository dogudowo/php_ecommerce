<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../api/db.php';

$response = ['success' => false, 'members' => [], 'message' => ''];

try {
    $pdo = getDbConnection();

    // Query to get members and their order counts
    // Assuming an 'Orders' table exists with a 'MemberID' column
    $sql = "SELECT 
                m.MemberID, 
                m.Name, 
                m.Email, 
                m.Phone, 
                m.City, 
                m.Address, 
                m.IsAdmin,
                (SELECT COUNT(*) FROM Orders o WHERE o.MembersID = m.MemberID) as OrderCount
            FROM Member m
            ORDER BY m.MemberID ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($members) {
        $response['success'] = true;
        $response['members'] = $members;
    } else {
        $response['message'] = 'No members found.';
        // It's not an error if no members are found, so success might still be true depending on desired behavior.
        // For now, let's consider it a success but with an empty list or a specific message.
        $response['success'] = true;
    }

} catch (PDOException $e) {
    error_log("Database error in getMembers.php: " . $e->getMessage());
    $response['message'] = '資料庫錯誤，無法獲取會員列表。 ' . $e->getMessage();
} catch (Exception $e) {
    error_log("General error in getMembers.php: " . $e->getMessage());
    $response['message'] = '發生未知錯誤，無法獲取會員列表。';
}

echo json_encode($response);
?>