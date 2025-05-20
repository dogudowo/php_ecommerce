<?php
declare(strict_types=1);

require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/db.php';

/* ----------  取得圖片 ---------- */
function getInstructionImages(): array
{
    $imagePaths = [
        'buyContext' => 'imgs/instruction/buyContext.jpg',
        'order' => 'imgs/instruction/order.jpg',
    ];

    $imagesWithTimestamp = [];
    foreach ($imagePaths as $type => $path) {
        $fullPath = __DIR__ . '/../' . $path;
        if (file_exists($fullPath)) {
            $timestamp = filemtime($fullPath);
            $imagesWithTimestamp[$type] = $path . '?' . $timestamp;
        } else {
            $imagesWithTimestamp[$type] = $path; // 如果檔案不存在，回傳原始路徑或預設圖片路徑
        }
    }

    return $imagesWithTimestamp;
}

/* ----------  更新圖片 ---------- */
function updateInstructionImage(string $type, array $newImage): string|bool
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

    // 刪除舊的圖片檔案
    $oldFiles = glob($targetDir . $type . '.*');
    if ($oldFiles) {
        foreach ($oldFiles as $oldFile) {
            if (is_file($oldFile)) {
                unlink($oldFile);
            } else {
                // 如果是目錄，記錄錯誤或跳過
                error_log('[InstructionModel] updateInstructionImage: Skipping directory ' . $oldFile . ' during cleanup.');
            }
        }
    }

    $newTargetFile = $targetDir . $type . '.' . $ext; // 使用固定名稱 + 副檔名
    if (move_uploaded_file($newImage['tmp_name'], $newTargetFile)) {
        // 設定檔案權限為 777
        if (chmod($newTargetFile, 0777)) {
            // 回傳相對於網站根目錄的圖片路徑
            return 'imgs/instruction/' . $type . '.' . $ext;
        } else {
            error_log('[InstructionModel] updateInstructionImage: Failed to set permissions for ' . $newTargetFile);
            // 即使設定權限失敗，檔案還是移動成功了，這裡可以根據需求決定是否回傳新的圖片路徑
            return 'imgs/instruction/' . $type . '.' . $ext; // 暫時回傳新路徑，表示檔案移動成功
        }
    } else {
        return false;
    }
}


/* ----------  讀取 QA ---------- */
function getQAItems(): array
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
function addQAItem(string $question, string $answer): bool
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
function updateQAItem(int $id, string $question, string $answer): bool
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
function deleteQAItem(int $id): bool
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
function handleRequest(): void
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
                echo json_encode(getQAItems());
                break;

            /* 新增 ---------------------------------------------------- */
            case 'addQAItem':
                $data = json_decode(file_get_contents('php://input'), true);
                if (!isset($data['question'], $data['answer'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '缺少必要參數']);
                    break;
                }
                $ok = addQAItem($data['question'], $data['answer']);
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
                $ok = updateQAItem((int) $data['id'], $data['question'], $data['answer']);
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
                $ok = deleteQAItem((int) $data['id']);
                echo json_encode(['success' => $ok]);
                break;

            /* 未知 ---------------------------------------------------- */
            /* 更新圖片 ------------------------------------------------ */
            case 'updateInstructionImage':
                if (!isset($_POST['image_type'], $_FILES['instruction_image'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '缺少必要參數']);
                    break;
                }
                $ok = updateInstructionImage($_POST['image_type'], $_FILES['instruction_image']);
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

/* ===== 主要修改 2：不再限制只能 GET，任何 method 只要帶 action 就處理 ===== */
if (isset($_GET['action'])) {
    handleRequest();
    exit;
}
