<?php
require_once __DIR__ . '/../../../../api/db.php';
require_once __DIR__ . '/../../../../api/error_handler.php';

$conn = getDBConnection();

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'get_intros':
            $stmt = $conn->query("SELECT * FROM brand_introduction ORDER BY brand_introduction_id");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'update_intro':
            $id = (int) $_POST['id'];
            $content = htmlspecialchars($_POST['content']);
            $stmt = $conn->prepare("UPDATE brand_introduction SET introdution = ? WHERE brand_introduction_id = ?");
            $stmt->execute([$content, $id]);
            echo json_encode(['success' => true]);
            break;

        case 'delete_intro':
            $id = (int) $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM brand_introduction WHERE brand_introduction_id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;

        case 'create_intro':
            $content = htmlspecialchars($_POST['content']);

            // 獲取當前最大ID並遞增
            $maxId = $conn->query("SELECT MAX(brand_introduction_id) AS max_id FROM brand_introduction")->fetch(PDO::FETCH_ASSOC)['max_id'];
            $newId = ($maxId ? $maxId : 0) + 1;

            $stmt = $conn->prepare("INSERT INTO brand_introduction (brand_introduction_id, introdution) VALUES (?, ?)");
            $stmt->execute([$newId, $content]);
            echo json_encode(['success' => true, 'id' => $newId]);
            break;

        // 代理品牌處理區塊
        case 'create_brand':
            try {
                $transactionStarted = false;
                // 強化參數驗證
                if (empty($_POST['brandName'] ?? '') || empty($_POST['brandContent'] ?? '')) {
                    throw new Exception('缺少必要參數');
                }

                // 先驗證參數再開始事務
                // 過濾空值後再轉義
                $brandName = htmlspecialchars(trim($_POST['brandName'] ?? ''));
                $brandContent = htmlspecialchars(trim($_POST['brandContent'] ?? ''));

                if (empty($brandName) || empty($brandContent)) {
                    throw new Exception('品牌名稱與內容不得為空');
                }

                $conn->beginTransaction();
                $transactionStarted = true;

                // 手動生成brand_id
                $maxId = $conn->query("SELECT MAX(brand_id) FROM agent_brand")->fetchColumn();
                $newBrandId = ($maxId ? $maxId : 0) + 1;

                $stmt = $conn->prepare("INSERT INTO agent_brand (brand_id, brand_name, brand_introduction_id, brand_content) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $newBrandId,
                    htmlspecialchars($brandName),
                    1,
                    htmlspecialchars($brandContent)
                ]);

                $conn->commit();
                echo json_encode(['success' => true, 'brand_id' => $newBrandId]);
            } catch (Exception $e) {
                if ($transactionStarted && $conn->inTransaction()) {
                    $conn->rollBack();
                }
                echo json_encode(['success' => false, 'message' => '品牌建立失敗: ' . $e->getMessage()]);
            }
            break;

        case 'get_brands':
            $stmt = $conn->query("SELECT * FROM agent_brand ORDER BY brand_id");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'update_brand':
            try {
                $id = (int) $_POST['id'];
                $brandName = htmlspecialchars(trim($_POST['brandName'] ?? ''));
                $brandContent = htmlspecialchars(trim($_POST['brandContent'] ?? ''));

                if (empty($brandName) || empty($brandContent)) {
                    throw new Exception('品牌名稱與內容不得為空');
                }

                $stmt = $conn->prepare("UPDATE agent_brand SET brand_name = ?, brand_content = ? WHERE brand_id = ?");
                $stmt->execute([$brandName, $brandContent, $id]);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => '品牌更新失敗: ' . $e->getMessage()]);
            }
            break;

        case 'delete_brand':
            try {
                $id = (int) $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM agent_brand WHERE brand_id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => '品牌刪除失敗: ' . $e->getMessage()]);
            }
            break;

        case 'upload_brand_image':
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $brandId = (int) $_POST['brand_id'];
                $uploadDir = __DIR__ . '/../../../../../imgs/brand/' . $brandId . '/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    throw new Exception('文件儲存失敗');
                }
                error_log('Image uploaded: ' . $filename);
                echo json_encode(['success' => true, 'path' => $filename]);
            } else {
                echo json_encode(['success' => false, 'message' => '文件上傳失敗']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '資料庫錯誤：' . $e->getMessage()]);
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => '伺服器錯誤：' . $e->getMessage()]);
}