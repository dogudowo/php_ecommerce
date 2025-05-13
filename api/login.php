<?php
require_once __DIR__ . '/error_handler.php';
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
            return [
                'success' => true,
                'user' => [
                    'id' => $user['MemberID'],
                    'name' => $user['Name'],
                    'phone' => $user['Phone'],
                    'email' => $user['Email'],
                    'role' => (int) $user['IsAdmin']
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