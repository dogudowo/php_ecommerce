<?php
declare(strict_types=1);

require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/db.php';

/* ===== 主要修改 1：所有請求一律先送 JSON Header ===== */
header('Content-Type: application/json; charset=utf-8');

class InstructionModel
{
    /* ----------  取得圖片 ---------- */
    public static function getInstructionImages(): array
    {
        return [
            'buyContext' => 'imgs/instruction/buyContext.jpg',
            'order' => 'imgs/instruction/order.jpg',
        ];
    }

    /* ----------  更新圖片 ---------- */
    public static function updateInstructionImage(string $type, array $newImage): bool
    {
        $allowedTypes = ['buyContext', 'order'];
        if (!in_array($type, $allowedTypes, true)) {
            return false;
        }

        $targetDir = __DIR__ . '/../imgs/instruction/';
        $targetFile = $targetDir . $type . '.jpg';

        if (!getimagesize($newImage['tmp_name'])) {
            return false;
        }

        $ext = strtolower(pathinfo($newImage['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg'], true)) {
            return false;
        }

        if (file_exists($targetFile)) {
            unlink($targetFile);
        }

        return move_uploaded_file($newImage['tmp_name'], $targetFile);
    }

    /* ----------  讀取 QA ---------- */
    public static function getQAItems(): array
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->query("SELECT * FROM QA ORDER BY QA_id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('[InstructionModel] getQAItems: ' . $e->getMessage());
            return [];
        }
    }

    /* ----------  新增 QA ---------- */
    private static function addQAItem(string $question, string $answer): bool
    {
        try {
            $pdo = getDBConnection();
            $maxId = (int) $pdo->query("SELECT MAX(QA_id) FROM QA")->fetchColumn();
            $newId = $maxId + 1;

            $stmt = $pdo->prepare(
                "INSERT INTO QA (QA_id, question, answer) VALUES (:id, :question, :answer)"
            );
            return $stmt->execute([
                ':id' => $newId,
                ':question' => $question,
                ':answer' => $answer,
            ]);
        } catch (PDOException $e) {
            error_log('[InstructionModel] addQAItem: ' . $e->getMessage());
            return false;
        }
    }

    /* ----------  更新 QA ---------- */
    private static function updateQAItem(int $id, string $question, string $answer): bool
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare(
                "UPDATE QA SET question = :question, answer = :answer WHERE QA_id = :id"
            );
            return $stmt->execute([
                ':id' => $id,
                ':question' => $question,
                ':answer' => $answer,
            ]);
        } catch (PDOException $e) {
            error_log('[InstructionModel] updateQAItem: ' . $e->getMessage());
            return false;
        }
    }

    /* ----------  刪除 QA ---------- */
    private static function deleteQAItem(int $id): bool
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM QA WHERE QA_id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('[InstructionModel] deleteQAItem: ' . $e->getMessage());
            return false;
        }
    }

    /* ----------  主要請求處理 ---------- */
    public static function handleRequest(): void
    {
        if (empty($_GET['action'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '缺少 action 參數']);
            return;
        }

        $action = $_GET['action'];

        try {
            switch ($action) {
                /* 取得列表 ------------------------------------------------ */
                case 'getQAItems':
                    echo json_encode(self::getQAItems());
                    break;

                /* 新增 ---------------------------------------------------- */
                case 'addQAItem':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['question'], $data['answer'])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => '缺少必要參數']);
                        break;
                    }
                    $ok = self::addQAItem($data['question'], $data['answer']);
                    echo json_encode(['success' => $ok]);
                    break;

                /* 更新 ---------------------------------------------------- */
                case 'updateQAItem':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'], $data['question'], $data['answer'])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => '缺少必要參數']);
                        break;
                    }
                    $ok = self::updateQAItem((int) $data['id'], $data['question'], $data['answer']);
                    echo json_encode(['success' => $ok]);
                    break;

                /* 刪除 ---------------------------------------------------- */
                case 'deleteQAItem':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (!isset($data['id'])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => '缺少必要參數']);
                        break;
                    }
                    $ok = self::deleteQAItem((int) $data['id']);
                    echo json_encode(['success' => $ok]);
                    break;

                /* 未知 ---------------------------------------------------- */
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => '未知 action']);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            error_log('[InstructionModel] handleRequest: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => '伺服器錯誤']);
        }
    }
}

/* ===== 主要修改 2：不再限制只能 GET，任何 method 只要帶 action 就處理 ===== */
if (isset($_GET['action'])) {
    InstructionModel::handleRequest();
    exit;
}
