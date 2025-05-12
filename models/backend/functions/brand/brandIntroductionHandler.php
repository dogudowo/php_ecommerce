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

                // 處理Logo圖片上傳 (多張)
                if (isset($_FILES['logo_images']) && !empty($_FILES['logo_images']['name'][0])) {
                    $logoDir = __DIR__ . '/../../../../imgs/brand/' . $newBrandId . '/';
                    if (!file_exists($logoDir)) {
                        if (!mkdir($logoDir, 0777, true)) {
                            throw new Exception('無法建立Logo目錄，請檢查權限');
                        }
                        chmod($logoDir, 0777);
                    }

                    $logoFiles = $_FILES['logo_images'];
                    $uploadedLogos = [];

                    for ($i = 0; $i < count($logoFiles['name']); $i++) {
                        if ($logoFiles['error'][$i] === UPLOAD_ERR_OK) {
                            $extension = strtolower(pathinfo($logoFiles['name'][$i], PATHINFO_EXTENSION));
                            if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
                                throw new Exception('不支援的Logo圖片格式');
                            }
                            $logoFilename = 'logo-' . $i . '.' . $extension;
                            if (!move_uploaded_file($logoFiles['tmp_name'][$i], $logoDir . $logoFilename)) {
                                throw new Exception('Logo圖片儲存失敗');
                            }
                            $uploadedLogos[] = $logoFilename;
                        }
                    }
                }

                // 處理最新消息圖片上傳 (多張)
                if (isset($_FILES['news_images']) && !empty($_FILES['news_images']['name'][0])) {
                    $newsDir = __DIR__ . '/../../../../imgs/brand/' . $newBrandId . '/newAbout/';
                    if (!file_exists($newsDir)) {
                        if (!mkdir($newsDir, 0777, true)) {
                            throw new Exception('無法建立最新消息圖片目錄，請檢查權限');
                        }
                        chmod($newsDir, 0777);
                    }

                    $newsFiles = $_FILES['news_images'];
                    $uploadedNews = [];

                    for ($i = 0; $i < count($newsFiles['name']); $i++) {
                        if ($newsFiles['error'][$i] === UPLOAD_ERR_OK) {
                            $extension = strtolower(pathinfo($newsFiles['name'][$i], PATHINFO_EXTENSION));
                            if (!in_array($extension, ['jpg', 'jpeg'])) {
                                throw new Exception('不支援的最新消息圖片格式');
                            }
                            $newsFilename = 'newabout-' . $i . '.' . $extension;
                            if (!move_uploaded_file($newsFiles['tmp_name'][$i], $newsDir . $newsFilename)) {
                                throw new Exception('最新消息圖片儲存失敗');
                            }
                            $uploadedNews[] = $newsFilename;
                        }
                    }
                }

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

                // 處理Logo圖片上傳 (多張)
                if (isset($_FILES['logo_images']) && !empty($_FILES['logo_images']['name'][0])) {
                    $logoDir = __DIR__ . '/../../../../imgs/brand/' . $id . '/';
                    if (!file_exists($logoDir)) {
                        mkdir($logoDir, 0777, true);
                    }

                    $logoFiles = $_FILES['logo_images'];
                    $uploadedLogos = [];

                    for ($i = 0; $i < count($logoFiles['name']); $i++) {
                        if ($logoFiles['error'][$i] === UPLOAD_ERR_OK) {
                            $extension = strtolower(pathinfo($logoFiles['name'][$i], PATHINFO_EXTENSION));
                            if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
                                throw new Exception('不支援的Logo圖片格式');
                            }
                            $logoFilename = 'logo-' . $i . '.' . $extension;
                            if (!move_uploaded_file($logoFiles['tmp_name'][$i], $logoDir . $logoFilename)) {
                                throw new Exception('Logo圖片儲存失敗');
                            }
                            $uploadedLogos[] = $logoFilename;
                        }
                    }
                }

                // 處理最新消息圖片上傳 (多張)
                if (isset($_FILES['news_images']) && !empty($_FILES['news_images']['name'][0])) {
                    $newsDir = $baseDir . $id . '/newAbout/';
                    if (!is_dir($newsDir)) {
                        mkdir($newsDir, 0777, true);
                    }

                    $newsFiles = $_FILES['news_images'];
                    $uploadedNews = [];

                    for ($i = 0; $i < count($newsFiles['name']); $i++) {
                        if ($newsFiles['error'][$i] === UPLOAD_ERR_OK) {
                            $extension = strtolower(pathinfo($newsFiles['name'][$i], PATHINFO_EXTENSION));
                            if (!in_array($extension, ['jpg', 'jpeg'])) {
                                throw new Exception('不支援的最新消息圖片格式');
                            }
                            $newsFilename = 'newabout-' . $i . '.' . $extension;
                            if (!move_uploaded_file($newsFiles['tmp_name'][$i], $newsDir . $newsFilename)) {
                                throw new Exception('最新消息圖片儲存失敗');
                            }
                            $uploadedNews[] = $newsFilename;
                        }
                    }
                }

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

                // 刪除品牌圖片資料夾
                $brandDir = __DIR__ . '/../../../../imgs/brand/' . $id;
                if (file_exists($brandDir)) {
                    // 刪除newAbout子目錄
                    $newAboutDir = $brandDir . '/newAbout';
                    if (file_exists($newAboutDir)) {
                        array_map('unlink', glob($newAboutDir . '/*'));
                        rmdir($newAboutDir);
                    }
                    // 刪除主目錄
                    array_map('unlink', glob($brandDir . '/*'));
                    rmdir($brandDir);
                }

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => '品牌刪除失敗: ' . $e->getMessage()]);
            }
            break;
        case 'get_brand_images':
            try {
                $brandId = isset($_GET['brand_id']) ? (int) $_GET['brand_id'] : (isset($_POST['brand_id']) ? (int) $_POST['brand_id'] : null);
                if ($brandId === null) {
                    throw new Exception('未提供品牌ID');
                }
                $logos = [];
                $news_images = [];

                $logoDir = __DIR__ . '/../../../../imgs/brand/' . $brandId . '/';
                if (is_dir($logoDir)) {
                    $logoFiles = array_diff(scandir($logoDir), array('.', '..', 'newAbout'));
                    foreach ($logoFiles as $file) {
                        if (is_file($logoDir . $file) && preg_match('/^logo-.*\.(png|jpg|jpeg)$/i', $file)) {
                            $logos[] = $file;
                        }
                    }
                }

                $newsDir = __DIR__ . '/../../../../imgs/brand/' . $brandId . '/newAbout/';
                if (is_dir($newsDir)) {
                    $newsFiles = array_diff(scandir($newsDir), array('.', '..'));
                    foreach ($newsFiles as $file) {
                        if (is_file($newsDir . $file) && preg_match('/^newabout-.*\.(jpg|jpeg)$/i', $file)) {
                            $news_images[] = $file;
                        }
                    }
                }
                echo json_encode(['success' => true, 'data' => ['logos' => $logos, 'news_images' => $news_images]]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => '獲取品牌圖片失敗: ' . $e->getMessage()]);
            }
            break;

        case 'delete_brand_image':
            try {
                $brandId = (int) $_POST['brand_id'];
                $imageType = $_POST['image_type']; // 'logo' or 'news'
                $imageName = basename($_POST['image_name']); // 防止路徑遍歷

                if ($imageType === 'logo') {
                    $filePath = __DIR__ . '/../../../../imgs/brand/' . $brandId . '/' . $imageName;
                } elseif ($imageType === 'news') {
                    $filePath = __DIR__ . '/../../../../imgs/brand/' . $brandId . '/newAbout/' . $imageName;
                } else {
                    throw new Exception('無效的圖片類型');
                }

                if (file_exists($filePath)) {
                    unlink($filePath);
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('圖片不存在');
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => '刪除品牌圖片失敗: ' . $e->getMessage()]);
            }
            break;

        case 'upload_brand_image':
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $brandId = (int) $_POST['brand_id'];
                $uploadDir = __DIR__ . '/../../../../imgs/brand/' . $brandId . '/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
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