<?php
defined('INCLUDED_FROM_ADMIN') || exit(header('Location: ../login.php'));
?>

<div class="container-fluid p-4">
    <h3 class="mb-4"><i class="bi bi-building me-2"></i>品牌管理</h3>

    <!-- 公司介紹區塊 -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            公司介紹
            <button class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#editCompanyIntroModal">編輯公司介紹</button>
        </div>
        <div class="card-body">
            <div id="companyIntroContent">
                <!-- 公司介紹內容將由 JS 載入 -->
            </div>
        </div>
    </div>

    <!-- 品牌列表區塊 -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            品牌列表
            <button class="btn btn-success " data-bs-toggle="modal" data-bs-target="#addBrandModal">新增品牌</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 20%;">品牌名稱</th>
                            <th style="width: 70%;">品牌介紹</th>
                            <th style="width: 10%;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="brandTableBody">
                        <!-- 品牌資料將由 JS 載入 -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 編輯公司介紹模態框 -->
<div class="modal fade" id="editCompanyIntroModal" tabindex="-1" aria-labelledby="editCompanyIntroModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editCompanyIntroModalLabel">編輯公司介紹</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCompanyIntroForm">
                    <div class="mb-3">
                        <label for="companyIntroTextarea" class="form-label">公司介紹內容</label>
                        <textarea class="form-control" id="companyIntroTextarea" rows="10" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveCompanyIntroBtn">儲存變更</button>
            </div>
        </div>
    </div>
</div>

<!-- 新增品牌模態框 -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addBrandModalLabel">新增品牌</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addBrandForm">
                    <div class="mb-3">
                        <label for="addBrandNameInput" class="form-label">品牌名稱</label>
                        <input type="text" class="form-control" id="addBrandNameInput" required>
                    </div>
                    <div class="mb-3">
                        <label for="addBrandContentInput" class="form-label">品牌介紹</label>
                        <textarea class="form-control" id="addBrandContentInput" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="addBrandLogoInput" class="form-label">品牌 Logo (多選)</label>
                        <input type="file" class="form-control" id="addBrandLogoInput" accept="image/*" multiple>
                        <div class="mt-2" id="addBrandLogoPreviewContainer"></div>
                    </div>
                    <div class="mb-3">
                        <label for="addBrandNewsImageInput" class="form-label">最新消息圖片 (多選)</label>
                        <input type="file" class="form-control" id="addBrandNewsImageInput" accept="image/*" multiple>
                        <div class="mt-2" id="addBrandNewsImagePreviewContainer"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-success" id="confirmAddBrandBtn">新增</button>
            </div>
        </div>
    </div>
</div>

<!-- 編輯品牌模態框 -->
<div class="modal fade" id="editBrandModal" tabindex="-1" aria-labelledby="editBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editBrandModalLabel">編輯品牌</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editBrandForm">
                    <input type="hidden" id="editBrandIdInput">
                    <div class="mb-3">
                        <label for="editBrandNameInput" class="form-label">品牌名稱</label>
                        <input type="text" class="form-control" id="editBrandNameInput" required>
                    </div>
                    <div class="mb-3">
                        <label for="editBrandContentInput" class="form-label">品牌介紹</label>
                        <textarea class="form-control" id="editBrandContentInput" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">品牌 Logo</label>
                        <div id="existingBrandLogosContainer" class="row mt-2"></div>
                        <label for="editBrandLogoInput" class="form-label mt-2">新增更多 Logo (多選)</label>
                        <input type="file" class="form-control" id="editBrandLogoInput" accept="image/*" multiple>
                        <div class="mt-2" id="editBrandLogoPreviewContainer"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">最新消息圖片</label>
                        <div id="existingBrandNewsImagesContainer" class="row mt-2"></div>
                        <label for="editBrandNewsImageInput" class="form-label mt-2">新增更多最新消息圖片 (多選)</label>
                        <input type="file" class="form-control" id="editBrandNewsImageInput" accept="image/*" multiple>
                        <div class="mt-2" id="editBrandNewsImagePreviewContainer"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveBrandBtn">儲存變更</button>
            </div>
        </div>
    </div>
</div>

<!-- 刪除品牌確認模態框 -->
<div class="modal fade" id="deleteBrandConfirmModal" tabindex="-1" aria-labelledby="deleteBrandConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteBrandConfirmModalLabel">確認刪除</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                確定要刪除品牌 <strong id="deleteBrandName"></strong> 嗎？此操作無法復原。
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBrandBtn">刪除</button>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        'use strict';

        // ---------- 常數 ----------
        const API_URL = 'api/brand.php';

        // ---------- DOM 取得 ----------
        const DOM = {
            brandTableBody: document.getElementById('brandTableBody'),
            companyIntroContent: document.getElementById('companyIntroContent'),
            companyIntroTextarea: document.getElementById('companyIntroTextarea'),
            saveCompanyIntroBtn: document.getElementById('saveCompanyIntroBtn'),
            addBrandModal: new bootstrap.Modal(document.getElementById('addBrandModal')),
            addBrandForm: document.getElementById('addBrandForm'),
            addBrandNameInput: document.getElementById('addBrandNameInput'),
            addBrandContentInput: document.getElementById('addBrandContentInput'),
            addBrandLogoInput: document.getElementById('addBrandLogoInput'),
            addBrandLogoPreviewContainer: document.getElementById('addBrandLogoPreviewContainer'),
            addBrandNewsImageInput: document.getElementById('addBrandNewsImageInput'),
            addBrandNewsImagePreviewContainer: document.getElementById('addBrandNewsImagePreviewContainer'),
            confirmAddBrandBtn: document.getElementById('confirmAddBrandBtn'),

            editBrandModal: new bootstrap.Modal(document.getElementById('editBrandModal')),
            editBrandForm: document.getElementById('editBrandForm'),
            editBrandIdInput: document.getElementById('editBrandIdInput'),
            editBrandNameInput: document.getElementById('editBrandNameInput'),
            editBrandContentInput: document.getElementById('editBrandContentInput'),
            editBrandLogoInput: document.getElementById('editBrandLogoInput'),
            editBrandLogoPreviewContainer: document.getElementById('editBrandLogoPreviewContainer'),
            existingBrandLogosContainer: document.getElementById('existingBrandLogosContainer'),
            saveBrandBtn: document.getElementById('saveBrandBtn'),
            editBrandNewsImageInput: document.getElementById('editBrandNewsImageInput'),
            editBrandNewsImagePreviewContainer: document.getElementById('editBrandNewsImagePreviewContainer'),
            existingBrandNewsImagesContainer: document.getElementById('existingBrandNewsImagesContainer'),

            deleteBrandConfirmModal: new bootstrap.Modal(document.getElementById('deleteBrandConfirmModal')),
            deleteBrandName: document.getElementById('deleteBrandName'),
            confirmDeleteBrandBtn: document.getElementById('confirmDeleteBrandBtn')
        };

        // ---------- Toast ----------
        const Toast = {
            container: (() => {
                const div = document.createElement("div");
                div.className = "position-fixed bottom-0 end-0 p-3";
                div.style.zIndex = 1080;
                document.body.appendChild(div);
                return div;
            })(),
            show(msg, type = 'info') {
                const html = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">${msg}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
                const el = document.createElement('div');
                el.innerHTML = html;
                this.container.appendChild(el.firstElementChild);
                new bootstrap.Toast(this.container.lastElementChild).show();
            }
        };

        // ---------- API ----------
        const API = {
            async request(action, payload = null, method = null) {
                let url = `${API_URL}?action=${action}`;
                let options = {};
                if (payload instanceof FormData) {
                    options.method = 'POST';
                    options.body = payload;
                } else if (payload && (!method || method === 'POST')) {
                    options.method = 'POST';
                    options.headers = { 'Content-Type': 'application/json' };
                    options.body = JSON.stringify(payload);
                } else if (payload && method === 'GET') {
                    url += '&' + new URLSearchParams(payload).toString();
                    options.method = 'GET';
                } else {
                    options.method = 'GET';
                }
                const resp = await fetch(url, options);
                const data = await resp.json();
                if (!resp.ok || data.success === false) throw new Error(data.error || 'API Error');
                return data;
            }
        };

        // ---------- 圖片預覽 ----------
        const ImagePreview = {
            render(container, files) {
                container.innerHTML = '';
                [...files].forEach(file => {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const col = document.createElement('div');
                        col.className = 'col-6 col-md-4 col-lg-3 mb-2';
                        col.innerHTML = `
                            <div class="card h-100 image-preview-card">
                                <img src="${imgPath}" class="card-img-top" style="height:200px;object-fit:cover;">
                                <div class="card-body p-1 text-center">
                                    <button ...> 刪除 </button>
                                </div>
                            </div>
                        `;
                        col.querySelector('.remove-new-image-btn').onclick = () => col.remove();
                        container.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                });
            },
            renderExisting(container, urls, type) {
                container.innerHTML = '';
                urls.forEach(url => {
                    const imgPath = url.replace('../', '');
                    const col = document.createElement('div');
                    col.className = 'col-4 col-lg-4 mb-2';
                    col.innerHTML = `
                    <div class="card h-100 image-preview-card">
                        <img src="${imgPath}" class="card-img-top" alt="${type}">
                        <div class="card-body p-1 text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-image-btn" data-src="${url}">
                                <i class="bi bi-trash"></i> 刪除
                            </button>
                        </div>
                    </div>
                `;
                    container.appendChild(col);
                });
            }
        };

        // ---------- 狀態 ----------
        let editState = {
            brandId: null,
            deletedLogos: [],
            deletedNews: [],
            originalLogos: [],
            originalNews: []
        };

        // ---------- 資料載入 ----------
        async function loadBrandData() {
            try {
                const data = await API.request('getBrandInfo');
                // 公司介紹
                DOM.companyIntroContent.innerHTML = data.company_intro.map(p => `<p class="px-2 text-start mx-0">${p.replace(/\n/g, '<br>')}</p>`).join('');
                DOM.companyIntroTextarea.value = data.company_intro.join('\n\n');
                // 品牌列表
                DOM.brandTableBody.innerHTML = '';
                Object.entries(data.brands).forEach(([brandId, brand]) => {
                    DOM.brandTableBody.innerHTML += `
                    <tr>
                        <td>${brand.name}</td>
                        <td>${brand.content.substring(0, 100)}...</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary edit-brand-btn" data-id="${brandId}"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-danger delete-brand-btn" data-id="${brandId}" data-name="${brand.name}"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
                });
            } catch (e) {
                Toast.show('載入品牌資料失敗: ' + e.message, 'danger');
            }
        }

        // ---------- 公司介紹事件 ----------
        DOM.saveCompanyIntroBtn.onclick = async () => {
            try {
                await API.request('updateCompanyIntro', { introduction: DOM.companyIntroTextarea.value });
                Toast.show('公司介紹更新成功', 'success');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editCompanyIntroModal')).hide();
                loadBrandData();
            } catch (e) {
                Toast.show('更新公司介紹失敗: ' + e.message, 'danger');
            }
        };

        // ---------- 新增品牌 ----------
        DOM.confirmAddBrandBtn.onclick = async () => {
            if (!DOM.addBrandForm.checkValidity()) {
                DOM.addBrandForm.classList.add('was-validated');
                return;
            }
            const fd = new FormData();
            fd.append('action', 'addBrand');
            fd.append('brand_name', DOM.addBrandNameInput.value);
            fd.append('brand_content', DOM.addBrandContentInput.value);
            [...DOM.addBrandLogoInput.files].forEach(f => fd.append('brand_logos[]', f));
            [...DOM.addBrandNewsImageInput.files].forEach(f => fd.append('brand_news_images[]', f));
            try {
                await API.request('addBrand', fd);
                Toast.show('新增品牌成功', 'success');
                DOM.addBrandModal.hide();
                DOM.addBrandForm.reset();
                DOM.addBrandForm.classList.remove('was-validated');
                DOM.addBrandLogoPreviewContainer.innerHTML = '';
                DOM.addBrandNewsImagePreviewContainer.innerHTML = '';
                loadBrandData();
            } catch (e) {
                Toast.show('新增品牌失敗: ' + e.message, 'danger');
            }
        };

        // ---------- 圖片選擇預覽 ----------
        DOM.addBrandLogoInput.onchange = e => ImagePreview.render(DOM.addBrandLogoPreviewContainer, e.target.files);
        DOM.addBrandNewsImageInput.onchange = e => ImagePreview.render(DOM.addBrandNewsImagePreviewContainer, e.target.files);
        DOM.editBrandLogoInput.onchange = e => ImagePreview.render(DOM.editBrandLogoPreviewContainer, e.target.files);
        DOM.editBrandNewsImageInput.onchange = e => ImagePreview.render(DOM.editBrandNewsImagePreviewContainer, e.target.files);

        // ---------- 編輯/刪除 品牌按鈕 ----------
        DOM.brandTableBody.onclick = async e => {
            const btn = e.target.closest('.edit-brand-btn, .delete-brand-btn');
            if (!btn) return;
            const brandId = btn.dataset.id;
            if (btn.classList.contains('edit-brand-btn')) {
                // 編輯品牌
                editState = { brandId, deletedLogos: [], deletedNews: [], originalLogos: [], originalNews: [] };
                try {
                    const brandData = await API.request('getBrandDetails', { brand_id: brandId }, 'GET');
                    DOM.editBrandIdInput.value = brandData.brand_id;
                    DOM.editBrandNameInput.value = brandData.brand_name;
                    DOM.editBrandContentInput.value = brandData.brand_content;
                    editState.originalLogos = brandData.logos || [];
                    editState.originalNews = brandData.news_images || [];
                    ImagePreview.renderExisting(DOM.existingBrandLogosContainer, editState.originalLogos, 'Logo');
                    ImagePreview.renderExisting(DOM.existingBrandNewsImagesContainer, editState.originalNews, '最新消息圖片');
                    DOM.editBrandLogoPreviewContainer.innerHTML = '';
                    DOM.editBrandLogoInput.value = '';
                    DOM.editBrandNewsImagePreviewContainer.innerHTML = '';
                    DOM.editBrandNewsImageInput.value = '';
                    DOM.editBrandModal.show();
                } catch (e) {
                    Toast.show('載入品牌資料失敗: ' + e.message, 'danger');
                }
            } else if (btn.classList.contains('delete-brand-btn')) {
                // 刪除品牌
                editState.brandId = brandId;
                DOM.deleteBrandName.textContent = btn.dataset.name;
                DOM.deleteBrandConfirmModal.show();
            }
        };

        // ---------- 編輯時 刪除/恢復 LOGO ----------
        DOM.existingBrandLogosContainer.onclick = e => {
            const btn = e.target.closest('.remove-image-btn');
            if (!btn) return;
            const card = btn.closest('.card');
            const src = btn.dataset.src;
            card.classList.toggle('image-deleted-preview');
            btn.classList.toggle('btn-outline-danger');
            btn.classList.toggle('btn-outline-secondary');
            btn.innerHTML = card.classList.contains('image-deleted-preview') ? '<i class="bi bi-arrow-counterclockwise"></i> 恢復' : '<i class="bi bi-trash"></i> 刪除';
            if (card.classList.contains('image-deleted-preview')) {
                if (!editState.deletedLogos.includes(src)) editState.deletedLogos.push(src);
            } else {
                editState.deletedLogos = editState.deletedLogos.filter(s => s !== src);
            }
        };

        // ---------- 編輯時 刪除/恢復 最新消息圖片 ----------
        DOM.existingBrandNewsImagesContainer.onclick = e => {
            const btn = e.target.closest('.remove-image-btn');
            if (!btn) return;
            const card = btn.closest('.card');
            const src = btn.dataset.src;
            card.classList.toggle('image-deleted-preview');
            btn.classList.toggle('btn-outline-danger');
            btn.classList.toggle('btn-outline-secondary');
            btn.innerHTML = card.classList.contains('image-deleted-preview') ? '<i class="bi bi-arrow-counterclockwise"></i> 恢復' : '<i class="bi bi-trash"></i> 刪除';
            if (card.classList.contains('image-deleted-preview')) {
                if (!editState.deletedNews.includes(src)) editState.deletedNews.push(src);
            } else {
                editState.deletedNews = editState.deletedNews.filter(s => s !== src);
            }
        };

        // ---------- 儲存編輯品牌 ----------
        DOM.saveBrandBtn.onclick = async () => {
            if (!DOM.editBrandForm.checkValidity()) {
                DOM.editBrandForm.classList.add('was-validated');
                return;
            }
            const fd = new FormData();
            fd.append('action', 'updateBrand');
            fd.append('brand_id', DOM.editBrandIdInput.value);
            fd.append('brand_name', DOM.editBrandNameInput.value);
            fd.append('brand_content', DOM.editBrandContentInput.value);
            [...DOM.editBrandLogoInput.files].forEach(f => fd.append('add_logos[]', f));
            fd.append('delete_logos', JSON.stringify(editState.deletedLogos));
            [...DOM.editBrandNewsImageInput.files].forEach(f => fd.append('add_news_images[]', f));
            fd.append('delete_news_images', JSON.stringify(editState.deletedNews));
            try {
                await API.request('updateBrand', fd);
                Toast.show('品牌更新成功', 'success');
                DOM.editBrandModal.hide();
                DOM.editBrandForm.classList.remove('was-validated');
                loadBrandData();
            } catch (e) {
                Toast.show('更新品牌失敗: ' + e.message, 'danger');
            }
        };

        // ---------- 確認刪除品牌 ----------
        DOM.confirmDeleteBrandBtn.onclick = async () => {
            try {
                await API.request('deleteBrand', { brand_id: editState.brandId });
                Toast.show('品牌刪除成功', 'success');
                DOM.deleteBrandConfirmModal.hide();
                loadBrandData();
            } catch (e) {
                Toast.show('刪除品牌失敗: ' + e.message, 'danger');
            }
        };

        // ---------- 初始化 ----------
        document.addEventListener('DOMContentLoaded', loadBrandData);
    })();
</script>