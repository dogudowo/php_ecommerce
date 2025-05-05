<?php
require_once __DIR__ . '/../../../../api/db.php';

function handleAddProduct()
{
    $db = getDBConnection();
    try {
        $db->beginTransaction();

        // 驗證必填
        foreach (['productName', 'productType', 'productIntro'] as $f) {
            if (empty($_POST[$f])) {
                throw new Exception('請填寫所有必填欄位');
            }
        }

        // 插入 Product
        $productId = insertProduct($db, [
            'Type' => $_POST['productType'],
            'ProductName' => $_POST['productName'],
            'Introdution' => $_POST['productIntro'],
            'isActive' => isset($_POST['productActive']) ? 1 : 0
        ]);

        // 建資料夾
        $dir = __DIR__ . '/../../../imgs/products/' . $productId . '/';
        if (!is_dir($dir))
            mkdir($dir, 0777, true);

        // 上傳主圖
        if (!empty($_FILES['mainImage']['name'])) {
            $src = uploadImage($_FILES['mainImage']);
            rename(__DIR__ . '/../../../' . $src, $dir . 'main.jpg');
        }

        // 上傳 gallery
        if (!empty($_FILES['galleryImages']['name'][0])) {
            foreach ($_FILES['galleryImages']['tmp_name'] as $i => $tmp) {
                if ($_FILES['galleryImages']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['galleryImages']['name'][$i],
                        'tmp_name' => $tmp,
                        'error' => UPLOAD_ERR_OK,
                    ];
                    $src = uploadImage($file);
                    rename(__DIR__ . '/../../../' . $src, $dir . 'gallery-' . ($i + 1) . '.jpg');
                }
            }
        }

        // 插入 Options
        if (!empty($_POST['sizes'])) {
            $sizes = json_decode($_POST['sizes'], true); // 將 JSON 字符串解析為關聯數組
            if (is_array($sizes)) {
                insertProductOptions($db, $productId, $sizes);
            } else {
                throw new Exception('尺寸資料格式錯誤');
            }
        }

        $db->commit();
        return ['success' => true, 'message' => '新增成功', 'productId' => $productId];

    } catch (Exception $e) {
        if ($db->inTransaction())
            $db->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function uploadImage(array $file): string
{
    $base = __DIR__ . '/../../../imgs/products/';
    if (!is_dir($base))
        mkdir($base, 0777, true);
    $fn = uniqid() . '_' . basename($file['name']);
    $to = $base . $fn;
    if (!move_uploaded_file($file['tmp_name'], $to)) {
        throw new Exception('圖片上傳失敗');
    }
    return 'imgs/products/' . $fn;
}

function insertProduct(PDO $db, array $d): int
{
    $row = $db->query("SELECT MAX(ProductID) AS m FROM Product")
        ->fetch(PDO::FETCH_ASSOC);
    $newId = ((int) $row['m']) + 1;

    $sql = "INSERT INTO Product
            (ProductID,Type,ProductName,Introdution,isActive)
            VALUES(:id,:type,:nm,:intro,:act)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':id' => $newId,
        ':type' => $d['Type'],
        ':nm' => $d['ProductName'],
        ':intro' => $d['Introdution'],
        ':act' => $d['isActive']
    ]);
    return $newId;
}

function insertProductOptions(PDO $db, int $pid, array $sizes): void
{
    $row = $db->query("SELECT MAX(OptionID) AS m FROM Options")
        ->fetch(PDO::FETCH_ASSOC);
    $optId = ((int) $row['m']) + 1;

    $sql = "INSERT INTO Options
            (OptionID,ProductID,Color,Size,SizeDescription,Price,Stock)
            VALUES(:oid,:pid,:col,:sz,:desc,:pr,:st)";
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
                ':st' => $c['Stock']
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