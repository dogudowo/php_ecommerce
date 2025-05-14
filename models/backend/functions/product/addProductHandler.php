<?php
require_once __DIR__ . '/../../../../api/error_handler.php';
require_once __DIR__ . '/../../../../api/db.php';

function handleAddProduct()
{
    $db = getDBConnection();
    try {
        $db->beginTransaction();

        // 驗證必填欄位
        foreach (['productName', 'productType', 'productIntro'] as $f) {
            if (empty($_POST[$f])) {
                throw new Exception('請填寫所有必填欄位');
            }
        }

        /** ---------- 新增商品主檔 ---------- **/
        $productId = insertProduct($db, [
            'Type' => $_POST['productType'],
            'ProductName' => $_POST['productName'],
            'Introdution' => $_POST['productIntro'],
            'isActive' => isset($_POST['productActive']) ? 1 : 0
        ]);

        /** ---------- 建立商品資料夾 ---------- **/
        $dir = __DIR__ . '/../../../imgs/products/' . $productId . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        /** ---------- 上傳主圖 ---------- **/
        if (!empty($_FILES['mainImage']['name'])) {
            $src = uploadImage($_FILES['mainImage']);     // imgs/products/xxxx.ext
            $ext = pathinfo($src, PATHINFO_EXTENSION);    // 取得副檔名
            rename(__DIR__ . '/../../../' . $src, $dir . 'main.' . $ext);
        }

        /** ---------- 上傳圖庫 ---------- **/
        if (!empty($_FILES['galleryImages']['name'][0])) {
            foreach ($_FILES['galleryImages']['tmp_name'] as $i => $tmp) {
                if ($_FILES['galleryImages']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['galleryImages']['name'][$i],
                        'tmp_name' => $tmp,
                        'error' => UPLOAD_ERR_OK,
                    ];
                    $src = uploadImage($file);
                    $ext = pathinfo($src, PATHINFO_EXTENSION);
                    rename(
                        __DIR__ . '/../../../' . $src,
                        $dir . 'gallery-' . ($i + 1) . '.' . $ext
                    );
                }
            }
        }

        /** ---------- 尺寸／顏色選項 ---------- **/
        if (!empty($_POST['sizes'])) {
            $sizes = json_decode($_POST['sizes'], true);
            if (is_array($sizes)) {
                insertProductOptions($db, $productId, $sizes);
            } else {
                throw new Exception('尺寸資料格式錯誤');
            }
        }

        $db->commit();
        return ['success' => true, 'message' => '新增成功', 'productId' => $productId];

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * 將上傳檔案移至暫存目錄並回傳相對路徑
 * -------------------------------------------
 * 允許：jpg / jpeg / png 
 */
function uploadImage(array $file): string
{
    $allowedExt = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        throw new Exception('不支援的檔案格式（僅支援 jpg, jpeg, png）');
    }

    $base = __DIR__ . '/../../../imgs/products/';
    if (!is_dir($base)) {
        mkdir($base, 0777, true);
    }

    $fn = uniqid('', true) . '.' . $ext;       // 確保檔名唯一並保留副檔名
    $to = $base . $fn;

    if (!move_uploaded_file($file['tmp_name'], $to)) {
        throw new Exception('圖片上傳失敗');
    }

    return 'imgs/products/' . $fn;             // 回傳相對路徑
}

/* ---------------- 以下函式無修改 ---------------- */

function insertProduct(PDO $db, array $d): int
{
    $row = $db->query("SELECT MAX(ProductID) AS m FROM Product")
        ->fetch(PDO::FETCH_ASSOC);
    $newId = ((int) $row['m']) + 1;

    $sql = "INSERT INTO Product
            (ProductID, Type, ProductName, Introdution, isActive)
            VALUES(:id, :type, :nm, :intro, :act)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':id' => $newId,
        ':type' => $d['Type'],
        ':nm' => $d['ProductName'],
        ':intro' => $d['Introdution'],
        ':act' => $d['isActive'],
    ]);

    return $newId;
}

function insertProductOptions(PDO $db, int $pid, array $sizes): void
{
    $row = $db->query("SELECT MAX(OptionID) AS m FROM Options")
        ->fetch(PDO::FETCH_ASSOC);
    $optId = ((int) $row['m']) + 1;

    $sql = "INSERT INTO Options
             (OptionID, ProductID, Color, Size, SizeDescription, Price, Stock)
             VALUES(:oid, :pid, :col, :sz, :desc, :pr, :st)";
    $stmt = $db->prepare($sql);

    foreach ($sizes as $s) {
        if (
            !isset($s['size'], $s['sizeDescription'], $s['price'], $s['colors'])
            || !is_array($s['colors'])
        ) {
            throw new Exception('尺寸資料錯誤');
        }
        foreach ($s['colors'] as $c) {
            if (!isset($c['Color'], $c['Stock'])) {
                throw new Exception('顏色資料不完整');
            }
            $stmt->execute([
                ':oid' => $optId,
                ':pid' => $pid,
                ':col' => $c['Color'],
                ':sz' => $s['size'],
                ':desc' => $s['sizeDescription'],
                ':pr' => $s['price'],
                ':st' => $c['Stock'],
            ]);
            $optId++;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'addProduct') {
    header('Content-Type: application/json');
    echo json_encode(handleAddProduct());
    exit;
}
?>