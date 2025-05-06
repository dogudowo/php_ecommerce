<?php
// 防止直接訪問
if (!defined('INCLUDED_FROM_ADMIN')) {
    header('Location: /admin_backend.php');
    exit;
}

class CarouselManager
{
    private $carouselDir;

    public function __construct()
    {
        $this->carouselDir = __DIR__ . '/../../imgs/carousel/';
    }

    /**
     * 獲取所有輪播圖片
     */
    public function getAllCarouselImages()
    {
        $images = [];
        if (is_dir($this->carouselDir)) {
            $files = scandir($this->carouselDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && !is_dir($this->carouselDir . $file)) {
                    $images[] = $file;
                }
            }
        }
        return $images;
    }

    /**
     * 上傳新輪播圖片
     */
    public function uploadCarouselImage($file)
    {
        $targetFile = $this->carouselDir . basename($file['name']);

        // 檢查文件類型
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            return ['success' => false, 'message' => '只允許上傳 JPG, JPEG, PNG & GIF 格式的圖片'];
        }

        // 檢查文件大小 (5MB)
        if ($file['size'] > 5000000) {
            return ['success' => false, 'message' => '圖片大小不能超過5MB'];
        }

        // 嘗試上傳文件
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return ['success' => true, 'message' => '圖片上傳成功'];
        } else {
            return ['success' => false, 'message' => '圖片上傳失敗'];
        }
    }

    /**
     * 刪除輪播圖片
     */
    public function deleteCarouselImage($filename)
    {
        $filePath = $this->carouselDir . $filename;

        // 檢查文件是否存在
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => '圖片不存在'];
        }

        // 嘗試刪除文件
        if (unlink($filePath)) {
            return ['success' => true, 'message' => '圖片刪除成功'];
        } else {
            return ['success' => false, 'message' => '圖片刪除失敗'];
        }
    }
}

// 實例化並處理請求
$carouselManager = new CarouselManager();

// 處理刪除請求
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['filename'])) {
    $result = $carouselManager->deleteCarouselImage($_POST['filename']);
    echo json_encode($result);
    exit;
}

// 處理獲取圖片列表請求
if (isset($_POST['action']) && $_POST['action'] === 'get_images') {
    echo json_encode($carouselManager->getAllCarouselImages());
    exit;
}

// 處理上傳請求
if (isset($_FILES['carouselImage'])) {
    $result = $carouselManager->uploadCarouselImage($_FILES['carouselImage']);
    echo json_encode($result);
    exit;
}
?>
<div class="container mt-4">
    <h3 class="mb-4">輪播圖片管理</h3>

    <!-- 上傳表單 -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">上傳新輪播圖片</h5>
        </div>
        <div class="card-body">
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="carouselImage" class="form-label">選擇圖片</label>
                    <input class="form-control" type="file" id="carouselImage" name="carouselImage" accept="image/*"
                        required>
                </div>
                <button type="submit" class="btn btn-primary">上傳</button>
            </form>
        </div>
    </div>

    <!-- 圖片列表 -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">輪播圖片列表</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>圖片</th>
                            <th>檔名</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="carouselImagesContainer">
                        <!-- AJAX載入圖片列表 -->
                    </tbody>
                    <script>
                        async function loadCarouselImages() {
                            try {
                                const response = await fetch('', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: 'action=get_images'
                                });
                                const images = await response.json();

                                let html = '';
                                images.forEach(image => {
                                    html += `
                                        <tr>
                                            <td><img src="imgs/carousel/${image}"
                                                    style="max-height: 100px;" class="img-thumbnail"></td>
                                            <td>${image}</td>
                                            <td>
                                                <button class="btn btn-danger btn-sm delete-btn"
                                                    data-filename="${image}">
                                                    刪除
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                                document.getElementById('carouselImagesContainer').innerHTML = html;
                            } catch (error) {
                                console.error('Error:', error);
                            }
                        }

                        // 初始載入圖片
                        loadCarouselImages();
                    </script>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // 處理上傳表單
    $('#uploadForm').on('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('上傳過程中發生錯誤');
        }
    });

    // 處理刪除按鈕
    $('.delete-btn').on('click', async function () {
        const filename = $(this).data('filename');

        if (!confirm('確定要刪除此圖片嗎？')) {
            return;
        }

        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&filename=${encodeURIComponent(filename)}`
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('刪除過程中發生錯誤');
        }
    });
</script>