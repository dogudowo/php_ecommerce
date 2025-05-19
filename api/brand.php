<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/error_handler.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? null;

function getBrandData()
{
    try {
        $conn = getDBConnection();

        // 取得公司介紹
        $companyIntro = [];
        $stmt = $conn->query("SELECT introdution FROM brand_introduction");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $companyIntro[] = $row['introdution'];
        }

        // 取得品牌資料
        $brands = [];
        $stmt = $conn->query("SELECT ab.brand_id, ab.brand_content, ab.brand_name 
                              FROM agent_brand ab");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $brandId = $row['brand_id'];
            $brands[$brandId] = [
                'name' => $row['brand_name'],
                'content' => $row['brand_content'],
                'logos' => glob("../imgs/brand/$brandId/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE), // 匹配任何副檔名的 logo 圖片
                'images' => glob("../imgs/brand/$brandId/newAbout/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE) // 匹配任何副檔名的最新消息圖片
            ];
        }

        return [
            'company_intro' => $companyIntro,
            'brands' => $brands
        ];
    } catch (Exception $e) {
        // 可以在這裡記錄錯誤，或者重新拋出異常
        throw $e;
    }
}



// 處理不同的 action
switch ($action) {
    case 'getBrandInfo':
        try {
            $data = getBrandData();
            echo json_encode($data);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'getBrandDetails':
        // 取得單一品牌詳細資料 (用於編輯)
        $brandId = $_GET['brand_id'] ?? null;
        if (!$brandId) {
            http_response_code(400);
            echo json_encode(['error' => '缺少 brand_id 參數']);
            exit;
        }
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT brand_id, brand_name, brand_content FROM agent_brand WHERE brand_id = ?");
            $stmt->execute([$brandId]);
            $brand = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$brand) {
                http_response_code(404);
                echo json_encode(['error' => '找不到指定的品牌']);
                exit;
            }

            // 取得品牌 Logo 圖片
            $brand['logos'] = glob("../imgs/brand/{$brandId}/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
            // 取得品牌最新消息圖片
            $brand['news_images'] = glob("../imgs/brand/{$brandId}/newAbout/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);

            echo json_encode($brand);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'updateCompanyIntro':
        // 更新公司介紹
        $intro = json_decode(file_get_contents('php://input'), true)['introduction'] ?? null;
        if ($intro === null) {
            http_response_code(400);
            echo json_encode(['error' => '缺少 introduction 參數']);
            exit;
        }
        try {
            $conn = getDBConnection();
            // 假設只有一筆公司介紹資料，ID 為 1
            $stmt = $conn->prepare("UPDATE brand_introduction SET introdution = ? WHERE brand_introduction_id = 1");
            $stmt->execute([$intro]);
            echo json_encode(['success' => true, 'message' => '公司介紹更新成功']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'addBrand':
        // 新增品牌
        $brandName = $_POST['brand_name'] ?? null;
        $brandContent = $_POST['brand_content'] ?? null;
        $brandLogos = $_FILES['brand_logos'] ?? [];
        $brandNewsImages = $_FILES['brand_news_images'] ?? []; // 新增：接收最新消息圖片

        if (!$brandName || $brandContent === null) {
            http_response_code(400);
            echo json_encode(['error' => '缺少品牌名稱或介紹參數']);
            exit;
        }

        try {
            $conn = getDBConnection();
            $conn->beginTransaction();

            // 取得目前最大的 brand_id
            $stmt = $conn->query("SELECT MAX(brand_id) AS max_id FROM agent_brand");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $nextBrandId = ($row['max_id'] === null) ? 1 : $row['max_id'] + 1;

            // 插入新的品牌資料，並指定 brand_id
            $stmt = $conn->prepare("INSERT INTO agent_brand (brand_id, brand_name, brand_content) VALUES (?, ?, ?)");
            $stmt->execute([$nextBrandId, $brandName, $brandContent]);
            $newBrandId = $nextBrandId;

            // 處理 Logo 圖片上傳
            if (!empty($brandLogos['name'][0])) { // 檢查是否有檔案上傳
                $uploadDir = __DIR__ . "/../imgs/brand/{$newBrandId}/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($brandLogos['name'] as $key => $name) {
                    $tmp_name = $brandLogos['tmp_name'][$key];
                    $error = $brandLogos['error'][$key];

                    if ($error === UPLOAD_ERR_OK) {
                        $fileExtension = pathinfo($name, PATHINFO_EXTENSION);
                        $newFileName = uniqid('logo_') . '.' . $fileExtension; // 使用唯一名稱避免衝突
                        $uploadPath = $uploadDir . $newFileName;
                        if (!move_uploaded_file($tmp_name, $uploadPath)) {
                            throw new Exception("檔案上傳失敗: {$name}");
                        }
                        chmod($uploadPath, 0777);
                    } else if ($error !== UPLOAD_ERR_NO_FILE) {
                        throw new Exception("檔案上傳錯誤: {$name}, 錯誤碼: {$error}");
                    }
                }
            }

            // 新增：處理最新消息圖片上傳
            if (!empty($brandNewsImages['name'][0])) { // 檢查是否有檔案上傳
                $uploadDir = __DIR__ . "/../imgs/brand/{$newBrandId}/newAbout/"; // 儲存到 newAbout 子資料夾
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($brandNewsImages['name'] as $key => $name) {
                    $tmp_name = $brandNewsImages['tmp_name'][$key];
                    $error = $brandNewsImages['error'][$key];

                    if ($error === UPLOAD_ERR_OK) {
                        $fileExtension = pathinfo($name, PATHINFO_EXTENSION);
                        $newFileName = uniqid('news_') . '.' . $fileExtension; // 使用唯一名稱避免衝突
                        $uploadPath = $uploadDir . $newFileName;
                        if (!move_uploaded_file($tmp_name, $uploadPath)) {
                            throw new Exception("最新消息圖片上傳失敗: {$name}");
                        }
                        chmod($uploadPath, 0777);
                    } else if ($error !== UPLOAD_ERR_NO_FILE) {
                        throw new Exception("最新消息圖片上傳錯誤: {$name}, 錯誤碼: {$error}");
                    }
                }
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => '品牌新增成功', 'brand_id' => $newBrandId]);

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'updateBrand':
        // 更新品牌
        $brandId = $_POST['brand_id'] ?? null;
        $brandName = $_POST['brand_name'] ?? null;
        $brandContent = $_POST['brand_content'] ?? null;
        $addLogos = $_FILES['add_logos'] ?? [];
        $deleteLogos = json_decode($_POST['delete_logos'] ?? '[]', true);
        $addNewsImages = $_FILES['add_news_images'] ?? [];
        $deleteNewsImages = json_decode($_POST['delete_news_images'] ?? '[]', true);

        if (!$brandId || !$brandName || $brandContent === null) {
            http_response_code(400);
            echo json_encode(['error' => '缺少必要的更新參數']);
            exit;
        }

        try {
            $conn = getDBConnection();
            $conn->beginTransaction();

            // 更新品牌文字資料
            $stmt = $conn->prepare("UPDATE agent_brand SET brand_name = ?, brand_content = ? WHERE brand_id = ?");
            $stmt->execute([$brandName, $brandContent, $brandId]);

            // 處理圖片刪除
            foreach ($deleteLogos as $logoPath) {
                // 確保路徑是預期的格式，避免刪除非品牌圖片
                if (strpos($logoPath, "../imgs/brand/{$brandId}/") === 0 && file_exists(__DIR__ . '/' . $logoPath)) {
                    unlink(__DIR__ . '/' . $logoPath);
                }
            }

            // 處理最新消息圖片刪除
            foreach ($deleteNewsImages as $imagePath) {
                // 確保路徑是預期的格式，避免刪除非品牌圖片
                if (strpos($imagePath, "../imgs/brand/{$brandId}/newAbout/") === 0 && file_exists(__DIR__ . '/' . $imagePath)) {
                    unlink(__DIR__ . '/' . $imagePath);
                }
            }

            // 處理 Logo 圖片上傳
            if (!empty($addLogos['name'][0])) { // 檢查是否有檔案上傳
                $uploadDir = __DIR__ . "/../imgs/brand/{$brandId}/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($addLogos['name'] as $key => $name) {
                    $tmp_name = $addLogos['tmp_name'][$key];
                    $error = $addLogos['error'][$key];

                    if ($error === UPLOAD_ERR_OK) {
                        $fileExtension = pathinfo($name, PATHINFO_EXTENSION);
                        $newFileName = uniqid('logo_') . '.' . $fileExtension; // 使用唯一名稱避免衝突
                        $uploadPath = $uploadDir . $newFileName;
                        if (!move_uploaded_file($tmp_name, $uploadPath)) {
                            throw new Exception("檔案上傳失敗: {$name}");
                        }
                        chmod($uploadPath, 0777);
                    } else if ($error !== UPLOAD_ERR_NO_FILE) {
                        throw new Exception("檔案上傳錯誤: {$name}, 錯誤碼: {$error}");
                    }
                }
            }

            // 處理最新消息圖片上傳
            if (!empty($addNewsImages['name'][0])) { // 檢查是否有檔案上傳
                $uploadDir = __DIR__ . "/../imgs/brand/{$brandId}/newAbout/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($addNewsImages['name'] as $key => $name) {
                    $tmp_name = $addNewsImages['tmp_name'][$key];
                    $error = $addNewsImages['error'][$key];

                    if ($error === UPLOAD_ERR_OK) {
                        $fileExtension = pathinfo($name, PATHINFO_EXTENSION);
                        $newFileName = uniqid('news_') . '.' . $fileExtension; // 使用唯一名稱避免衝突
                        $uploadPath = $uploadDir . $newFileName;
                        if (!move_uploaded_file($tmp_name, $uploadPath)) {
                            throw new Exception("檔案上傳失敗: {$name}");
                        }
                        chmod($uploadPath, 0777);
                    } else if ($error !== UPLOAD_ERR_NO_FILE) {
                        throw new Exception("檔案上傳錯誤: {$name}, 錯誤碼: {$error}");
                    }
                }
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => '品牌更新成功']);

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'deleteBrand':
        // 刪除品牌
        // 嘗試從 $_REQUEST (GET/POST) 或請求主體中取得 brand_id
        $brandId = $_REQUEST['brand_id'] ?? (json_decode(file_get_contents('php://input'), true)['brand_id'] ?? null);
        if (!$brandId) {
            http_response_code(400);
            echo json_encode(['error' => '缺少 brand_id 參數']);
            exit;
        }

        try {
            $conn = getDBConnection();
            $conn->beginTransaction();

            // 刪除品牌相關圖片資料夾
            $brandImgDir = __DIR__ . "/../imgs/brand/{$brandId}/";
            if (is_dir($brandImgDir)) {
                // 遞迴刪除資料夾內容
                function deleteDirectory($dir)
                {
                    if (!file_exists($dir))
                        return true;
                    if (!is_dir($dir))
                        return unlink($dir);
                    foreach (scandir($dir) as $item) {
                        if ($item == '.' || $item == '..')
                            continue;
                        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item))
                            return false;
                    }
                    return rmdir($dir);
                }
                deleteDirectory($brandImgDir);
            }

            // 刪除資料庫中的品牌資料
            $stmt = $conn->prepare("DELETE FROM agent_brand WHERE brand_id = ?");
            $stmt->execute([$brandId]);

            $conn->commit();
            echo json_encode(['success' => true, 'message' => '品牌刪除成功']);

        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => '無效的 action 參數']);
        break;
}