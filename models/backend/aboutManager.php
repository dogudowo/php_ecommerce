<?php
// 引入數據庫連接
require_once __DIR__ . '/../../api/db.php';
require_once __DIR__ . '/functions/about/companyFunctions.php';
require_once __DIR__ . '/functions/about/storeFunctions.php';

// 獲取公司資料
$company = CompanyFunctions::getCompanyInfo();
// 獲取所有門市資料
$stores = StoreFunctions::getAllStores();
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>關於我們頁面管理</h3>
    </div>

    <!-- 公司資料管理區塊 -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4>公司資料管理</h4>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal"
                    data-bs-target="#addCompanyModal">新增公司</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>統一編號</th>
                            <th>公司名稱</th>
                            <th>電子郵件</th>
                            <th>電話</th>
                            <th>傳真</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($company as $comp): ?>
                            <tr>
                                <td><?= $comp['tax_id'] ?></td>
                                <td><?= $comp['name'] ?></td>
                                <td><?= $comp['email'] ?></td>
                                <td><?= $comp['phone'] ?></td>
                                <td><?= $comp['fax'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-company"
                                        data-id="<?= $comp['tax_id'] ?>">編輯</button>
                                    <button class="btn btn-sm btn-danger delete-company"
                                        data-id="<?= $comp['tax_id'] ?>">刪除</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 門市資料管理區塊 -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4>門市資料管理</h4>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal"
                    data-bs-target="#addStoreModal">新增門市</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>統一編號</th>
                            <th>門市名稱</th>
                            <th>電話</th>
                            <th>地址</th>
                            <th>營業時間</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stores as $store): ?>
                            <tr>
                                <td><?= $store['tax_id'] ?></td>
                                <td><?= $store['name'] ?></td>
                                <td><?= $store['phone'] ?></td>
                                <td><?= $store['location'] ?></td>
                                <td><?= $store['opening_time'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-store"
                                        data-id="<?= $store['tax_id'] ?>">編輯</button>
                                    <button class="btn btn-sm btn-danger delete-store"
                                        data-id="<?= $store['tax_id'] ?>">刪除</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 新增公司Modal -->
<div class="modal fade" id="addCompanyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新增公司</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCompanyForm">
                    <div class="mb-3">
                        <label for="company_tax_id" class="form-label">統一編號</label>
                        <input type="text" class="form-control" id="company_tax_id" name="tax_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_name" class="form-label">公司名稱</label>
                        <input type="text" class="form-control" id="company_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_email" class="form-label">電子郵件</label>
                        <textarea class="form-control" id="company_email" name="email" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="company_phone" class="form-label">電話</label>
                        <input type="text" class="form-control" id="company_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="company_fax" class="form-label">傳真</label>
                        <input type="text" class="form-control" id="company_fax" name="fax">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveCompanyBtn">儲存</button>
            </div>
        </div>
    </div>
</div>

<!-- 新增門市Modal -->
<div class="modal fade" id="addStoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新增門市</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStoreForm">
                    <div class="mb-3">
                        <label for="store_name" class="form-label">門市名稱</label>
                        <input type="text" class="form-control" id="store_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="store_phone" class="form-label">電話</label>
                        <input type="text" class="form-control" id="store_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="store_location" class="form-label">地址</label>
                        <input type="text" class="form-control" id="store_location" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label for="store_opening_time" class="form-label">營業時間</label>
                        <textarea class="form-control" id="store_opening_time" name="opening_time" rows="3"
                            required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="store_map_url" class="form-label">地圖連結</label>
                        <input type="text" class="form-control" id="store_map_url" name="map_url">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveStoreBtn">儲存</button>
            </div>
        </div>
    </div>
</div>

<!-- 編輯公司Modal -->
<div class="modal fade" id="editCompanyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">編輯公司</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCompanyForm">
                    <input type="hidden" id="edit_company_tax_id" name="tax_id">
                    <div class="mb-3">
                        <label for="edit_company_name" class="form-label">公司名稱</label>
                        <input type="text" class="form-control" id="edit_company_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_company_email" class="form-label">電子郵件</label>
                        <textarea class="form-control" id="edit_company_email" name="email" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_company_phone" class="form-label">電話</label>
                        <input type="text" class="form-control" id="edit_company_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="edit_company_fax" class="form-label">傳真</label>
                        <input type="text" class="form-control" id="edit_company_fax" name="fax">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="updateCompanyBtn">更新</button>
            </div>
        </div>
    </div>
</div>

<!-- 廣告連結管理區塊 -->
<div class="card">
    <div class="card-header bg-info text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-link-45deg me-2"></i>廣告連結管理</h4>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">廣告連結</label>
            <div class="input-group">
                <input type="text" class="form-control" id="currentAdLink"
                    value="<?php echo json_decode(file_get_contents(__DIR__ . '/../../src/adLink.json'))->adLink; ?>">
            </div>
        </div>
        <button type="button" class="btn btn-primary" id="saveAdLinkBtn">
            <i class="bi bi-save me-1"></i>儲存變更
        </button>
    </div>
</div>
<script>
    $(document).ready(function () {
        // 儲存廣告連結
        $('#saveAdLinkBtn').click(async function () {
            const newLink = $('#currentAdLink').val();

            try {
                const response = await fetch('models/backend/functions/about/updateAdLink.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ adLink: newLink })
                });

                const result = await response.json();

                if (result.success) {
                    showToast('廣告連結更新成功！', 'success');
                } else {
                    showToast('更新失敗: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error(error);
                showToast('儲存時發生錯誤，請稍後再試', 'danger');
            }
        });

        function showToast(message, type = 'info') {
            const toastId = 'toast-' + Date.now();
            const toastClass = type === 'success' ? 'bg-success' : (type === 'danger' ? 'bg-danger' : 'bg-info');
            const toastIcon = type === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' :
                (type === 'danger' ? '<i class="bi bi-x-circle-fill me-2"></i>' :
                    '<i class="bi bi-info-circle-fill me-2"></i>');

            const $toast = $(`
                <div class="toast align-items-center text-white ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${toastIcon} ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `);

            $('.toast-container').append($toast);
            const toast = new bootstrap.Toast($toast[0]);
            toast.show();

            $toast.on('hidden.bs.toast', function () {
                $(this).remove();
            });
        }
    });
</script>

<!-- 編輯門市Modal -->
<div class="modal fade" id="editStoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">編輯門市</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editStoreForm">
                    <input type="hidden" id="edit_store_tax_id" name="tax_id">
                    <div class="mb-3">
                        <label for="edit_store_name" class="form-label">門市名稱</label>
                        <input type="text" class="form-control" id="edit_store_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_store_phone" class="form-label">電話</label>
                        <input type="text" class="form-control" id="edit_store_phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_store_location" class="form-label">地址</label>
                        <input type="text" class="form-control" id="edit_store_location" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_store_opening_time" class="form-label">營業時間</label>
                        <textarea class="form-control" id="edit_store_opening_time" name="opening_time" rows="3"
                            required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_store_map_url" class="form-label">地圖連結</label>
                        <input type="text" class="form-control" id="edit_store_map_url" name="map_url">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="updateStoreBtn">更新</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // 公司資料表單提交
        $('#companyForm').submit(function (e) {
            e.preventDefault();
            $.ajax({
                url: 'models/backend/functions/about/updateCompany.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    if (response.success) {
                        alert('公司資料更新成功');
                    } else {
                        alert('更新失敗: ' + response.message);
                    }
                },
                error: function () {
                    alert('發生錯誤，請稍後再試');
                }
            });
        });

        // 新增門市
        $('#saveStoreBtn').click(function () {
            $.ajax({
                url: 'models/backend/functions/about/addStore.php',
                method: 'POST',
                data: $('#addStoreForm').serialize(),
                success: function (response) {
                    if (response.success) {
                        alert('門市新增成功');
                        location.reload();
                    } else {
                        alert('新增失敗: ' + response.message);
                    }
                },
                error: function () {
                    alert('發生錯誤，請稍後再試');
                }
            });
        });

        // 編輯門市
        $('.edit-store').click(function () {
            const tax_id = $(this).data('id');
            $.ajax({
                url: 'models/backend/functions/about/getStore.php',
                method: 'GET',
                data: { tax_id: tax_id },
                success: function (response) {
                    if (response.success) {
                        $('#edit_store_tax_id').val(response.data.tax_id);
                        $('#edit_store_name').val(response.data.name);
                        $('#edit_store_phone').val(response.data.phone);
                        $('#edit_store_location').val(response.data.location);
                        $('#edit_store_opening_time').val(response.data.opening_time);
                        $('#edit_store_map_url').val(response.data.url);
                        $('#editStoreModal').modal('show');
                    } else {
                        alert('取得門市資料失敗: ' + response.message);
                    }
                },
                error: function () {
                    alert('發生錯誤，請稍後再試');
                }
            });
        });

        // 更新門市
        $('#updateStoreBtn').click(function () {
            $.ajax({
                url: 'models/backend/functions/about/updateStore.php',
                method: 'POST',
                data: $('#editStoreForm').serialize(),
                success: function (response) {
                    if (response.success) {
                        alert('門市更新成功');
                        location.reload();
                    } else {
                        alert('更新失敗: ' + response.message);
                    }
                },
                error: function () {
                    alert('發生錯誤，請稍後再試');
                }
            });
        });

        // 刪除門市
        $('.delete-store').click(function () {
            if (confirm('確定要刪除此門市嗎？')) {
                const tax_id = $(this).data('id');
                $.ajax({
                    url: 'models/backend/functions/about/deleteStore.php',
                    method: 'POST',
                    data: { tax_id: tax_id },
                    success: function (response) {
                        if (response.success) {
                            alert('門市刪除成功');
                            location.reload();
                        } else {
                            alert('刪除失敗: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('發生錯誤，請稍後再試');
                    }
                });
            }
        });

        // 編輯公司
        $('.edit-company').click(function () {
            const tax_id = $(this).data('id');
            $.ajax({
                url: 'models/backend/functions/about/getCompany.php',
                method: 'GET',
                data: { tax_id: tax_id },
                success: function (response) {
                    if (response.success) {
                        $('#edit_company_tax_id').val(response.data.tax_id);
                        $('#edit_company_name').val(response.data.name);
                        $('#edit_company_email').val(response.data.email);
                        $('#edit_company_phone').val(response.data.phone);
                        $('#edit_company_fax').val(response.data.fax);
                        $('#editCompanyModal').modal('show');
                    } else {
                        alert('取得公司資料失敗: ' + response.message);
                    }
                },
                error: function () {
                    alert('發生錯誤，請稍後再試');
                }
            });
        });

        // 更新公司
        $('#updateCompanyBtn').click(function () {
            $.ajax({
                url: 'models/backend/functions/about/updateCompany.php',
                method: 'POST',
                data: $('#editCompanyForm').serialize(),
                success: function (response) {
                    if (response.success) {
                        alert('公司更新成功');
                        location.reload();
                    } else {
                        alert('更新失敗: ' + response.message);
                    }
                },
                error: function () {
                    alert('發生錯誤，請稍後再試');
                }
            });
        });

        // 新增公司
        $('#saveCompanyBtn').click(function () {
            $.ajax({
                url: 'models/backend/functions/about/addCompany.php',
                method: 'POST',
                data: $('#addCompanyForm').serialize(),
                success: function (response) {
                    if (response.success) {
                        alert('公司新增成功');
                        location.reload();
                    } else {
                        alert('新增失敗: ' + response.message);
                    }
                },
                error: function () {
                    alert('發生錯誤，請稍後再試');
                }
            });
        });

        // 刪除公司
        $('.delete-company').click(function () {
            if (confirm('確定要刪除此公司嗎？')) {
                const tax_id = $(this).data('id');
                $.ajax({
                    url: 'models/backend/functions/about/deleteCompany.php',
                    method: 'POST',
                    data: { tax_id: tax_id },
                    success: function (response) {
                        if (response.success) {
                            alert('公司刪除成功');
                            location.reload();
                        } else {
                            alert('刪除失敗: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('發生錯誤，請稍後再試');
                    }
                });
            }
        });
    });
    // 更新廣告連結
    $('#saveAdLinkBtn').click(function () {
        const adLink = $('#currentAdLink').val();
        $.ajax({
            url: 'models/backend/functions/about/updateAdLink.php',
            method: 'POST',
            data: { adLink: adLink },
            success: function (response) {
                if (response.success) {
                    const toast = new bootstrap.Toast(document.getElementById('toastSuccess'));
                    toast.show();
                } else {
                    const toast = new bootstrap.Toast(document.getElementById('toastError'));
                    toast.show();
                }
            },
            error: function () {
                const toast = new bootstrap.Toast(document.getElementById('toastError'));
                toast.show();
            }
        });
    });

    function copyToClipboard(elementId) {
        const copyText = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(copyText).then(() => {
            const toast = new bootstrap.Toast(document.getElementById('copyToast'));
            toast.show();
        });
    }
</script>