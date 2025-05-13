<?php
// 引入資料庫連線
require_once __DIR__ . '/../../api/db.php';

// 讀取廣告連結設定
$adLinkObj = json_decode(file_get_contents(__DIR__ . '/../../src/adLink.json'));
$adLink = $adLinkObj->adLink;
?>

<div class="container-fluid p-0 mt-4">
    <!-- 頁面標題與操作區 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>關於我們頁面管理</h3>
    </div>

    <!-- 公司資料管理區塊 -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4>公司資料管理</h4>
                <!-- 新增公司按鈕 -->
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal" data-entity="company">
                    <i class="bi bi-plus-lg me-1"></i>新增公司
                </button>
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
                    <tbody id="companyTableBody">
                        <!-- AJAX 載入公司資料 -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 門市資料管理區塊 -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4>門市資料管理</h4>
                <!-- 新增門市按鈕 -->
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal" data-entity="store">
                    <i class="bi bi-plus-lg me-1"></i>新增門市
                </button>
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
                    <tbody id="storeTableBody">
                        <!-- AJAX 載入門市資料 -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 廣告連結管理區塊 -->
    <div class="card">
        <div class="card-header">
            <h4><i class="bi bi-link-45deg me-2"></i>廣告連結管理</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">廣告連結</label>
                <!-- 輸入框預設顯示目前連結 -->
                <input type="text" class="form-control" id="adLinkInput" value="<?= htmlspecialchars($adLink) ?>">
            </div>
            <!-- 儲存廣告連結按鈕 -->
            <button class="btn btn-primary" id="saveAdLinkBtn">儲存變更</button>
        </div>
    </div>
</div>

<!-- 全域彈跳框：用於新增或編輯 -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <!-- 動態顯示標題 -->
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- 動態生成的表單 -->
                <form id="entityForm"></form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <!-- 動態設定按鈕文字與功能 -->
                <button type="button" class="btn btn-primary" id="saveEntityBtn"></button>
            </div>
        </div>
    </div>
</div>

<!-- Toast 容器：用於顯示通知 -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<script>
    // 處理輸入文字的 XSS 過濾與 iframe 連結提取
    function escapeHtml(text, fieldName = '') {
        // 只處理公司資料中的 url 欄位
        if (fieldName === 'url') {
            const iframeRegex = /<iframe[^>]+src="([^"]+)"[^>]*>/gi;
            const match = iframeRegex.exec(text);
            if (match && match[1]) {
                return match[1];
            }
        }
        // 預設處理其他 HTML 標籤
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    $(function () {
        // 欄位標籤對照表
        const labelMap = {
            tax_id: '統一編號',
            name: { company: '公司名稱', store: '門市名稱' },
            email: '電子郵件',
            phone: '電話',
            fax: '傳真',
            location: '地址',
            opening_time: '營業時間',
            url: '地圖連結'
        };
        // API 路徑基底
        const apiBaseUrl = 'models/backend/functions/about/';
        // 不同實體對應的 API
        const apiMap = {
            company: {
                getAll: 'companyFunctions.php',
                get: 'companyFunctions.php',
                add: 'companyFunctions.php',
                update: 'companyFunctions.php',
                delete: 'companyFunctions.php'
            },
            store: {
                getAll: 'storeFunctions.php',
                get: 'storeFunctions.php',
                add: 'storeFunctions.php',
                update: 'storeFunctions.php',
                delete: 'storeFunctions.php'
            }
        };

        // 載入表格資料
        function loadTableData(entity, tableBodyId) {
            const config = apiMap[entity];
            if (!config) return;

            let ajaxUrl;
            let ajaxData = {};
            let ajaxType = 'GET';

            if (entity === 'company') {
                ajaxUrl = apiBaseUrl + config.getAll;
                ajaxData = { action: 'getAllCompanies' };
            } else if (entity === 'store') {
                ajaxUrl = apiBaseUrl + config.getAll;
                ajaxData = { action: 'getAllStores' };
            } else {
                return;
            }

            $.ajax({
                url: ajaxUrl,
                type: ajaxType,
                data: ajaxData,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const tableBody = $('#' + tableBodyId).empty();
                        response.data.forEach(function (row) {
                            let cells = '';
                            if (entity === 'company') {
                                cells += `<td>${row.tax_id || ''}</td>`;
                                cells += `<td>${row.name || ''}</td>`;
                                cells += `<td>${row.email || ''}</td>`;
                                cells += `<td>${row.phone || ''}</td>`;
                                cells += `<td>${row.fax || ''}</td>`;
                            } else if (entity === 'store') {
                                cells += `<td>${row.tax_id || ''}</td>`;
                                cells += `<td>${row.name || ''}</td>`;
                                cells += `<td>${row.phone || ''}</td>`;
                                cells += `<td>${row.location || ''}</td>`;
                                cells += `<td>${escapeHtml(row.opening_time || '', 'url')}</td>`;
                            }
                            cells += `<td>
                                        <button class="btn btn-sm btn-warning btn-action" data-op="edit" data-entity="${entity}" data-id="${escapeHtml(row.tax_id)}">編輯</button>
                                        <button class="btn btn-sm btn-danger btn-action" data-op="delete" data-entity="${entity}" data-id="${escapeHtml(row.tax_id)}">刪除</button>
                                    </td>`;
                            tableBody.append(`<tr>${cells}</tr>`);
                        });
                    } else {
                        showToast('載入' + (entity === 'company' ? '公司' : '門市') + '資料失敗: ' + escapeHtml(response.message), 'danger');
                    }
                },
                fail: function () {
                    showToast('載入' + (entity === 'company' ? '公司' : '門市') + '資料時發生錯誤', 'danger');
                }
            });
        }

        // 初始化載入公司與門市資料
        loadTableData('company', 'companyTableBody');
        loadTableData('store', 'storeTableBody');

        // 監聽編輯按鈕動態生成表單與儲存行為
        $(document).on('click', '.btn-action[data-op="edit"]', function () {
            const entity = $(this).data('entity');
            const id = $(this).data('id');
            const title = '編輯' + (entity === 'company' ? '公司' : '門市');
            $('#modalTitle').text(title);
            $('#saveEntityBtn').text('更新');

            const form = $('#entityForm').empty();
            const fields = entity === 'company'
                ? ['tax_id', 'name', 'email', 'phone', 'fax']
                : ['name', 'phone', 'location', 'opening_time', 'url']; // 移除 'tax_id' for store edit

            fields.forEach(function (f) {
                let fieldName = (entity === 'store' && f === 'url') ? 'map_url' : f;
                const labelText = typeof labelMap[f] === 'object' ? labelMap[f][entity] : labelMap[f];
                const label = $('<label>').addClass('form-label').attr('for', f).text(labelText);
                const isTextarea = (f === 'opening_time');
                let input;

                if (f === 'tax_id') {
                    input = $('<input>').attr({ type: 'text', id: f, name: fieldName }).prop('readonly', true);
                } else if (isTextarea) {
                    input = $('<textarea>').attr({ id: f, name: fieldName, rows: 4 });
                } else {
                    input = $('<input>').attr({ type: 'text', id: f, name: fieldName });
                }
                input.addClass('form-control');
                form.append($('<div>').addClass('mb-3').append(label, input));
            });

            let ajaxUrl;
            let ajaxData = {};

            if (entity === 'company') {
                ajaxUrl = apiBaseUrl + apiMap[entity].get;
                ajaxData = { action: 'getCompany', tax_id: id };
            } else if (entity === 'store') {
                ajaxUrl = apiBaseUrl + apiMap[entity].get;
                ajaxData = { action: 'getStore', tax_id: id };
            } else {
                return;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                data: ajaxData,
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        Object.entries(res.data).forEach(function ([k, v]) {
                            if ($('#' + k).length) $('#' + k).val(v);
                        });
                    } else {
                        showToast('載入編輯資料失敗: ' + escapeHtml(res.message), 'danger');
                        $('#addModal').modal('hide');
                    }
                },
                fail: function () {
                    showToast('載入編輯資料時發生錯誤', 'danger');
                    $('#addModal').modal('hide');
                }
            });

            $('#saveEntityBtn').off('click').on('click', function () {
                saveEntity(entity, 'update', id);
            });
            $('#addModal').modal('show');
        });

        // 監聽新增按鈕
        $('.btn[data-bs-target="#addModal"]').on('click', function () {
            const entity = $(this).data('entity');
            const title = '新增' + (entity === 'company' ? '公司' : '門市');
            $('#modalTitle').text(title);
            $('#saveEntityBtn').text('儲存');

            const form = $('#entityForm').empty();
            const fields = entity === 'company'
                ? ['tax_id', 'name', 'email', 'phone', 'fax']
                : ['name', 'phone', 'location', 'opening_time', 'url'];

            fields.forEach(function (f) {
                let fieldName = (entity === 'store' && f === 'url') ? 'map_url' : f;
                const labelText = typeof labelMap[f] === 'object' ? labelMap[f][entity] : labelMap[f];
                const label = $('<label>').addClass('form-label').attr('for', f).text(labelText);
                const isTextarea = (f === 'opening_time');
                let input;

                if (isTextarea) {
                    input = $('<textarea>').attr({ id: f, name: fieldName, rows: 4 });
                } else {
                    input = $('<input>').attr({ type: 'text', id: f, name: fieldName });
                }
                input.addClass('form-control');
                form.append($('<div>').addClass('mb-3').append(label, input));
            });

            $('#saveEntityBtn').off('click').on('click', function () {
                saveEntity(entity, 'add');
            });
            $('#addModal').modal('show');
        });

        // 儲存函式：處理新增與更新
        function saveEntity(entity, op, id = null) {
            let formData = $('#entityForm').serializeArray();
            let ajaxUrl;
            let actionValue;

            if (entity === 'company') {
                ajaxUrl = apiBaseUrl + apiMap[entity][op];
                actionValue = op === 'add' ? 'addCompany' : 'updateCompany';
            } else if (entity === 'store') {
                ajaxUrl = apiBaseUrl + apiMap[entity][op];
                actionValue = op === 'add' ? 'addStore' : 'updateStore';
            } else {
                return;
            }
            formData.push({ name: 'action', value: actionValue });
            if (op === 'update' && id) {
                formData.push({ name: 'tax_id', value: id });
            }

            $.post(ajaxUrl, $.param(formData), function (res) {
                showToast(res.success ? '操作成功' : '操作失敗: ' + escapeHtml(res.message || ''), res.success ? 'success' : 'danger');
                if (res.success) {
                    $('#addModal').modal('hide');
                    loadTableData(entity, entity === 'company' ? 'companyTableBody' : 'storeTableBody');
                }
            }, 'json').fail(function (jqXHR) {
                let errorMsg = '儲存操作失敗，請檢查網路連線或稍後再試。';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = '儲存操作失敗: ' + escapeHtml(jqXHR.responseJSON.message);
                }
                showToast(errorMsg, 'danger');
            });
        }

        // 刪除按鈕監聽
        $(document).on('click', '.btn-action[data-op="delete"]', function () {
            if (!confirm('確定要刪除嗎？')) return;
            const entity = this.dataset.entity;
            const id = this.dataset.id;
            let ajaxUrl;
            let ajaxData = {};

            if (entity === 'company') {
                ajaxUrl = apiBaseUrl + apiMap[entity].delete;
                ajaxData = { action: 'deleteCompany', tax_id: id };
            } else if (entity === 'store') {
                ajaxUrl = apiBaseUrl + apiMap[entity].delete;
                ajaxData = { action: 'deleteStore', tax_id: id };
            } else {
                return;
            }

            $.post(ajaxUrl, ajaxData, function (res) {
                showToast(res.success ? '刪除成功' : '刪除失敗: ' + escapeHtml(res.message || ''), res.success ? 'success' : 'danger');
                if (res.success) {
                    loadTableData(entity, entity === 'company' ? 'companyTableBody' : 'storeTableBody');
                }
            }, 'json').fail(function (jqXHR) {
                let errorMsg = '刪除操作失敗，請檢查網路連線或稍後再試。';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = '刪除操作失敗: ' + escapeHtml(jqXHR.responseJSON.message);
                }
                showToast(errorMsg, 'danger');
            });
        });

        // 更新廣告連結按鈕
        $('#saveAdLinkBtn').click(function () {
            const adLinkValue = $('#adLinkInput').val();
            $.post(apiBaseUrl + 'updateAdLink.php', { adLink: adLinkValue }, function (res) {
                showToast(res.success ? '更新成功' : '更新失敗: ' + escapeHtml(res.message || ''), res.success ? 'success' : 'danger');
            }, 'json').fail(function (jqXHR) {
                let errorMsg = '更新廣告連結失敗，請檢查網路連線或稍後再試。';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMsg = '更新廣告連結失敗: ' + escapeHtml(jqXHR.responseJSON.message);
                }
                showToast(errorMsg, 'danger');
            });
        });

        // 顯示 Toast 通知
        function showToast(msg, type) {
            const message = String(msg);
            const toast = $(
                '<div class="toast align-items-center text-white bg-' + escapeHtml(type) + ' border-0" role="alert" data-bs-delay="3000">' +
                '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>' +
                '</div>'
            );
            $('.toast-container').append(toast);
            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Toast !== 'undefined') {
                new bootstrap.Toast(toast[0]).show();
                toast.on('hidden.bs.toast', function () { $(this).remove(); });
            } else {
                console.error("Bootstrap Toast 組件不存在。");
            }
        }
    });
</script>