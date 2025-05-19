<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/error_handler.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? null;

try {
    switch ($action) {
        case 'getBrandInfo':
            handleGetBrandInfo();
            break;

        case 'getBrandDetails':
            handleGetBrandDetails();
            break;

        case 'updateCompanyIntro':
            handleUpdateCompanyIntro();
            break;

        case 'addBrand':
            handleAddBrand();
            break;

        case 'updateBrand':
            handleUpdateBrand();
            break;

        case 'deleteBrand':
            handleDeleteBrand();
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => '無效的 action 參數']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

/**
 * 取得所有品牌資訊及公司介紹
 */
function handleGetBrandInfo()
{
    $data = getBrandData();
    echo json_encode($data);
    exit;
}

/**
 * 取得單一品牌詳細資料
 */
function handleGetBrandDetails()
{
    $brandId = $_GET['brand_id'] ?? null;
    if (!$brandId) {
        http_response_code(400);
        echo json_encode(['error' => '缺少 brand_id 參數']);
        exit;
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT brand_id, brand_name, brand_content FROM agent_brand WHERE brand_id = ?");
    $stmt->execute([$brandId]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$brand) {
        http_response_code(404);
        echo json_encode(['error' => '找不到指定的品牌']);
        exit;
    }

    $brand['logos'] = glob("../imgs/brand/{$brandId}/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
    $brand['news_images'] = glob("../imgs/brand/{$brandId}/newAbout/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);

    echo json_encode($brand);
    exit;
}

/**
 * 更新公司介紹
 */
function handleUpdateCompanyIntro()
{
    $payload = json_decode(file_get_contents('php://input'), true);
    $intro = $payload['introduction'] ?? null;

    if ($intro === null) {
        http_response_code(400);
        echo json_encode(['error' => '缺少 introduction 參數']);
        exit;
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE brand_introduction SET introdution = ? WHERE brand_introduction_id = 1");
    $stmt->execute([$intro]);

    echo json_encode(['success' => true, 'message' => '公司介紹更新成功']);
    exit;
}

/**
 * 新增品牌
 */
function handleAddBrand()
{
    $brandName = $_POST['brand_name'] ?? null;
    $brandContent = $_POST['brand_content'] ?? null;
    $brandLogos = $_FILES['brand_logos'] ?? [];
    $brandNewsImages = $_FILES['brand_news_images'] ?? [];

    if (!$brandName || $brandContent === null) {
        http_response_code(400);
        echo json_encode(['error' => '缺少品牌名稱或介紹參數']);
        exit;
    }

    $conn = getDBConnection();
    $conn->beginTransaction();

    // 計算下一個 brand_id
    $stmt = $conn->query("SELECT MAX(brand_id) AS max_id FROM agent_brand");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $newId = ($row['max_id'] === null) ? 1 : $row['max_id'] + 1;

    // 插入品牌文字
    $stmt = $conn->prepare("INSERT INTO agent_brand (brand_id, brand_name, brand_content) VALUES (?, ?, ?)");
    $stmt->execute([$newId, $brandName, $brandContent]);

    // 上傳 logos
    uploadFiles($brandLogos, __DIR__ . "/../imgs/brand/{$newId}/", 'logo_');

    // 上傳最新消息圖片
    uploadFiles($brandNewsImages, __DIR__ . "/../imgs/brand/{$newId}/newAbout/", 'news_');

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => '品牌新增成功',
        'brand_id' => $newId,
    ]);
    exit;
}

/**
 * 更新品牌
 */
function handleUpdateBrand()
{
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

    $conn = getDBConnection();
    $conn->beginTransaction();

    // 更新文字
    $stmt = $conn->prepare("UPDATE agent_brand SET brand_name = ?, brand_content = ? WHERE brand_id = ?");
    $stmt->execute([$brandName, $brandContent, $brandId]);

    // 刪除指定檔案
    deletePaths($deleteLogos);
    deletePaths($deleteNewsImages);

    // 上傳新增檔案
    uploadFiles($addLogos, __DIR__ . "/../imgs/brand/{$brandId}/", 'logo_');
    uploadFiles($addNewsImages, __DIR__ . "/../imgs/brand/{$brandId}/newAbout/", 'news_');

    $conn->commit();
    echo json_encode(['success' => true, 'message' => '品牌更新成功']);
    exit;
}

/**
 * 刪除品牌及所有檔案
 */
function handleDeleteBrand()
{
    $input = json_decode(file_get_contents('php://input'), true);
    $brandId = $_REQUEST['brand_id'] ?? $input['brand_id'] ?? null;

    if (!$brandId) {
        http_response_code(400);
        echo json_encode(['error' => '缺少 brand_id 參數']);
        exit;
    }

    $conn = getDBConnection();
    $conn->beginTransaction();

    // 刪除檔案資料夾
    $dir = __DIR__ . "/../imgs/brand/{$brandId}/";
    deleteDirectory($dir);

    // 刪除資料庫
    $stmt = $conn->prepare("DELETE FROM agent_brand WHERE brand_id = ?");
    $stmt->execute([$brandId]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => '品牌刪除成功']);
    exit;
}

/**
 * 取得品牌與公司介紹原始資料
 */
function getBrandData()
{
    $conn = getDBConnection();

    $companyIntro = [];
    foreach ($conn->query("SELECT introdution FROM brand_introduction") as $row) {
        $companyIntro[] = $row['introdution'];
    }

    $brands = [];
    foreach ($conn->query("SELECT brand_id, brand_name, brand_content FROM agent_brand") as $row) {
        $id = $row['brand_id'];
        $brands[$id] = [
            'name' => $row['brand_name'],
            'content' => $row['brand_content'],
            'logos' => glob("../imgs/brand/$id/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE),
            'images' => glob("../imgs/brand/$id/newAbout/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE),
        ];
    }

    return [
        'company_intro' => $companyIntro,
        'brands' => $brands,
    ];
}

/**
 * 上傳多檔案輔助
 */
function uploadFiles(array $files, string $uploadDir, string $prefix)
{
    if (empty($files['name'][0])) {
        return;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($files['name'] as $i => $name) {
        $tmp = $files['tmp_name'][$i];
        $err = $files['error'][$i];
        if ($err === UPLOAD_ERR_OK) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $newName = uniqid($prefix) . '.' . $ext;
            $dest = $uploadDir . $newName;
            if (!move_uploaded_file($tmp, $dest)) {
                throw new Exception("檔案上傳失敗: {$name}");
            }
            chmod($dest, 0777);
        } elseif ($err !== UPLOAD_ERR_NO_FILE) {
            throw new Exception("檔案上傳錯誤: {$name}, 錯誤碼: {$err}");
        }
    }
}

/**
 * 刪除多條目輔助
 */
function deletePaths(array $paths)
{
    foreach ($paths as $path) {
        $real = __DIR__ . '/' . ltrim($path, '/');
        if (file_exists($real)) {
            unlink($real);
        }
    }
}

/**
 * 遞迴刪除目錄
 */
function deleteDirectory(string $dir)
{
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        deleteDirectory($dir . DIRECTORY_SEPARATOR . $item);
    }
    return rmdir($dir);
}