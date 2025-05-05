<?php
// 會員功能文件

// 引入數據庫連接
require_once __DIR__ . '/../../../../api/db.php';

// 獲取所有會員
function getAllMembers()
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT m.*, 
                              (SELECT COUNT(*) FROM Orders WHERE MembersID = m.MemberID) as OrderCount 
                              FROM Member m 
                              ORDER BY m.MemberID");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching members: " . $e->getMessage());
        return [];
    }
}

// 獲取會員詳情
function getMemberDetails($memberId)
{
    $pdo = getDBConnection();
    try {
        // 獲取會員基本信息
        $stmt = $pdo->prepare("SELECT MemberID, Name, Email, Phone, City, Address, IsAdmin FROM Member WHERE MemberID = ?");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            return null;
        }

        return $member;
    } catch (PDOException $e) {
        error_log("Error fetching member details: " . $e->getMessage());
        return null;
    }
}
?>