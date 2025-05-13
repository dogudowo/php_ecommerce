<div class="container-fluid p-0">
    <!-- 頁首操作列 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-award me-2"></i>品牌介紹頁面管理</h3>
    </div>

    <!-- 品牌介紹列表 -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-text-paragraph me-2"></i>品牌介紹編輯</h5>
            <button class="btn btn-success" id="addIntroBtn">
                <i class="bi bi-plus-lg me-1"></i>新增段落
            </button>
        </div>
        <div class="card-body" id="introContainer">
            <div class="spinner-wrapper text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>

    <!-- 代理品牌列表 -->
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

<!-- ==== 品牌介紹 Modals ==== -->
<!-- 刪除介紹 Modal -->
<div class="modal fade" id="deleteIntroModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
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
        </div>
    </div>
</div>

<!-- 編輯介紹 Modal -->
<div class="modal fade" id="editIntroModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
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
        </div>
    </div>
</div>

<!-- 新增介紹 Modal -->
<div class="modal fade" id="addIntroModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新增品牌介紹</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" rows="8" id="newIntroContent"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveNewIntroBtn">新增段落</button>
            </div>
        </div>
    </div>
</div>

<!-- ==== 代理品牌 Modals ==== -->
<!-- 刪除品牌 Modal -->
<div class="modal fade" id="deleteBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
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
        </div>
    </div>
</div>

<!-- 編輯品牌 Modal -->
<div class="modal fade" id="editBrandModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
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

                <!-- Logo 圖片上傳 (多張) -->
                <div class="mb-3">
                    <label class="form-label">品牌 Logo</label>
                    <input type="file" class="form-control" id="editBrandLogoInput" accept="image/png, image/jpeg"
                        multiple>
                    <div class="mt-2" id="editBrandLogoPreviewContainer"></div>
                    <div class="mt-2" id="existingBrandLogosContainer"></div>
                </div>

                <!-- 最新消息圖片上傳 (多張) -->
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
        </div>
    </div>
</div>

<!-- 新增品牌 Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
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
                <!-- Logo 圖片上傳 (多張) -->
                <div class="mb-3">
                    <label class="form-label">品牌 Logo</label>
                    <input type="file" class="form-control" id="addBrandLogoInput" accept="image/png, image/jpeg"
                        multiple>
                    <div class="mt-2" id="addBrandLogoPreviewContainer"></div>
                </div>

                <!-- 最新消息圖片上傳 (多張) -->
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
        </div>
    </div>
</div>

<!-- Toast 通知 -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle"></strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>

<script>
    (function ($, bootstrap) {
        'use strict';
        const API_URL = 'api/brand.php';

        function showToast(title, msg, type = 'success') {
            $('#toastTitle').text(title);
            $('#toastMessage').text(msg);
            $('#notificationToast')
                .removeClass('bg-success bg-danger bg-warning')
                .addClass(`bg-${type}`)
                .toast('show');
        }

        async function apiRequest(action, data = {}, method = 'POST') {
            let url = API_URL;
            let ajaxOptions = {
                url: url,
                method: method,
                dataType: 'json',
                data: { action, ...data }
            };

            if (method.toUpperCase() === 'GET') {
                // For GET requests, parameters are typically in the URL
                ajaxOptions.data = { action, ...data }; // jQuery handles query string for GET
            } else if (data instanceof FormData) {
                // For FormData, set processData and contentType to false
                ajaxOptions.data = data; // FormData already includes action
                ajaxOptions.processData = false;
                ajaxOptions.contentType = false;
            } else {
                // For regular POST, jQuery will handle serialization
                ajaxOptions.data = { action, ...data };
            }

            try {
                const res = await $.ajax(ajaxOptions);
                if (!res.success) throw new Error(res.message || '操作失敗');
                return res.data || res; // Assuming 'data' key for successful responses with payload
            } catch (jqXHR) {
                // Attempt to parse error from responseJSON if available
                const errorMsg = jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : (jqXHR.responseText || '操作失敗');
                throw new Error(errorMsg);
            }
        }

        const introTpl = intro => `
      <div class="card mb-3" data-id="${intro.brand_introduction_id}">
        <div class="card-body d-flex justify-content-between align-items-center">
          <p class="mb-0">${intro.introdution}</p>
          <div>
            <button class="btn btn-sm btn-outline-primary edit-intro"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger delete-intro ms-2"><i class="bi bi-trash"></i></button>
          </div>
        </div>
      </div>`;

        const brandTpl = brand => `
      <div class="card mb-3" data-id="${brand.brand_id}">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-1">${brand.brand_name}</h5>
            <p class="text-muted mb-0">${brand.brand_content}</p>
          </div>
          <div>
            <button class="btn btn-sm btn-outline-primary edit-brand"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger delete-brand ms-2"><i class="bi bi-trash"></i></button>
          </div>
        </div>
      </div>`;

        async function renderList(container, tplFn, action) {
            const $c = $(container);
            $c.html(`
        <div class="spinner-wrapper text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
        </div>`);
            try {
                const list = await apiRequest(action);
                $c.html(list.map(tplFn).join(''));
            } catch {
                showToast('錯誤', `載入 ${action} 失敗`, 'danger');
            }
        }

        const getModal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id));

        function bindEvents() {
            // ==== 品牌介紹 ====
            $('#addIntroBtn').click(() => {
                $('#newIntroContent').val('');
                getModal('addIntroModal').show();
            });

            $('#saveNewIntroBtn').click(async () => {
                const content = $('#newIntroContent').val().trim();
                if (!content) return showToast('警告', '內容不得為空', 'warning');
                try {
                    await apiRequest('create_intro', { content });
                    getModal('addIntroModal').hide();
                    await refreshIntro();
                    showToast('成功', '新增介紹段落成功');
                } catch (err) {
                    showToast('錯誤', err.message, 'danger');
                }
            });

            $('#introContainer').on('click', '.edit-intro', function () {
                const $card = $(this).closest('.card');
                const id = $card.data('id');
                $('#editIntroContent').val($card.find('p').text().trim());
                $('#editIntroModal').data('id', id);
                getModal('editIntroModal').show();
            });

            $('#saveIntroBtn').click(async () => {
                const id = $('#editIntroModal').data('id');
                const content = $('#editIntroContent').val().trim();
                try {
                    await apiRequest('update_intro', { id, content });
                    getModal('editIntroModal').hide();
                    await refreshIntro();
                    showToast('成功', '更新介紹成功');
                } catch (err) {
                    showToast('錯誤', err.message, 'danger');
                }
            });

            $('#introContainer').on('click', '.delete-intro', function () {
                const id = $(this).closest('.card').data('id');
                $('#deleteIntroId').val(id);
                getModal('deleteIntroModal').show();
            });

            $('#confirmDeleteIntroBtn').click(async () => {
                const id = $('#deleteIntroId').val();
                try {
                    await apiRequest('delete_intro', { id });
                    getModal('deleteIntroModal').hide();
                    await refreshIntro();
                    showToast('成功', '刪除段落成功');
                } catch (err) {
                    showToast('錯誤', err.message, 'danger');
                }
            });

            // ==== 代理品牌 ====
            $('#addBrandBtn').click(() => {
                $('#brandNameInput, #brandContentInput').val('');
                $('#addBrandLogoPreviewContainer, #addBrandNewsImagePreviewContainer').html('');
                $('#addBrandLogoInput, #addBrandNewsImageInput').val('');
                getModal('addBrandModal').show();
            });

            $('#createBrandBtn').click(async () => {
                const name = $('#brandNameInput').val().trim();
                const content = $('#brandContentInput').val().trim();
                if (!name) return showToast('警告', '品牌名稱不得為空', 'warning');

                const formData = new FormData();
                formData.append('action', 'create_brand');
                formData.append('brandName', name);
                formData.append('brandContent', content);

                const logoFiles = $('#addBrandLogoInput')[0].files;
                if (logoFiles && logoFiles.length > 0) {
                    Array.from(logoFiles).forEach((file, index) => {
                        formData.append(`logo_images[${index}]`, file);
                    });
                }

                const newsImageFiles = $('#addBrandNewsImageInput')[0].files;
                if (newsImageFiles && newsImageFiles.length > 0) {
                    Array.from(newsImageFiles).forEach((file, index) => {
                        formData.append(`news_images[${index}]`, file);
                    });
                }

                try {
                    await $.ajax({
                        url: API_URL,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    }).done(function (res) {
                        if (!res.success) throw new Error(res.message || '操作失敗');
                        getModal('addBrandModal').hide();
                        refreshBrand();
                        showToast('成功', '品牌新增成功');
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        throw new Error(errorThrown || textStatus);
                    });
                } catch (err) {
                    showToast('錯誤', err.message, 'danger');
                }
            });

            $('#brandContainer').on('click', '.edit-brand', function () {
                const $card = $(this).closest('.card');
                const id = $card.data('id');
                $('#editBrandNameInput').val($card.find('h5').text().trim());
                $('#editBrandContentInput').val($card.find('p').text().trim());
                $('#editBrandModal').data('id', id);
                // 清空舊的圖片預覽和列表
                $('#editBrandLogoPreviewContainer, #existingBrandLogosContainer, #editBrandNewsImagePreviewContainer, #existingBrandNewsImagesContainer').html('');
                $('#editBrandLogoInput, #editBrandNewsImageInput').val(''); // 清空檔案選擇
                loadBrandImages(id); // 加載現有圖片
                getModal('editBrandModal').show();
            });

            $('#saveBrandBtn').click(async () => {
                const id = $('#editBrandModal').data('id');
                const name = $('#editBrandNameInput').val().trim();
                const content = $('#editBrandContentInput').val().trim();
                if (!name) return showToast('警告', '品牌名稱不得為空', 'warning');

                const formData = new FormData();
                formData.append('action', 'update_brand');
                formData.append('id', id);
                formData.append('brandName', name);
                formData.append('brandContent', content);

                const logoFiles = $('#editBrandLogoInput')[0].files;
                if (logoFiles && logoFiles.length > 0) {
                    Array.from(logoFiles).forEach((file, index) => {
                        formData.append(`logo_images[${index}]`, file);
                    });
                }

                const newsImageFiles = $('#editBrandNewsImageInput')[0].files;
                if (newsImageFiles && newsImageFiles.length > 0) {
                    Array.from(newsImageFiles).forEach((file, index) => {
                        formData.append(`news_images[${index}]`, file);
                    });
                }

                try {
                    // 使用 jQuery.ajax 進行 FormData 上傳
                    await $.ajax({
                        url: API_URL,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    }).done(function (res) {
                        if (!res.success) throw new Error(res.message || '操作失敗');
                        getModal('editBrandModal').hide();
                        refreshBrand();
                        showToast('成功', '品牌更新成功');
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        throw new Error(errorThrown || textStatus);
                    });
                } catch (err) {
                    showToast('錯誤', err.message, 'danger');
                }
            });

            $('#brandContainer').on('click', '.delete-brand', function () {
                const id = $(this).closest('.card').data('id');
                $('#deleteBrandId').val(id);
                getModal('deleteBrandModal').show();
            });

            $('#confirmDeleteBrandBtn').click(async () => {
                const id = $('#deleteBrandId').val();
                try {
                    await apiRequest('delete_brand', { id });
                    getModal('deleteBrandModal').hide();
                    await refreshBrand();
                    showToast('成功', '刪除品牌成功');
                } catch (err) {
                    showToast('錯誤', err.message, 'danger');
                }
            });

            // Logo 圖片預覽 (多張)
            $('#editBrandLogoInput').on('change', function (event) {
                const previewContainer = $('#editBrandLogoPreviewContainer');
                previewContainer.html(''); // 清空舊的預覽
                const files = event.target.files;
                if (files && files.length > 0) {
                    Array.from(files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewContainer.append(`<img src="${e.target.result}" class="img-thumbnail me-2 mb-2" style="width: 100px; height: 100px; object-fit: contain;">`);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            });

            // 新增品牌Logo圖片預覽 (多張)
            $('#addBrandLogoInput').on('change', function (event) {
                const previewContainer = $('#addBrandLogoPreviewContainer');
                previewContainer.html(''); // 清空舊的預覽
                const files = event.target.files;
                if (files && files.length > 0) {
                    Array.from(files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewContainer.append(`<img src="${e.target.result}" class="img-thumbnail me-2 mb-2" style="width: 100px; height: 100px; object-fit: contain;">`);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            });

            // 最新消息圖片預覽 (多張)
            $('#editBrandNewsImageInput').on('change', function (event) {
                const previewContainer = $('#editBrandNewsImagePreviewContainer');
                previewContainer.html(''); // 清空舊的預覽
                const files = event.target.files;
                if (files && files.length > 0) {
                    Array.from(files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewContainer.append(`<img src="${e.target.result}" class="img-thumbnail me-2 mb-2" style="width: 100px; height: 100px; object-fit: contain;">`);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            });

            // 新增品牌最新消息圖片預覽 (多張)
            $('#addBrandNewsImageInput').on('change', function (event) {
                const previewContainer = $('#addBrandNewsImagePreviewContainer');
                previewContainer.html(''); // 清空舊的預覽
                const files = event.target.files;
                if (files && files.length > 0) {
                    Array.from(files).forEach(file => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewContainer.append(`<img src="${e.target.result}" class="img-thumbnail me-2 mb-2" style="width: 100px; height: 100px; object-fit: contain;">`);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            });
        }

        const refreshIntro = () => renderList('#introContainer', introTpl, 'get_intros');
        const refreshBrand = () => renderList('#brandContainer', brandTpl, 'get_brands');

        // 載入品牌圖片
        async function loadBrandImages(id) {
            try {
                const res = await apiRequest('get_brand_images', { brand_id: id });

                // 顯示現有Logo圖片
                const $logoContainer = $('#existingBrandLogosContainer');
                $logoContainer.html('<h6 class="mt-3">現有Logo圖片</h6><div class="row g-2" id="existingLogosRow"></div>');

                if (res.logos && res.logos.length > 0) {
                    res.logos.forEach(logo => {
                        $('#existingLogosRow').append(`
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100">
                            <img src="imgs/brand/${id}/${logo}" class="card-img-top" alt="品牌Logo">
                            <div class="card-footer p-1 bg-white text-center">
                                <button class="btn btn-sm btn-danger delete-brand-image" 
                                    data-image-type="logo" data-image-name="${logo}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `);
                    });
                } else {
                    $('#existingLogosRow').html('<div class="col-12 text-muted">沒有Logo圖片</div>');
                }

                // 顯示現有最新消息圖片
                const $newsContainer = $('#existingBrandNewsImagesContainer');
                $newsContainer.html('<h6 class="mt-3">現有最新消息圖片</h6><div class="row g-2" id="existingNewsRow"></div>');

                if (res.news_images && res.news_images.length > 0) {
                    res.news_images.forEach(news => {
                        $('#existingNewsRow').append(`
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card h-100">
                            <img src="imgs/brand/${id}/newAbout/${news}" class="card-img-top" alt="最新消息圖片">
                            <div class="card-footer p-1 bg-white text-center">
                                <button class="btn btn-sm btn-danger delete-brand-image" 
                                    data-image-type="news" data-image-name="${news}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `);
                    });
                } else {
                    $('#existingNewsRow').html('<div class="col-12 text-muted">沒有最新消息圖片</div>');
                }

                // 綁定刪除圖片事件
                $('.delete-brand-image').click(async function () {
                    const imageType = $(this).data('image-type');
                    const imageName = $(this).data('image-name');

                    try {
                        await apiRequest('delete_brand_image', {
                            brand_id: id,
                            image_type: imageType,
                            image_name: imageName
                        });
                        showToast('成功', '圖片刪除成功');
                        loadBrandImages(id); // 重新載入圖片列表
                    } catch (err) {
                        showToast('錯誤', err.message, 'danger');
                    }
                });

            } catch (err) {
                console.error(err);
                showToast('錯誤', '載入品牌圖片失敗', 'danger');
            }
        }

        // 預覽選擇的圖片
        function previewImage(fileInputId, previewContainerId) {
            const input = document.getElementById(fileInputId);
            const previewContainer = $(previewContainerId);
            previewContainer.html('');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewContainer.html(`<img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: contain;">`);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).ready(() => {
            bindEvents();
            refreshIntro();
            refreshBrand();

            $('#editBrandLogoInput').change(() => previewImage('editBrandLogoInput', '#editBrandLogoPreviewContainer'));
            $('#editBrandNewsImageInput').change(() => previewImage('editBrandNewsImageInput', '#editBrandNewsImagePreviewContainer'));

            // 刪除品牌圖片事件綁定 (動態綁定到Modal內部)
            $('#editBrandModal').on('click', '.delete-brand-image', async function () {
                const brandId = $(this).data('brand-id');
                const imageType = $(this).data('type');
                const imageName = $(this).data('name');

                if (!confirm(`確定要刪除這個${imageType === 'logo' ? 'Logo' : '最新消息圖片'}嗎？`)) return;

                try {
                    await apiRequest('delete_brand_image', { brand_id: brandId, image_type: imageType, image_name: imageName });
                    showToast('成功', '圖片刪除成功');
                    loadBrandImages(brandId); // 重新載入圖片列表
                } catch (err) {
                    showToast('錯誤', '圖片刪除失敗: ' + err.message, 'danger');
                }
            });
        });

    })(jQuery, bootstrap);
</script>