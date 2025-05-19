<?php
require_once __DIR__ . '/../api/error_handler.php';
// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN')) {
    header('Location: ../login.php');
    exit;
}

// 輪播圖目錄路徑
$carousel_dir = __DIR__ . '/../imgs/carousel/';
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
    (() => {
        "use strict";
        // ----- 常量 -----
        const API_URL = "api/carousel.php";
        const container = document.getElementById("carouselContainer");
        const form = document.getElementById("carouselUploadForm");
        const fileInput = document.getElementById("carouselImage");
        const previewContainer = document.getElementById("imagePreviewContainer");
        const previewImg = document.getElementById("imagePreview");
        const deleteModal = document.getElementById("deleteCarouselModal");
        const deleteImg = document.getElementById("deleteImagePreview");
        const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

        // ----- Toast 容器 & 函式 -----
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.setAttribute('aria-live', 'polite');
        toastContainer.setAttribute('aria-atomic', 'true');
        document.body.appendChild(toastContainer);

        function showToast(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `toast align-items-center text-white bg-${type} border-0`;
            el.setAttribute('role', 'alert');
            el.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                    <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>
                    ${msg}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>`;
            toastContainer.appendChild(el);
            new bootstrap.Toast(el, { delay: 3000 }).show();
        }

        // ----- 通用 Fetch Helper -----
        async function apiPost(formData) {
            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });
                return await res.json();
            } catch (err) {
                console.error(err);
                return { success: false, message: '網路錯誤，請稍後再試' };
            }
        }

        // ----- 載入列表 -----
        async function loadCarouselImages() {
            container.innerHTML =
                `<div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">載入輪播圖...</p>
                </div>`;
            const res = await fetch(`${API_URL}?action=list`).then(r => r.json()).catch(_ => ({ success: false, message: '載入失敗' }));
            if (!res.success) {
                container.innerHTML =
                    `<div class="col-12 text-center py-5">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size:3rem;"></i>
                        <p class="mt-3">${res.message}</p>
                    </div>`;
                return;
            }
            if (!res.data || res.data.length === 0) {
                container.innerHTML =
                    `<div class="col-12 text-center py-5">
                        <i class="bi bi-images text-muted" style="font-size:3rem;"></i>
                        <p class="mt-3">目前沒有輪播圖，請上傳新圖片。</p>
                    </div>`;
                return;
            }
            container.innerHTML = res.data.map(img => `
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                    <img src="imgs/carousel/${img}" class="card-img-top" alt="${img}">
                    <div class="card-body">
                        <h6 class="card-title text-truncate">${img}</h6>
                    </div>
                    <div class="card-footer bg-white">
                        <button class="btn btn-danger btn-sm delete-carousel" 
                                data-filename="${img}" data-image="imgs/carousel/${img}"
                                data-bs-toggle="modal" data-bs-target="#deleteCarouselModal">
                        <i class="bi bi-trash me-1"></i>刪除
                        </button>
                    </div>
                    </div>
                </div>`
            ).join('');
        }

        // ----- 初始化 -----
        document.addEventListener('DOMContentLoaded', () => {
            loadCarouselImages();

            // 圖片預覽
            fileInput.addEventListener('change', e => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = ev => {
                        previewImg.src = ev.target.result;
                        previewContainer.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.classList.add('d-none');
                }
            });

            // 上傳
            document.getElementById('uploadCarouselBtn').addEventListener('click', async btn => {
                if (!fileInput.files.length) return showToast('請選擇要上傳的圖片', 'warning');
                btn.disabled = true;
                const originalHTML = btn.innerHTML;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> 上傳中...`;

                const fd = new FormData(form);
                fd.set('action', 'upload');
                const result = await apiPost(fd);

                if (result.success) {
                    form.reset();
                    previewContainer.classList.add('d-none');
                    showToast('輪播圖上傳成功！', 'success');
                    loadCarouselImages();
                } else {
                    showToast(`上傳失敗：${result.message}`, 'danger');
                }
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            });

            // 刪除：事件代理
            container.addEventListener('click', e => {
                if (!e.target.closest('.delete-carousel')) return;
                const btn = e.target.closest('.delete-carousel');
                deleteImg.src = btn.dataset.image;
                confirmDeleteBtn.dataset.filename = btn.dataset.filename;
            });

            // 確認刪除
            confirmDeleteBtn.addEventListener('click', async () => {
                const filename = confirmDeleteBtn.dataset.filename;
                const fd = new FormData();
                fd.set('action', 'delete');
                fd.set('filename', filename);

                const result = await apiPost(fd);
                if (result.success) {
                    showToast('輪播圖已成功刪除！', 'success');
                    loadCarouselImages();
                    bootstrap.Modal.getInstance(deleteModal).hide();
                } else {
                    showToast(`刪除失敗：${result.message}`, 'danger');
                }
            });
        });
    })();
</script>