<!-- ================= 品牌介紹／代理品牌管理 ================= -->
<div class="container-fluid p-0">
    <!-- 頁首操作列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-award me-2"></i>品牌介紹</h3>
    </div>

    <!-- ──── 品牌介紹列表 ──── -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-text-paragraph me-2"></i>品牌介紹編輯</h5>
        </div>
        <div class="card-body" id="introContainer">
            <div class="spinner-wrapper text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>

    <!-- ──── 代理品牌列表 ──── -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-shop me-2"></i>代理品牌管理</h5>
            <button class="btn btn-primary" id="addBrandBtn">
                <i class="bi bi-plus-lg me-1"></i>新增品牌
            </button>
        </div>
        <div class="card-body" id="brandContainer">
            <div class="spinner-wrapper text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
</div>

<!-- ===== 品牌介紹 Modals ===== -->
<!-- 刪除介紹 -->
<div class="modal fade" id="deleteIntroModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">確認刪除段落</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>確定要刪除此介紹段落嗎？</p>
                <input type="hidden" id="deleteIntroId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteIntroBtn">確認刪除</button>
            </div>
        </form>
    </div>
</div>

<!-- 編輯介紹 -->
<div class="modal fade" id="editIntroModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">編輯介紹段落</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" rows="8" id="editIntroContent"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveIntroBtn">儲存段落</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== 代理品牌 Modals ===== -->
<!-- 刪除品牌 -->
<div class="modal fade" id="deleteBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">確認刪除品牌</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>確定要刪除此代理品牌嗎？</p>
                <input type="hidden" id="deleteBrandId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBrandBtn">確認刪除</button>
            </div>
        </form>
    </div>
</div>

<!-- 編輯品牌 -->
<div class="modal fade" id="editBrandModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">編輯代理品牌</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">品牌名稱</label>
                    <input type="text" class="form-control" id="editBrandNameInput">
                </div>
                <div class="mb-3">
                    <label class="form-label">品牌介紹</label>
                    <textarea class="form-control" rows="8" id="editBrandContentInput"></textarea>
                </div>

                <!-- Logo 圖片上傳 -->
                <div class="mb-3">
                    <label class="form-label">品牌 Logo</label>
                    <input type="file" class="form-control" id="editBrandLogoInput" accept="image/png, image/jpeg"
                        multiple>
                    <div class="mt-2" id="editBrandLogoPreviewContainer"></div>
                    <div class="mt-2" id="existingBrandLogosContainer"></div>
                </div>

                <!-- 最新消息圖片上傳 -->
                <div class="mb-3">
                    <label class="form-label">最新消息圖片</label>
                    <input type="file" class="form-control" id="editBrandNewsImageInput" accept="image/jpeg" multiple>
                    <div class="mt-2" id="editBrandNewsImagePreviewContainer"></div>
                    <div class="mt-2" id="existingBrandNewsImagesContainer"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveBrandBtn">儲存修改</button>
            </div>
        </form>
    </div>
</div>

<!-- 新增品牌 -->
<div class="modal fade" id="addBrandModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新增代理品牌</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">品牌名稱</label>
                    <input type="text" class="form-control" id="brandNameInput">
                </div>
                <div class="mb-3">
                    <label class="form-label">品牌介紹</label>
                    <textarea class="form-control" rows="5" id="brandContentInput"></textarea>
                </div>

                <!-- Logo 上傳 -->
                <div class="mb-3">
                    <label class="form-label">品牌 Logo</label>
                    <input type="file" class="form-control" id="addBrandLogoInput" accept="image/png, image/jpeg"
                        multiple>
                    <div class="mt-2" id="addBrandLogoPreviewContainer"></div>
                </div>

                <!-- 最新消息圖片 -->
                <div class="mb-3">
                    <label class="form-label">最新消息圖片</label>
                    <input type="file" class="form-control" id="addBrandNewsImageInput" accept="image/jpeg" multiple>
                    <div class="mt-2" id="addBrandNewsImagePreviewContainer"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="createBrandBtn">新增品牌</button>
            </div>
        </form>
    </div>
</div>
<div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;"></div>

<!-- ================= JavaScript ================= -->
<script>
    (() => {
        'use strict';

        // === 全域設定 ===
        const API_URL = 'api/brand.php';
        const introContainer = document.getElementById('introContainer');
        const brandContainer = document.getElementById('brandContainer');

        // === Toast 工具 ===
        const toastContainer = document.getElementById('toastContainer');
        function showToast(msg, type = 'success') {
            const el = document.createElement('div');
            el.className = `toast align-items-center text-white bg-${type} border-0 mb-2`;
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

        // === API 呼叫 ===
        async function apiRequest(action, data = {}) {
            let opts = { method: 'POST' };
            if (data instanceof FormData) {
                data.append('action', action);
                opts.body = data;
            } else {
                const params = new URLSearchParams({ action, ...data });
                opts.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
                opts.body = params;
            }
            const res = await fetch(API_URL, opts).then(r => r.json());
            if (!res.success) throw new Error(res.message || '操作失敗');
            return res.data ?? res;
        }

        // === 模板函式 ===
        function renderIntroItem(item) {
            return `
                <div class="card mb-3" data-id="${item.brand_introduction_id}">
                    <div class="card-body d-flex justify-content-between align-items-center">
                    <p class="mb-0 pe-1">${item.introdution}</p>
                    <button class="btn btn-sm btn-outline-primary btn-edit-intro">
                        <i class="bi bi-pencil"></i>
                    </button>
                    </div>
                </div>`;
        }
        function renderBrandItem(b) {
            return `
                <div class="card mb-3" data-id="${b.brand_id}">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="mb-1">${b.brand_name}</h5>
                            <p class="text-muted mb-0">${b.brand_content}</p>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary btn-edit-brand">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-brand">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
        }

        // === 載入列表 ===
        async function loadIntros() {
            introContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
            try {
                const list = await apiRequest('get_intros');
                introContainer.innerHTML = list.length
                    ? list.map(renderIntroItem).join('')
                    : '<p class="text-muted">暫無介紹段落</p>';
            } catch (e) {
                introContainer.innerHTML = '<p class="text-danger">載入失敗</p>';
                showToast('讀取介紹段落失敗：' + e.message, 'danger');
            }
        }
        async function loadBrands() {
            brandContainer.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
            try {
                const list = await apiRequest('get_brands');
                brandContainer.innerHTML = list.length
                    ? list.map(renderBrandItem).join('')
                    : '<p class="text-muted">暫無代理品牌</p>';
            } catch (e) {
                brandContainer.innerHTML = '<p class="text-danger">載入失敗</p>';
                showToast('讀取品牌列表失敗：' + e.message, 'danger');
            }
        }

        // === 介紹編輯 ===
        const editIntroModal = new bootstrap.Modal(document.getElementById('editIntroModal'));
        const editIntroTextarea = document.getElementById('editIntroContent');
        let currentIntroId = null;

        introContainer.addEventListener('click', e => {
            if (!e.target.closest('.btn-edit-intro')) return;
            const card = e.target.closest('.card');
            currentIntroId = card.dataset.id;
            editIntroTextarea.value = card.querySelector('p').textContent.trim();
            editIntroModal.show();
        });

        document.getElementById('saveIntroBtn').addEventListener('click', async () => {
            try {
                await apiRequest('update_intro', { id: currentIntroId, content: editIntroTextarea.value.trim() });
                editIntroModal.hide();
                await loadIntros();
                showToast('介紹段落已更新', 'success');
            } catch (e) {
                showToast('更新失敗：' + e.message, 'danger');
            }
        });

        // === 新增品牌 ===
        const addBrandModal = new bootstrap.Modal(document.getElementById('addBrandModal'));
        document.getElementById('addBrandBtn').addEventListener('click', () => {
            document.getElementById('brandNameInput').value = '';
            document.getElementById('brandContentInput').value = '';
            document.getElementById('addBrandLogoInput').value = '';
            document.getElementById('addBrandNewsImageInput').value = '';
            document.getElementById('addBrandLogoPreviewContainer').innerHTML = '';
            document.getElementById('addBrandNewsImagePreviewContainer').innerHTML = '';
            addBrandModal.show();
        });
        document.getElementById('createBrandBtn').addEventListener('click', async () => {
            const name = document.getElementById('brandNameInput').value.trim();
            if (!name) return showToast('品牌名稱不得為空', 'warning');
            const fd = new FormData();
            fd.append('brandName', name);
            fd.append('brandContent', document.getElementById('brandContentInput').value.trim());
            [...document.getElementById('addBrandLogoInput').files].forEach((f, i) => fd.append(`logo_images[${i}]`, f));
            [...document.getElementById('addBrandNewsImageInput').files].forEach((f, i) => fd.append(`news_images[${i}]`, f));
            try {
                await apiRequest('create_brand', fd);
                addBrandModal.hide();
                await loadBrands();
                showToast('新增品牌成功', 'success');
            } catch (e) {
                showToast('新增失敗：' + e.message, 'danger');
            }
        });

        // === 編輯 / 刪除品牌 ===
        const editBrandModal = new bootstrap.Modal(document.getElementById('editBrandModal'));
        let currentBrandId = null;

        brandContainer.addEventListener('click', async e => {
            // 編輯
            if (e.target.closest('.btn-edit-brand')) {
                const card = e.target.closest('.card');
                currentBrandId = card.dataset.id;
                document.getElementById('editBrandNameInput').value = card.querySelector('h5').textContent.trim();
                document.getElementById('editBrandContentInput').value = card.querySelector('p').textContent.trim();
                document.getElementById('editBrandLogoInput').value = '';
                document.getElementById('editBrandNewsImageInput').value = '';
                document.getElementById('editBrandLogoPreviewContainer').innerHTML = '';
                document.getElementById('editBrandNewsImagePreviewContainer').innerHTML = '';
                document.getElementById('existingBrandLogosContainer').innerHTML = '';
                document.getElementById('existingBrandNewsImagesContainer').innerHTML = '';
                editBrandModal.show();
                // 載入圖片
                loadBrandImages(currentBrandId);
                return;
            }
            // 刪除
            if (e.target.closest('.btn-delete-brand')) {
                const id = e.target.closest('.card').dataset.id;
                if (!confirm('確定刪除此品牌？')) return;
                try {
                    await apiRequest('delete_brand', { id });
                    await loadBrands();
                    showToast('刪除品牌成功', 'success');
                } catch (err) {
                    showToast('刪除失敗：' + err.message, 'danger');
                }
            }
        });

        document.getElementById('saveBrandBtn').addEventListener('click', async () => {
            const fd = new FormData();
            fd.append('id', currentBrandId);
            fd.append('brandName', document.getElementById('editBrandNameInput').value.trim());
            fd.append('brandContent', document.getElementById('editBrandContentInput').value.trim());
            [...document.getElementById('editBrandLogoInput').files].forEach((f, i) => fd.append(`logo_images[${i}]`, f));
            [...document.getElementById('editBrandNewsImageInput').files].forEach((f, i) => fd.append(`news_images[${i}]`, f));
            try {
                await apiRequest('update_brand', fd);
                editBrandModal.hide();
                await loadBrands();
                showToast('品牌已更新', 'success');
            } catch (e) {
                showToast('更新失敗：' + e.message, 'danger');
            }
        });

        // === 載入品牌圖片並綁刪除 ===
        async function loadBrandImages(id) {
            try {
                const res = await apiRequest('get_brand_images', { brand_id: id });
                const logos = document.getElementById('existingBrandLogosContainer');
                const news = document.getElementById('existingBrandNewsImagesContainer');
                logos.innerHTML = '<h6>現有 Logo</h6><div class="row g-2" id="logoRow"></div>';
                news.innerHTML = '<h6>現有最新消息</h6><div class="row g-2" id="newsRow"></div>';
                const logoRow = document.getElementById('logoRow');
                const newsRow = document.getElementById('newsRow');
                (res.logos || []).forEach(fn => {
                    const col = document.createElement('div'); col.className = 'col-6 col-md-4 col-lg-3';
                    col.innerHTML = `
                        <div class="card h-100">
                            <img src="imgs/brand/${id}/${fn}" class="card-img-top">
                            <div class="card-footer text-center">
                            <button class="btn btn-sm btn-danger btn-del-brand-img" data-type="logo" data-name="${fn}">
                                刪除
                            </button>
                            </div>
                        </div>`;
                    logoRow.append(col);
                });
                (res.news_images || []).forEach(fn => {
                    const col = document.createElement('div'); col.className = 'col-6 col-md-4 col-lg-3';
                    col.innerHTML = `
                        <div class="card h-100">
                            <img src="imgs/brand/${id}/newAbout/${fn}" class="card-img-top">
                            <div class="card-footer text-center">
                            <button class="btn btn-sm btn-danger btn-del-brand-img" data-type="news" data-name="${fn}">
                                刪除
                            </button>
                            </div>
                        </div>`;
                    newsRow.append(col);
                });
                // 刪除按鈕綁定
                [...logos.querySelectorAll('.btn-del-brand-img'),
                ...news.querySelectorAll('.btn-del-brand-img')]
                    .forEach(btn => {
                        btn.addEventListener('click', async () => {
                            if (!confirm('確定刪除這張圖片？')) return;
                            try {
                                await apiRequest('delete_brand_image', {
                                    brand_id: id,
                                    image_type: btn.dataset.type,
                                    image_name: btn.dataset.name
                                });
                                loadBrandImages(id);
                                showToast('圖片已刪除', 'success');
                            } catch (e) {
                                showToast('刪除失敗：' + e.message, 'danger');
                            }
                        });
                    });
            } catch (e) {
                showToast('載入圖片失敗：' + e.message, 'danger');
            }
        }

        // === 圖片預覽 ===
        function bindPreview(inputId, containerId) {
            document.getElementById(inputId).addEventListener('change', e => {
                const box = document.getElementById(containerId);
                box.innerHTML = '';
                [...e.target.files].forEach(f => {
                    const reader = new FileReader();
                    reader.onload = ev => {
                        const img = document.createElement('img');
                        img.src = ev.target.result;
                        img.className = 'img-thumbnail me-2 mb-2';
                        img.style.cssText = 'width:100px;height:100px;object-fit:contain';
                        box.appendChild(img);
                    };
                    reader.readAsDataURL(f);
                });
            });
        }
        bindPreview('addBrandLogoInput', 'addBrandLogoPreviewContainer');
        bindPreview('addBrandNewsImageInput', 'addBrandNewsImagePreviewContainer');
        bindPreview('editBrandLogoInput', 'editBrandLogoPreviewContainer');
        bindPreview('editBrandNewsImageInput', 'editBrandNewsImagePreviewContainer');

        // === 初始化 ===
        document.addEventListener('DOMContentLoaded', () => {
            loadIntros();
            loadBrands();
        });

    })();
</script>