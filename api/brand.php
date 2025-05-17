<?php
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/db.php';

class BrandModel
{
    public static function getBrandInfo()
    {
        $conn = getDBConnection();

        // 獲取公司介紹
        $companyIntro = [];
        $stmt = $conn->query("SELECT introdution FROM brand_introduction");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $companyIntro[] = $row['introdution'];
        }

        // 獲取品牌資料
        $brands = [];
        $stmt = $conn->query("SELECT ab.brand_id, ab.brand_content, bi.introdution, ab.brand_name 
                              FROM agent_brand ab 
                              JOIN brand_introduction bi ON ab.brand_introduction_id = bi.brand_introduction_id");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $brandId = $row['brand_id'];
            $brands[$brandId] = [
                'name' => $row['brand_name'],
                'content' => $row['brand_content'],
                'description' => explode('\n', $row['introdution']),
                'logos' => [],
                'images' => []
            ];

            // 獲取品牌logo
            $logoDir = "imgs/brand/{$brandId}/";
            if (is_dir(__DIR__ . "/../{$logoDir}")) {
                $logos = glob(__DIR__ . "/../{$logoDir}" . "logo-*.{png,jpg,jpeg}", GLOB_BRACE);
                foreach ($logos as $logo) {
                    $brands[$brandId]['logos'][] = basename($logo);
                }
            }

            // 獲取品牌圖片
            $newAboutDir = "imgs/brand/{$brandId}/newAbout/";
            if (is_dir(__DIR__ . "/../{$newAboutDir}")) {
                $images = glob(__DIR__ . "/../{$newAboutDir}" . "newabout-*.jpg", GLOB_BRACE);
                foreach ($images as $image) {
                    $brands[$brandId]['images'][] = "brand/{$brandId}/newAbout/" . basename($image);
                }
            }
        }

        return [
            'company_intro' => $companyIntro,
            'brands' => $brands
        ];
    }

    public static function handleRequest()
    {
        header('Content-Type: application/json');
        $conn = getDBConnection();

        try {
            $action = $_POST['action'] ?? '';
            umask(0); // 確保 chmod 不受 umask 限制

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

                case 'create_brand':
                    try {
                        $transactionStarted = false;
                        if (empty($_POST['brandName'] ?? '') || empty($_POST['brandContent'] ?? '')) {
                            throw new Exception('缺少必要參數');
                        }

                        $brandName = htmlspecialchars(trim($_POST['brandName'] ?? ''));
                        $brandContent = htmlspecialchars(trim($_POST['brandContent'] ?? ''));

                        if (empty($brandName) || empty($brandContent)) {
                            throw new Exception('品牌名稱與內容不得為空');
                        }

                        $conn->beginTransaction();
                        $transactionStarted = true;

                        $maxId = $conn->query("SELECT MAX(brand_id) FROM agent_brand")->fetchColumn();
                        $newBrandId = ($maxId ? $maxId : 0) + 1;

                        $stmt = $conn->prepare("INSERT INTO agent_brand (brand_id, brand_name, brand_introduction_id, brand_content) VALUES (?, ?, ?, ?)");
                        $stmt->execute([
                            $newBrandId,
                            $brandName,
                            1,
                            $brandContent
                        ]);

                        // 處理 Logo 圖片上傳
                        if (isset($_FILES['logo_images']) && !empty($_FILES['logo_images']['name'][0])) {
                            $logoDir = __DIR__ . '/../imgs/brand/' . $newBrandId . '/';
                            if (!file_exists($logoDir)) {
                                mkdir($logoDir, 0777, true);
                                chmod($logoDir, 0777);
                            }

                            $logoFiles = $_FILES['logo_images'];
                            for ($i = 0; $i < count($logoFiles['name']); $i++) {
                                if ($logoFiles['error'][$i] === UPLOAD_ERR_OK) {
                                    $extension = strtolower(pathinfo($logoFiles['name'][$i], PATHINFO_EXTENSION));
                                    if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
                                        throw new Exception('不支援的Logo圖片格式');
                                    }
                                    $logoFilename = 'logo-' . $i . '.' . $extension;
                                    $destPath = $logoDir . $logoFilename;
                                    move_uploaded_file($logoFiles['tmp_name'][$i], $destPath);
                                    chmod($destPath, 0777);
                                }
                            }
                        }

                        // 處理 最新消息 圖片上傳
                        if (isset($_FILES['news_images']) && !empty($_FILES['news_images']['name'][0])) {
                            $newsDir = __DIR__ . '/../imgs/brand/' . $newBrandId . '/newAbout/';
                            if (!file_exists($newsDir)) {
                                mkdir($newsDir, 0777, true);
                                chmod($newsDir, 0777);
                            }

                            $newsFiles = $_FILES['news_images'];
                            for ($i = 0; $i < count($newsFiles['name']); $i++) {
                                if ($newsFiles['error'][$i] === UPLOAD_ERR_OK) {
                                    $extension = strtolower(pathinfo($newsFiles['name'][$i], PATHINFO_EXTENSION));
                                    if (!in_array($extension, ['jpg', 'jpeg'])) {
                                        throw new Exception('不支援的最新消息圖片格式');
                                    }
                                    $newsFilename = 'newabout-' . $i . '.' . $extension;
                                    $destPath = $newsDir . $newsFilename;
                                    move_uploaded_file($newsFiles['tmp_name'][$i], $destPath);
                                    chmod($destPath, 0777);
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

                        // Logo 圖片覆寫
                        if (isset($_FILES['logo_images']) && !empty($_FILES['logo_images']['name'][0])) {
                            $logoDir = __DIR__ . '/../imgs/brand/' . $id . '/';
                            if (!file_exists($logoDir)) {
                                mkdir($logoDir, 0777, true);
                                chmod($logoDir, 0777);
                            }

                            $logoFiles = $_FILES['logo_images'];
                            for ($i = 0; $i < count($logoFiles['name']); $i++) {
                                if ($logoFiles['error'][$i] === UPLOAD_ERR_OK) {
                                    $extension = strtolower(pathinfo($logoFiles['name'][$i], PATHINFO_EXTENSION));
                                    if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
                                        throw new Exception('不支援的Logo圖片格式');
                                    }
                                    $logoFilename = 'logo-' . $i . '.' . $extension;
                                    $destPath = $logoDir . $logoFilename;
                                    move_uploaded_file($logoFiles['tmp_name'][$i], $destPath);
                                    chmod($destPath, 0777);
                                }
                            }
                        }

                        // 最新消息 圖片覆寫
                        if (isset($_FILES['news_images']) && !empty($_FILES['news_images']['name'][0])) {
                            $newsDir = __DIR__ . '/../imgs/brand/' . $id . '/newAbout/';
                            if (!file_exists($newsDir)) {
                                mkdir($newsDir, 0777, true);
                                chmod($newsDir, 0777);
                            }

                            $newsFiles = $_FILES['news_images'];
                            for ($i = 0; $i < count($newsFiles['name']); $i++) {
                                if ($newsFiles['error'][$i] === UPLOAD_ERR_OK) {
                                    $extension = strtolower(pathinfo($newsFiles['name'][$i], PATHINFO_EXTENSION));
                                    if (!in_array($extension, ['jpg', 'jpeg'])) {
                                        throw new Exception('不支援的最新消息圖片格式');
                                    }
                                    $newsFilename = 'newabout-' . $i . '.' . $extension;
                                    $destPath = $newsDir . $newsFilename;
                                    move_uploaded_file($newsFiles['tmp_name'][$i], $destPath);
                                    chmod($destPath, 0777);
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

                        // 刪除品牌資料夾
                        $brandDir = __DIR__ . '/../imgs/brand/' . $id;
                        if (is_dir($brandDir)) {
                            $files = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($brandDir, RecursiveDirectoryIterator::SKIP_DOTS),
                                RecursiveIteratorIterator::CHILD_FIRST
                            );
                            foreach ($files as $file) {
                                $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
                            }
                            rmdir($brandDir);
                        }

                        echo json_encode(['success' => true]);
                    } catch (Exception $e) {
                        echo json_encode(['success' => false, 'message' => '品牌刪除失敗: ' . $e->getMessage()]);
                    }
                    break;

                case 'get_brand_images':
                    $brandId = (int) $_POST['brand_id'];
                    $logos = [];
                    $newsImages = [];

                    $logoDir = __DIR__ . '/../imgs/brand/' . $brandId . '/';
                    if (is_dir($logoDir)) {
                        $logoFiles = glob($logoDir . 'logo-*.{png,jpg,jpeg}', GLOB_BRACE);
                        foreach ($logoFiles as $file) {
                            $logos[] = basename($file);
                        }
                    }

                    $newsDir = __DIR__ . '/../imgs/brand/' . $brandId . '/newAbout/';
                    if (is_dir($newsDir)) {
                        $newsFiles = glob($newsDir . 'newabout-*.jpg', GLOB_BRACE);
                        foreach ($newsFiles as $file) {
                            $newsImages[] = basename($file);
                        }
                    }

                    echo json_encode(['success' => true, 'logos' => $logos, 'news_images' => $newsImages]);
                    break;

                default:
                    echo json_encode(['success' => false, 'message' => '無效的操作']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '操作失敗: ' . $e->getMessage()]);
        }
    }
}

// 處理請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    BrandModel::handleRequest();
}
?>