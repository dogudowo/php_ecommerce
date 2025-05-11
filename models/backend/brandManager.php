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
                    <textarea class="form-control" rows="5" id="editBrandContentInput"></textarea>
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
        const API_URL = 'models/backend/functions/brand/brandIntroductionHandler.php';

        function showToast(title, msg, type = 'success') {
            $('#toastTitle').text(title);
            $('#toastMessage').text(msg);
            $('#notificationToast')
                .removeClass('bg-success bg-danger bg-warning')
                .addClass(`bg-${type}`)
                .toast('show');
        }

        async function apiRequest(action, data = {}) {
            const res = await $.post(API_URL, { action, ...data });
            if (!res.success) throw new Error(res.message || '操作失敗');
            return res.data || res;
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
                getModal('addBrandModal').show();
            });

            $('#createBrandBtn').click(async () => {
                const name = $('#brandNameInput').val().trim();
                const content = $('#brandContentInput').val().trim();
                if (!name) return showToast('警告', '品牌名稱不得為空', 'warning');
                try {
                    await apiRequest('create_brand', { brandName: name, brandContent: content });
                    getModal('addBrandModal').hide();
                    await refreshBrand();
                    showToast('成功', '品牌新增成功');
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
                getModal('editBrandModal').show();
            });

            $('#saveBrandBtn').click(async () => {
                const id = $('#editBrandModal').data('id');
                const name = $('#editBrandNameInput').val().trim();
                const content = $('#editBrandContentInput').val().trim();
                if (!name) return showToast('警告', '品牌名稱不得為空', 'warning');
                try {
                    await apiRequest('update_brand', { id, brandName: name, brandContent: content });
                    getModal('editBrandModal').hide();
                    await refreshBrand();
                    showToast('成功', '品牌更新成功');
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
        }

        const refreshIntro = () => renderList('#introContainer', introTpl, 'get_intros');
        const refreshBrand = () => renderList('#brandContainer', brandTpl, 'get_brands');

        $(document).ready(() => {
            bindEvents();
            refreshIntro();
            refreshBrand();
        });
    })(jQuery, bootstrap);
</script>