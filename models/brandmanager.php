<?php
defined('INCLUDED_FROM_ADMIN') || exit(header('Location: ../login.php'));
?>

<div class="container-fluid p-4">
    <h3 class="mb-4"><i class="bi bi-building me-2"></i>品牌管理</h3>

    <!-- 公司介紹區塊 -->
    <div class="card mb-4">
        <div class="card-header">
            公司介紹
        </div>
        <div class="card-body">
            <div id="companyIntroContent">
                <!-- 公司介紹內容將由 JS 載入 -->
            </div>
            <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal"
                data-bs-target="#editCompanyIntroModal">編輯公司介紹</button>
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
                            <th style="width: 15%;">品牌名稱</th>
                            <th style="width: 30%;">品牌介紹</th>
                            <th style="width: 15%;">操作</th>
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
                        <div id="existingBrandLogosContainer" class="mt-2 d-flex gap-2"></div>
                        <label for="editBrandLogoInput" class="form-label mt-2">新增更多 Logo (多選)</label>
                        <input type="file" class="form-control" id="editBrandLogoInput" accept="image/*" multiple>
                        <div class="mt-2 d-flex gap-2" id="editBrandLogoPreviewContainer"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">最新消息圖片</label>
                        <div id="existingBrandNewsImagesContainer" class="mt-2 d-flex gap-2"></div>
                        <label for="editBrandNewsImageInput" class="form-label mt-2">新增更多最新消息圖片 (多選)</label>
                        <input type="file" class="form-control" id="editBrandNewsImageInput" accept="image/*" multiple>
                        <div class="mt-2 d-flex gap-3" id="editBrandNewsImagePreviewContainer"></div>
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

        /* ----------  DOM 快取 ---------- */
        const brandTableBody = document.getElementById('brandTableBody');
        const companyIntroContent = document.getElementById('companyIntroContent');

        const editCompanyIntroModal = new bootstrap.Modal(document.getElementById('editCompanyIntroModal'));
        const companyIntroTextarea = document.getElementById('companyIntroTextarea');
        const saveCompanyIntroBtn = document.getElementById('saveCompanyIntroBtn');

        const addBrandModal = new bootstrap.Modal(document.getElementById('addBrandModal'));
        const addBrandForm = document.getElementById('addBrandForm');
        const addBrandNameInput = document.getElementById('addBrandNameInput');
        const addBrandContentInput = document.getElementById('addBrandContentInput');
        const addBrandLogoInput = document.getElementById('addBrandLogoInput');
        const addBrandLogoPreviewContainer = document.getElementById('addBrandLogoPreviewContainer');
        const confirmAddBrandBtn = document.getElementById('confirmAddBrandBtn');

        const addBrandNewsImageInput = document.getElementById('addBrandNewsImageInput');
        const addBrandNewsImagePreviewContainer = document.getElementById('addBrandNewsImagePreviewContainer');

        const editBrandModal = new bootstrap.Modal(document.getElementById('editBrandModal'));
        const editBrandForm = document.getElementById('editBrandForm');
        const editBrandIdInput = document.getElementById('editBrandIdInput');
        const editBrandNameInput = document.getElementById('editBrandNameInput');
        const editBrandContentInput = document.getElementById('editBrandContentInput');
        const editBrandLogoInput = document.getElementById('editBrandLogoInput');
        const editBrandLogoPreviewContainer = document.getElementById('editBrandLogoPreviewContainer');
        const existingBrandLogosContainer = document.getElementById('existingBrandLogosContainer');
        const saveBrandBtn = document.getElementById('saveBrandBtn');

        const deleteBrandConfirmModal = new bootstrap.Modal(document.getElementById('deleteBrandConfirmModal'));
        const deleteBrandName = document.getElementById('deleteBrandName');
        const confirmDeleteBrandBtn = document.getElementById('confirmDeleteBrandBtn');

        // 新增最新消息圖片相關 DOM 快取
        const editBrandNewsImageInput = document.getElementById('editBrandNewsImageInput');
        const editBrandNewsImagePreviewContainer = document.getElementById('editBrandNewsImagePreviewContainer');
        const existingBrandNewsImagesContainer = document.getElementById('existingBrandNewsImagesContainer');

        let currentEditingBrandId = null;
        let originalBrandLogos = []; // 用於追蹤編輯時現有的 Logo 圖片
        let deletedBrandLogoSrcs = []; // 用於追蹤編輯時被標記刪除的 Logo 圖片
        let originalBrandNewsImages = []; // 用於追蹤編輯時現有的最新消息圖片
        let deletedBrandNewsImageSrcs = []; // 用於追蹤編輯時被標記刪除的最新消息圖片

        /* Toast 容器 */
        const toastContainer = (() => {
            const div = document.createElement("div");
            div.className = "position-fixed bottom-0 end-0 p-3";
            div.style.zIndex = 1080; // 確保在 modal 上方
            document.body.appendChild(div);
            return div;
        })();

        /* ----------  輔助函式 ---------- */

        // 顯示 Toast 訊息
        const showToast = (message, type = 'info') => {
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            const toastElement = document.createElement('div');
            toastElement.innerHTML = toastHtml;
            toastContainer.appendChild(toastElement.firstElementChild);
            const toast = new bootstrap.Toast(toastContainer.lastElementChild);
            toast.show();
        };

        // 處理 API 請求
        const apiRequest = async (action, data = null) => {
            let url = `api/brand.php?action=${action}`;
            const options = {
                method: data instanceof FormData ? 'POST' : 'GET',
                body: data instanceof FormData ? data : null,
                headers: data && !(data instanceof FormData) ? { 'Content-Type': 'application/json' } : {}
            };

            if (data && !(data instanceof FormData)) {
                if (options.method === 'GET') {
                    // 將資料轉換為查詢字串並附加到 URL
                    const params = new URLSearchParams(data).toString();
                    url = `${url}&${params}`;
                    options.body = null; // 確保 GET 請求沒有 body
                } else {
                    options.body = JSON.stringify(data);
                }
            }

            try {
                const response = await fetch(url, options);
                const result = await response.json();

                if (!response.ok) {
                    console.error('API Error:', result.error);
                    throw new Error(result.error || 'API 請求失敗');
                }
                return result;
            } catch (error) {
                console.error('Fetch Error:', error);
                throw error;
            }
        };

        // 載入公司介紹和品牌列表
        const loadBrandData = async () => {
            try {
                const data = await apiRequest('getBrandInfo');

                // 載入公司介紹
                companyIntroContent.innerHTML = data.company_intro.map(p => `<p class="px-2 text-start mx-0">${p.replace(/\n/g, '<br>')}</p>`).join('');
                companyIntroTextarea.value = data.company_intro.join('\n\n'); // 編輯 modal 用

                // 載入品牌列表
                brandTableBody.innerHTML = ''; // 清空現有列表
                Object.keys(data.brands).forEach(brandId => {
                    const brand = data.brands[brandId];
                    const row = `
                        <tr>
                            <td>${brand.name}</td>
                            <td>${brand.content.substring(0, 100)}...</td>
                            <td>
                                <button class="btn btn-sm btn-primary me-2 edit-brand-btn" data-id="${brandId}">編輯</button>
                                <button class="btn btn-sm btn-danger delete-brand-btn" data-id="${brandId}" data-name="${brand.name}">刪除</button>
                            </td>
                        </tr>
                    `;
                    brandTableBody.innerHTML += row;
                });

            } catch (error) {
                showToast('載入品牌資料失敗: ' + error.message, 'danger');
            }
        };

        // 渲染現有圖片 (編輯時使用)
        const renderExistingImages = (containerEl, imageUrls, type) => {
            containerEl.innerHTML = ''; // 清空現有圖片顯示
            imageUrls.forEach(url => {
                const imgPath = url.replace('../', ''); // 移除 ../ 以便在前端顯示
                const col = document.createElement('div');
                col.className = 'col-3 mb-2';
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
                containerEl.appendChild(col);
            });
        };

        // 渲染新選擇的圖片預覽
        const renderNewImagePreviews = (containerEl, files) => {
            containerEl.innerHTML = ''; // 清空現有預覽
            [...files].forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const col = document.createElement('div');
                    col.className = 'col-4 col-md-3 col-lg-2 mb-2';
                    col.innerHTML = `
                        <div class="card h-100 image-preview-card">
                            <img src="${e.target.result}" class="card-img-top object-fit-cover" style="height: 100px;" alt="預覽">
                            <div class="card-body p-1 text-center">
                                <button type="button" class="btn btn-sm btn-outline-warning remove-new-image-btn">
                                    <i class="bi bi-x-circle"></i> 移除
                                </button>
                            </div>
                        </div>
                    `;
                    containerEl.appendChild(col);

                    // 移除新圖片預覽的事件處理
                    col.querySelector('.remove-new-image-btn').addEventListener('click', () => {
                        // 這裡需要更複雜的邏輯來從 FileList 中移除檔案，或者使用 DataTransfer
                        // 簡單起見，先移除 DOM 元素，後續在 FormData 中處理
                        col.remove();
                    });
                };
                reader.readAsDataURL(file);
            });
        };

        /* ----------  事件監聽 ---------- */

        // 頁面載入完成後載入資料
        document.addEventListener('DOMContentLoaded', loadBrandData);

        // 編輯公司介紹按鈕點擊
        saveCompanyIntroBtn.addEventListener('click', async () => {
            const newIntro = companyIntroTextarea.value;
            try {
                await apiRequest('updateCompanyIntro', { introduction: newIntro });
                showToast('公司介紹更新成功', 'success');
                editCompanyIntroModal.hide();
                loadBrandData(); // 重新載入以更新顯示
            } catch (error) {
                showToast('更新公司介紹失敗: ' + error.message, 'danger');
            }
        });

        // 新增品牌表單提交
        confirmAddBrandBtn.addEventListener('click', async () => {
            if (!addBrandForm.checkValidity()) {
                addBrandForm.classList.add('was-validated');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'addBrand');
            formData.append('brand_name', addBrandNameInput.value);
            formData.append('brand_content', addBrandContentInput.value);

            // 添加 Logo 圖片檔案
            [...addBrandLogoInput.files].forEach(file => {
                formData.append('brand_logos[]', file);
            });

            // 添加最新消息圖片檔案
            [...addBrandNewsImageInput.files].forEach(file => {
                formData.append('brand_news_images[]', file);
            });

            try {
                await apiRequest('addBrand', formData);
                showToast('新增品牌成功', 'success');
                addBrandModal.hide();
                addBrandForm.reset(); // 重置表單
                addBrandForm.classList.remove('was-validated');
                addBrandLogoPreviewContainer.innerHTML = ''; // 清空預覽
                loadBrandData(); // 重新載入品牌列表
            } catch (error) {
                showToast('新增品牌失敗: ' + error.message, 'danger');
            }
        });

        // 新增品牌 Logo 圖片選擇變更
        addBrandLogoInput.addEventListener('change', (event) => {
            renderNewImagePreviews(addBrandLogoPreviewContainer, event.target.files);
        });

        // 新增品牌最新消息圖片選擇
        addBrandNewsImageInput.addEventListener('change', (event) => {
            renderNewImagePreviews(addBrandNewsImagePreviewContainer, event.target.files);
        });

        // 品牌列表操作按鈕點擊 (編輯/刪除)
        brandTableBody.addEventListener('click', async (event) => {
            const target = event.target;

            // 編輯按鈕
            if (target.classList.contains('edit-brand-btn')) {
                currentEditingBrandId = target.dataset.id;
                try {
                    const brandData = await apiRequest('getBrandDetails', { brand_id: currentEditingBrandId });
                    editBrandIdInput.value = brandData.brand_id;
                    editBrandNameInput.value = brandData.brand_name;
                    editBrandContentInput.value = brandData.brand_content;

                    // 載入並顯示現有 Logo
                    originalBrandLogos = brandData.logos || [];
                    deletedBrandLogoSrcs = []; // 重置刪除列表
                    renderExistingImages(existingBrandLogosContainer, originalBrandLogos, 'Logo');

                    // 載入並顯示現有最新消息圖片
                    originalBrandNewsImages = brandData.news_images || [];
                    deletedBrandNewsImageSrcs = []; // 重置刪除列表
                    renderExistingImages(existingBrandNewsImagesContainer, originalBrandNewsImages, '最新消息圖片');

                    // 清空新圖片預覽和 input
                    editBrandLogoPreviewContainer.innerHTML = '';
                    editBrandLogoInput.value = '';

                    // 清空新最新消息圖片預覽和 input
                    editBrandNewsImagePreviewContainer.innerHTML = '';
                    editBrandNewsImageInput.value = '';

                    editBrandModal.show();
                } catch (error) {
                    showToast('載入品牌詳細資料失敗: ' + error.message, 'danger');
                }
            }

            // 刪除按鈕
            if (target.classList.contains('delete-brand-btn')) {
                currentEditingBrandId = target.dataset.id;
                const brandName = target.dataset.name;
                deleteBrandName.textContent = brandName;
                deleteBrandConfirmModal.show();
            }
        });

        // 編輯品牌模態框中的現有圖片刪除按鈕點擊
        existingBrandLogosContainer.addEventListener('click', (event) => {
            const target = event.target;
            if (target.closest('.remove-image-btn')) {
                const btn = target.closest('.remove-image-btn');
                const card = btn.closest('.card');
                const imageUrl = btn.dataset.src; // 這是完整的後端路徑，例如 ../imgs/brand/1/logo1.png

                // 標記為刪除 (視覺上改變樣式)
                card.classList.toggle('image-deleted-preview');
                btn.classList.toggle('btn-outline-danger');
                btn.classList.toggle('btn-outline-secondary');
                btn.innerHTML = card.classList.contains('image-deleted-preview') ? '<i class="bi bi-arrow-counterclockwise"></i> 恢復' : '<i class="bi bi-trash"></i> 刪除';

                // 更新 deletedBrandLogoSrcs 列表
                if (card.classList.contains('image-deleted-preview')) {
                    if (!deletedBrandLogoSrcs.includes(imageUrl)) {
                        deletedBrandLogoSrcs.push(imageUrl);
                    }
                } else {
                    const index = deletedBrandLogoSrcs.indexOf(imageUrl);
                    if (index > -1) {
                        deletedBrandLogoSrcs.splice(index, 1);
                    }
                }
            }
        });

        // 編輯品牌模態框中的新增圖片選擇變更
        editBrandLogoInput.addEventListener('change', (event) => {
            renderNewImagePreviews(editBrandLogoPreviewContainer, event.target.files);
        });

        // 編輯品牌模態框中的現有最新消息圖片刪除按鈕點擊
        existingBrandNewsImagesContainer.addEventListener('click', (event) => {
            const target = event.target;
            if (target.closest('.remove-image-btn')) {
                const btn = target.closest('.remove-image-btn');
                const card = btn.closest('.card');
                const imageUrl = btn.dataset.src; // 這是完整的後端路徑，例如 ../imgs/brand/1/newAbout/image1.png

                // 標記為刪除 (視覺上改變樣式)
                card.classList.toggle('image-deleted-preview');
                btn.classList.toggle('btn-outline-danger');
                btn.classList.toggle('btn-outline-secondary');
                btn.innerHTML = card.classList.contains('image-deleted-preview') ? '<i class="bi bi-arrow-counterclockwise"></i> 恢復' : '<i class="bi bi-trash"></i> 刪除';

                // 更新 deletedBrandNewsImageSrcs 列表
                if (card.classList.contains('image-deleted-preview')) {
                    if (!deletedBrandNewsImageSrcs.includes(imageUrl)) {
                        deletedBrandNewsImageSrcs.push(imageUrl);
                    }
                } else {
                    const index = deletedBrandNewsImageSrcs.indexOf(imageUrl);
                    if (index > -1) {
                        deletedBrandNewsImageSrcs.splice(index, 1);
                    }
                }
            }
        });

        // 編輯品牌模態框中的新增最新消息圖片選擇變更
        editBrandNewsImageInput.addEventListener('change', (event) => {
            renderNewImagePreviews(editBrandNewsImagePreviewContainer, event.target.files);
        });

        // 儲存編輯品牌按鈕點擊
        saveBrandBtn.addEventListener('click', async () => {
            if (!editBrandForm.checkValidity()) {
                editBrandForm.classList.add('was-validated');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'updateBrand');
            formData.append('brand_id', editBrandIdInput.value);
            formData.append('brand_name', editBrandNameInput.value);
            formData.append('brand_content', editBrandContentInput.value);

            // 添加新的 Logo 圖片檔案
            [...editBrandLogoInput.files].forEach(file => {
                formData.append('add_logos[]', file);
            });

            // 添加要刪除的 Logo 圖片路徑
            formData.append('delete_logos', JSON.stringify(deletedBrandLogoSrcs));

            // 添加新的最新消息圖片檔案
            [...editBrandNewsImageInput.files].forEach(file => {
                formData.append('add_news_images[]', file);
            });

            // 添加要刪除的最新消息圖片路徑
            formData.append('delete_news_images', JSON.stringify(deletedBrandNewsImageSrcs));

            try {
                await apiRequest('updateBrand', formData);
                showToast('品牌更新成功', 'success');
                editBrandModal.hide();
                editBrandForm.classList.remove('was-validated');
                loadBrandData(); // 重新載入品牌列表
            } catch (error) {
                showToast('更新品牌失敗: ' + error.message, 'danger');
            }
        });

        // 確認刪除品牌按鈕點擊
        confirmDeleteBrandBtn.addEventListener('click', async () => {
            try {
                await apiRequest('deleteBrand', { brand_id: currentEditingBrandId });
                showToast('品牌刪除成功', 'success');
                deleteBrandConfirmModal.hide();
                loadBrandData(); // 重新載入品牌列表
            } catch (error) {
                showToast('刪除品牌失敗: ' + error.message, 'danger');
            }
        });

    })();
</script>