<?php
require_once __DIR__ . '/error_handler.php';
class QAModel
{
    public static function getQAItems()
    {
        // 引入數據庫連接
        require_once __DIR__ . '/db.php';

        try {
            // 獲取數據庫連接
            $pdo = getDBConnection();

            // 準備SQL查詢，從QA表中獲取所有問答
            $stmt = $pdo->prepare("SELECT question, answer FROM QA ORDER BY QA_id");
            $stmt->execute();

            // 獲取所有結果
            $qaItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 如果沒有數據，返回空數組
            if (empty($qaItems)) {
                return [];
            }

            return $qaItems;

        } catch (PDOException $e) {
            // 記錄錯誤
            error_log("Error fetching QA items: " . $e->getMessage());

            // 發生錯誤時返回空數組
            return [];
        }
    }
}
?>