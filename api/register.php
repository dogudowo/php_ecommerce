<?php
require_once __DIR__ . '/error_handler.php';
// 註冊功能API
require_once __DIR__ . '/db.php';

/**
 * 註冊新用戶
 * @param string $username 使用者名稱
 * @param string $email 電子郵件
 * @param string $password 密碼
 * @param string $confirm_password 確認密碼
 * @return array 包含成功狀態和訊息
 */
function registerUser($name, $phone, $email, $password, $confirm_password, $city, $address)
{
    // 驗證輸入
    if (empty($name) || empty($phone) || empty($email) || empty($password) || empty($confirm_password) || empty($city) || empty($address)) {
        return ['success' => false, 'message' => '所有欄位都必須填寫'];
    }

    if ($password !== $confirm_password) {
        return ['success' => false, 'message' => '密碼與確認密碼不相符'];
    }

    // 檢查電子郵件格式
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => '無效的電子郵件格式'];
    }

    // 檢查手機號碼格式
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        return ['success' => false, 'message' => '手機號碼格式不正確'];
    }

    $pdo = getDBConnection();

    try {
        // 檢查手機號碼是否已存在
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Member WHERE Phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => '手機號碼已被使用'];
        }

        // 檢查電子郵件是否已存在
        $stmt = $pdo->prepare("SELECT MemberID FROM Member WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => '電子郵件已被使用'];
        }

        // 獲取當前最大MemberID
        $stmt = $pdo->prepare("SELECT MAX(MemberID) as max_id FROM Member");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $newMemberID = $result['max_id'] ? $result['max_id'] + 1 : 1;

        // 創建新用戶
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); //bcrypt
        $stmt = $pdo->prepare("INSERT INTO Member (MemberID, Name, Phone, Email, Password, City, Address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$newMemberID, $name, $phone, $email, $hashed_password, $city, $address]);

        return ['success' => true, 'message' => '註冊成功'];
    } catch (PDOException $e) {
        error_log("註冊錯誤: " . $e->getMessage());
        return ['success' => false, 'message' => '註冊過程中發生錯誤'];
    }
}
?>