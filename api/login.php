<?php
require_once __DIR__ . '/db.php';

function authenticateUser($phone, $password)
{
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT MemberID, Name, Phone, Email, Password, IsAdmin FROM Member WHERE Phone = :phone");
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            // 根據 IsAdmin 值設定不同角色
            // 0: 一般會員, 1: 員工, 2: 管理員
            $role = 'member';
            if ($user['IsAdmin'] == 1) {
                $role = 'staff';
            } elseif ($user['IsAdmin'] == 2) {
                $role = 'admin';
            }

            return [
                'success' => true,
                'user' => [
                    'id' => $user['MemberID'],
                    'name' => $user['Name'],
                    'phone' => $user['Phone'],
                    'email' => $user['Email'],
                    'role' => $role
                ]
            ];
        }

        return ['success' => false];
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return ['success' => false];
    }
}
?>