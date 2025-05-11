<?php
require_once __DIR__ . '/../../api/error_handler.php';
// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN')) {
    header('Location: ../../login.php');
    exit;
}

// 輪播圖目錄路徑
$carousel_dir = __DIR__ . '/../../imgs/carousel/';
?>

<div class="container-fluid p-0">
    <!-- 標題 & 按鈕 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-images me-2"></i>輪播圖管理</h3>
    </div>

    <!-- 新增輪播圖區域 -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>新增輪播圖</h5>
        </div>
        <div class="card-body">
            <form id="carouselUploadForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="carouselImage" class="form-label">選擇圖片</label>
                            <input type="file" class="form-control" id="carouselImage" name="carouselImage"
                                accept="image/*" required>
                            <div class="form-text">建議使用寬高比為16:9的圖片，最大檔案大小為2MB。</div>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" id="uploadCarouselBtn">
                                <i class="bi bi-upload me-1"></i>上傳圖片
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="imagePreviewContainer" class="mb-3 d-none">
                            <label class="form-label">圖片預覽</label>
                            <div class="border rounded p-2">
                                <img id="imagePreview" class="img-fluid" src="" alt="圖片預覽">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 輪播圖列表 -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>輪播圖列表</h5>
        </div>
        <div class="card-body">
            <div id="carouselContainer" class="row g-3">
                <!-- 輪播圖將通過AJAX載入 -->
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                    <p class="mt-2">載入輪播圖...</p>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- 刪除輪播圖模態框 -->
<div class="modal fade" id="deleteCarouselModal" tabindex="-1" aria-labelledby="deleteCarouselModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCarouselModalLabel">確認刪除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="關閉"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span>確定要刪除這張輪播圖嗎？此操作無法撤銷。</span>
                </div>
                <div class="border rounded p-2 mb-3">
                    <img id="deleteImagePreview" class="img-fluid" src="" alt="要刪除的圖片">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">確認刪除</button>
            </div>
        </div>
    </div>
</div>

<script>
    // 當頁面載入完成後執行
    document.addEventListener('DOMContentLoaded', function () {
        // 載入輪播圖列表
        loadCarouselImages();

        // 圖片預覽功能
        document.getElementById('carouselImage').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreviewContainer').classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                document.getElementById('imagePreviewContainer').classList.add('d-none');
            }
        });

        // 上傳輪播圖
        document.getElementById('uploadCarouselBtn').addEventListener('click', async function () {
            const fileInput = document.getElementById('carouselImage');
            if (!fileInput.files[0]) {
                showAlert('warning', '請選擇要上傳的圖片');
                return;
            }

            // 顯示上傳中狀態
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 上傳中...';

            const formData = new FormData();
            formData.append('carouselImage', fileInput.files[0]);
            formData.append('action', 'upload');

            try {
                const response = await fetch('models/backend/functions/carouselHandler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    // 重新載入輪播圖列表
                    loadCarouselImages();
                    // 重置表單
                    document.getElementById('carouselUploadForm').reset();
                    document.getElementById('imagePreviewContainer').classList.add('d-none');
                    // 顯示成功訊息
                    showAlert('success', '輪播圖上傳成功！');
                } else {
                    showAlert('danger', '上傳失敗：' + result.message);
                }
            } catch (error) {
                console.log('上傳錯誤：', error);
                showAlert('danger', '上傳過程中發生錯誤，請稍後再試。');
            } finally {
                // 恢復按鈕狀態
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-upload me-1"></i>上傳圖片';
            }
        });

        // 設置刪除確認按鈕事件
        document.getElementById('confirmDeleteBtn').addEventListener('click', async function () {
            const filename = this.dataset.filename;
            if (!filename) return;

            try {
                const formData = new FormData();
                formData.append('filename', filename);
                formData.append('action', 'delete');

                const response = await fetch('models/backend/functions/carouselHandler.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    // 重新載入輪播圖列表
                    loadCarouselImages();
                    // 關閉模態框
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteCarouselModal'));
                    modal.hide();
                    // 顯示成功訊息
                    showAlert('success', '輪播圖已成功刪除！');
                } else {
                    showAlert('danger', '刪除失敗：' + result.message);
                }
            } catch (error) {
                console.log('刪除錯誤：', error);
                showAlert('danger', '刪除過程中發生錯誤，請稍後再試。');
            }
        });
    });

    // 載入輪播圖列表函數
    async function loadCarouselImages() {
        const container = document.getElementById('carouselContainer');
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">載入中...</span>
                </div>
                <p class="mt-2">載入輪播圖...</p>
            </div>
        `;

        try {
            const response = await fetch('models/backend/functions/carouselHandler.php?action=list');
            const result = await response.json();

            if (result.success) {
                if (result.images.length > 0) {
                    let html = '';
                    result.images.forEach(image => {
                        html += `
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    <img src="imgs/carousel/${image}" class="card-img-top" alt="輪播圖">
                                    <div class="card-body">
                                        <h6 class="card-title text-truncate">${image}</h6>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <button type="button" class="btn btn-danger btn-sm delete-carousel" 
                                                data-bs-toggle="modal" data-bs-target="#deleteCarouselModal"
                                                data-filename="${image}" data-image="imgs/carousel/${image}">
                                            <i class="bi bi-trash me-1"></i>刪除
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;

                    // 為所有刪除按鈕添加事件
                    document.querySelectorAll('.delete-carousel').forEach(button => {
                        button.addEventListener('click', function () {
                            const filename = this.dataset.filename;
                            const imageSrc = this.dataset.image;
                            document.getElementById('deleteImagePreview').src = imageSrc;
                            document.getElementById('confirmDeleteBtn').dataset.filename = filename;
                        });
                    });
                } else {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-images text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3">目前沒有輪播圖，請點擊「新增輪播圖」按鈕上傳。</p>
                        </div>
                    `;
                }
            } else {
                showAlert('danger', '載入失敗：' + result.message);
            }
        } catch (error) {
            console.log('載入錯誤：', error);
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-3">載入輪播圖時發生錯誤，請重新整理頁面或稍後再試。</p>
                </div>
            `;
        }
    }

    // 顯示提示訊息函數
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="關閉"></button>
        `;
        document.body.appendChild(alertDiv);

        // 3秒後自動關閉
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 3000);
    }
</script>