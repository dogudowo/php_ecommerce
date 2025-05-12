<?php
// about_management.php
// 引入數據庫連接與函式
require_once __DIR__ . '/../../api/db.php';
require_once __DIR__ . '/functions/about/companyFunctions.php';
require_once __DIR__ . '/functions/about/storeFunctions.php';

// 獲取資料
$companies = CompanyFunctions::getCompanyInfo();
$stores = StoreFunctions::getAllStores();
$adLinkObj = json_decode(file_get_contents(__DIR__ . '/../../src/adLink.json'));
$adLink = $adLinkObj->adLink;
?>

<div class="container-fluid p-0 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>關於我們頁面管理</h3>
    </div>

    <?php function renderTable($title, $headers, $rows, $type)
    { ?>
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4><?= $title ?></h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"
                        data-entity="<?= $type ?>">
                        <i class="bi bi-plus-lg  me-1"></i>新增<?= $type === 'company' ? '公司' : '門市' ?>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <?php foreach ($headers as $h): ?>
                                    <th><?= $h ?></th><?php endforeach; ?>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <?php foreach ($row as $val): ?>
                                        <td><?= htmlspecialchars($val) ?></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-action" data-op="edit"
                                            data-entity="<?= $type ?>" data-id="<?= $row[array_key_first($row)] ?>">編輯</button>
                                        <button class="btn btn-sm btn-danger btn-action" data-op="delete"
                                            data-entity="<?= $type ?>" data-id="<?= $row[array_key_first($row)] ?>">刪除</button>
                                    </td>
                                </tr><?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php }

    renderTable(
        '公司資料管理',
        ['統一編號', '公司名稱', '電子郵件', '電話', '傳真'],
        array_map(function ($c) {
            return ['tax_id' => $c['tax_id'], 'name' => $c['name'], 'email' => $c['email'], 'phone' => $c['phone'], 'fax' => $c['fax']];
        }, $companies),
        'company'
    );

    renderTable(
        '門市資料管理',
        ['統一編號', '門市名稱', '電話', '地址', '營業時間'],
        array_map(function ($s) {
            return ['tax_id' => $s['tax_id'], 'name' => $s['name'], 'phone' => $s['phone'], 'location' => $s['location'], 'opening_time' => $s['opening_time']];
        }, $stores),
        'store'
    );
    ?>

    <!-- 廣告連結管理 -->
    <div class="card">
        <div class="card-header">
            <h4><i class="bi bi-link-45deg me-2"></i>廣告連結管理</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">廣告連結</label>
                <input type="text" class="form-control" id="adLinkInput" value="<?= htmlspecialchars($adLink) ?>">
            </div>
            <button class="btn btn-primary" id="saveAdLinkBtn">儲存變更</button>
        </div>
    </div>
</div>

<!-- 全域 Modal: 新增/編輯 -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="entityForm"></form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveEntityBtn"></button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<script>
    $(function () {
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
        const apiMap = {
            company: { get: 'getCompany.php', add: 'addCompany.php', update: 'updateCompany.php', delete: 'deleteCompany.php' },
            store: { get: 'getStore.php', add: 'addStore.php', update: 'updateStore.php', delete: 'deleteStore.php' }
        };

        $('.btn[data-entity]').on('click', function () {
            const entity = $(this).data('entity');
            const op = this.dataset.op || 'add';
            const title = (op === 'add' ? '新增' : '編輯') + (entity === 'company' ? '公司' : '門市');
            $('#modalTitle').text(title);
            $('#saveEntityBtn').text(op === 'add' ? '儲存' : '更新');

            const form = $('#entityForm').empty();
            const fields = entity === 'company'
                ? ['tax_id', 'name', 'email', 'phone', 'fax']
                : ['name', 'phone', 'location', 'opening_time', 'url'];

            fields.forEach(function (f) {
                const labelText = typeof labelMap[f] === 'object' ? labelMap[f][entity] : labelMap[f];
                const label = $('<label>').addClass('form-label').attr('for', f).text(labelText);
                const isTextarea = ['email', 'opening_time'].includes(f);
                let input;
                if (f === 'tax_id') {
                    // 編輯時顯示唯讀欄位，新增時隱藏輸入框
                    input = $('<input>').attr({ type: 'text', id: f, name: f });
                    if (op === 'edit') {
                        input.prop('readonly', true);
                    } else {
                        input.closest('.mb-3').hide();
                    }
                } else {
                    if (isTextarea) {
                        input = $('<textarea>').attr({ id: f, name: f, rows: 4 });
                    } else {
                        input = $('<input>').attr({ type: 'text', id: f, name: f });
                    }
                }
                input.addClass('form-control');
                form.append($('<div>').addClass('mb-3').append(label, input));
            });

            if (op === 'edit') {
                const id = $(this).data('id');
                $.getJSON('models/backend/functions/about/' + apiMap[entity].get, { tax_id: id }, function (res) {
                    if (res.success) {
                        Object.entries(res.data).forEach(function ([k, v]) {
                            if (k === 'tax_id') {
                                $('#' + k).val(v);
                            } else {
                                $('#' + k).val(v);
                            }
                        });
                    }
                });
            }

            $('#saveEntityBtn').off().on('click', function () {
                saveEntity(entity, op);
            });
            $('#addModal').modal('show');
        });

        function saveEntity(entity, op) {
            const url = 'models/backend/functions/about/' + apiMap[entity][op];
            $.post(url, $('#entityForm').serialize(), function (res) {
                showToast(res.success ? '操作成功' : '操作失敗', res.success ? 'success' : 'danger');
                if (res.success) {
                    setTimeout(function () { location.reload(); }, 1000);
                }
            }, 'json');
        }

        $(document).on('click', '.btn-action[data-op="delete"]', function () {
            if (!confirm('確定要刪除嗎？')) return;
            const entity = this.dataset.entity;
            const id = this.dataset.id;
            $.post('models/backend/functions/about/' + apiMap[entity].delete, { tax_id: id }, function (res) {
                showToast(res.success ? '刪除成功' : '刪除失敗', res.success ? 'success' : 'danger');
                if (res.success) {
                    setTimeout(function () { location.reload(); }, 500);
                }
            }, 'json');
        });

        $('#saveAdLinkBtn').click(function () {
            $.post('models/backend/functions/about/updateAdLink.php', { adLink: $('#adLinkInput').val() }, function (res) {
                showToast(res.success ? '更新成功' : '更新失敗', res.success ? 'success' : 'danger');
            }, 'json');
        });

        function showToast(msg, type) {
            const toast = $(
                '<div class="toast align-items-center text-white bg-' + type + ' border-0" role="alert" data-bs-delay="3000">' +
                '<div class="d-flex"><div class="toast-body">' + msg + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>' +
                '</div>'
            );
            $('.toast-container').append(toast);
            new bootstrap.Toast(toast[0]).show();
        }
    });
</script>