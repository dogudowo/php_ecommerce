<?php
// 處理 AJAX 請求的入口點
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // 設置 Content-Type 為 JSON
    header('Content-Type: application/json');

    try {
        // 根據 action 參數調用相應的方法
        switch ($action) {
            case 'getQAItems':
                InstructionHandler::getQAItemsJSON();
                exit;
            case 'addQAItem':
                $data = json_decode(file_get_contents('php://input'), true);
                if (!$data || !isset($data['question']) || !isset($data['answer'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '缺少必要參數']);
                    exit;
                }
                $result = InstructionHandler::addQAItem($data['question'], $data['answer']);
                echo json_encode(['success' => $result]);
                exit;
            case 'updateQAItem':
                $data = json_decode(file_get_contents('php://input'), true);
                if (!$data || !isset($data['id']) || !isset($data['question']) || !isset($data['answer'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '缺少必要參數']);
                    exit;
                }
                $result = InstructionHandler::updateQAItem($data['id'], $data['question'], $data['answer']);
                echo json_encode(['success' => $result]);
                exit;
            case 'deleteQAItem':
                $data = json_decode(file_get_contents('php://input'), true);
                if (!$data || !isset($data['id'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '缺少必要參數']);
                    exit;
                }
                $result = InstructionHandler::deleteQAItem($data['id']);
                echo json_encode(['success' => $result]);
                exit;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未知的操作']);
                exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log('Error in InstructionHandler: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => '伺服器錯誤']);
        exit;
    }
}

class InstructionHandler
{
    /**
     * 取得購物說明圖片路徑
     */
    public static function getInstructionImages()
    {
        return [
            'buyContext' => 'imgs/instruction/buyContext.jpg',
            'order' => 'imgs/instruction/order.jpg'
        ];
    }

    /**
     * 更新購物說明圖片
     */
    public static function updateInstructionImage($type, $newImage)
    {
        // 驗證圖片類型
        $allowedTypes = ['buyContext', 'order'];
        if (!in_array($type, $allowedTypes)) {
            return false;
        }

        // 處理圖片上傳，使用固定的文件名替換舊圖片
        $targetDir = __DIR__ . '/../../../../imgs/instruction/';
        $targetFile = $targetDir . $type . '.jpg'; // 使用固定的文件名：buyContext.jpg 或 order.jpg

        // 檢查是否為圖片
        if (!getimagesize($newImage['tmp_name'])) {
            return false;
        }

        // 檢查文件格式
        $imageFileType = strtolower(pathinfo($newImage['name'], PATHINFO_EXTENSION));
        if ($imageFileType != "jpg" && $imageFileType != "jpeg") {
            return false;
        }

        // 如果舊圖片存在，先刪除它
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }

        // 嘗試上傳新圖片
        if (move_uploaded_file($newImage['tmp_name'], $targetFile)) {
            return true;
        }

        return false;
    }

    /**
     * 取得所有QA項目 (AJAX用)
     */
    public static function getQAItemsJSON()
    {
        header('Content-Type: application/json');
        try {
            require_once __DIR__ . '/../../../../api/db.php';
            $db = getDBConnection();

            $stmt = $db->prepare("SELECT * FROM QA ORDER BY QA_id ASC");
            $stmt->execute();

            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * 新增QA項目 (AJAX用)
     */
    public static function addQAItem($question, $answer)
    {
        try {
            require_once __DIR__ . '/../../../../api/db.php';
            $db = getDBConnection();

            // 查詢當前最大的 QA_id
            $maxIdStmt = $db->query("SELECT MAX(QA_id) as max_id FROM QA");
            $maxId = $maxIdStmt->fetch(PDO::FETCH_ASSOC)['max_id'];
            $newId = ($maxId !== null) ? $maxId + 1 : 1; // 如果表為空，則從 1 開始

            // 使用顯式指定 QA_id 的方式插入新記錄
            $stmt = $db->prepare("INSERT INTO QA (QA_id, question, answer) VALUES (:id, :question, :answer)");
            $result = $stmt->execute([
                ':id' => $newId,
                ':question' => $question,
                ':answer' => $answer
            ]);

            return $result;
        } catch (PDOException $e) {
            error_log('Database error in addQAItem: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 更新QA項目 (AJAX用)
     */
    public static function updateQAItem($id, $question, $answer)
    {
        try {
            require_once __DIR__ . '/../../../../api/db.php';
            $db = getDBConnection();

            $stmt = $db->prepare("UPDATE QA SET question = :question, answer = :answer WHERE QA_id = :id");
            return $stmt->execute([
                ':id' => $id,
                ':question' => $question,
                ':answer' => $answer
            ]);
        } catch (PDOException $e) {
            error_log('Database error in updateQAItem: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 刪除QA項目 (AJAX用)
     */
    public static function deleteQAItem($id)
    {
        try {
            require_once __DIR__ . '/../../../../api/db.php';
            $db = getDBConnection();

            $stmt = $db->prepare("DELETE FROM QA WHERE QA_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Database error in deleteQAItem: ' . $e->getMessage());
            return false;
        }
    }
}
?>