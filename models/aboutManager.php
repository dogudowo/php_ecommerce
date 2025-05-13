<?php
// about_management_optimized.php (no global API constants)
require_once __DIR__ . '/../api/db.php';
$adLinkObj = json_decode(file_get_contents(__DIR__ . '/../src/adLink.json'));
$adLink = $adLinkObj->adLink ?? '';
?>

<div class="container-fluid p-0 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>關於我們頁面管理</h3>
    </div>

    <!-- 公司資料 -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>公司資料管理</h4>
            <button class="btn btn-primary js-open-modal" data-entity="company" data-op="add">
                <i class="bi bi-plus-lg me-1"></i>新增公司
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>統一編號</th>
                            <th>公司名稱</th>
                            <th>電子郵件</th>
                            <th>電話</th>
                            <th>傳真</th>
                            <th style="width:140px;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="companyTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 門市資料 -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>門市資料管理</h4>
            <button class="btn btn-primary js-open-modal" data-entity="store" data-op="add">
                <i class="bi bi-plus-lg me-1"></i>新增門市
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th class="d-none">統一編號</th>
                            <th>門市名稱</th>
                            <th>電話</th>
                            <th>地址</th>
                            <th>服務時間</th>
                            <th style="width:140px;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="storeTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 廣告連結 -->
    <div class="card">
        <div class="card-header">
            <h4><i class="bi bi-link-45deg me-2"></i>廣告連結管理</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">廣告連結</label>
                <input type="url" id="adLinkInput" class="form-control" value="<?= htmlspecialchars($adLink) ?>">
            </div>
            <button class="btn btn-primary" id="saveAdLinkBtn">儲存變更</button>
        </div>
    </div>
</div>

<!-- 通用 Modal -->
<div class="modal fade" id="entityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="entityForm"></form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button class="btn btn-primary" id="modalSaveBtn"></button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<script>
    (() => {
        /* ------------------ 共用設定 ------------------ */
        const FIELD_CFG = {
            tax_id: { label: "統一編號", hideFor: ["store"] },
            name: { label: { company: "公司名稱", store: "門市名稱" } },
            email: { label: "電子郵件" },
            phone: { label: "電話" },
            fax: { label: "傳真" },
            location: { label: "地址" },
            opening_time: { label: "服務時間", textarea: true },
            url: { label: "地圖連結" }
        };

        const modalEl = document.getElementById('entityModal');
        const toastWrap = document.querySelector('.toast-container');
        let curEntity = 'company', curOp = 'add', editId = '';

        /* ------------------ 工具 ------------------ */
        const toast = (msg, type = 'success') => {
            const el = document.createElement('div');
            el.innerHTML = `<div class="toast align-items-center text-white bg-${type} border-0" role="alert" data-bs-delay="3000">
            <div class="d-flex"><div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`;
            const t = el.firstElementChild;
            toastWrap.appendChild(t);
            new bootstrap.Toast(t).show();
            t.addEventListener('hidden.bs.toast', () => t.remove());
        };

        const actionOf = (entity, op) => ({
            company: {
                getAll: 'getAllCompanies', get: 'getCompany', add: 'addCompany', update: 'updateCompany', delete: 'deleteCompany'
            },
            store: {
                getAll: 'getAllStores', get: 'getStore', add: 'addStore', update: 'updateStore', delete: 'deleteStore'
            }
        })[entity][op];

        /* ------------------ 載入表格 ------------------ */
        const loadTable = async entity => {
            try {
                const res = await fetch(`api/about.php?action=${actionOf(entity, 'getAll')}`);
                const json = await res.json();
                if (!json.success) throw Error(json.message);
                const tbody = document.getElementById(`${entity}TableBody`);
                tbody.innerHTML = json.data.map(row => renderRow(entity, row)).join('');
            } catch (e) { toast(`${entity === 'company' ? '公司' : '門市'}資料載入失敗：${e.message || e}`, 'danger'); }
        };

        const renderRow = (entity, row) => {
            const opTd = `<td><button class="btn btn-sm btn-warning js-edit" data-entity="${entity}" data-id="${row.tax_id}">編輯</button>
                       <button class="btn btn-sm btn-danger  js-del"  data-entity="${entity}" data-id="${row.tax_id}">刪除</button></td>`;
            return entity === 'company'
                ? `<tr><td>${row.tax_id || ''}</td><td>${row.name || ''}</td><td>${row.email || ''}</td><td>${row.phone || ''}</td><td>${row.fax || ''}</td>${opTd}</tr>`
                : `<tr><td class="d-none">${row.tax_id || ''}</td><td>${row.name || ''}</td><td>${row.phone || ''}</td><td>${row.location || ''}</td><td>${row.opening_time || ''}</td>${opTd}</tr>`;
        };

        /* ------------------ Modal 相關 ------------------ */
        const buildForm = (entity, data = {}) => {
            const form = document.getElementById('entityForm');
            form.innerHTML = '';
            const fields = entity === 'company'
                ? ['tax_id', 'name', 'email', 'phone', 'fax']
                : ['tax_id', 'name', 'phone', 'location', 'opening_time', 'url'];
            fields.forEach(f => {
                const cfg = FIELD_CFG[f];
                if (cfg.hideFor?.includes(entity)) return; // UI 隱藏
                const labelTxt = typeof cfg.label === 'object' ? cfg.label[entity] : cfg.label;
                const wrap = document.createElement('div');
                wrap.className = 'mb-3';
                wrap.innerHTML = `<label class="form-label" for="${f}">${labelTxt}</label>
                ${cfg.textarea ? `<textarea id="${f}" name="${f}" rows="4" class="form-control"></textarea>` : `<input id="${f}" name="${f}" class="form-control">`}`;
                form.appendChild(wrap);
            });
            if (entity === 'store' && !form.tax_id) {
                const hid = document.createElement('input'); hid.type = 'hidden'; hid.id = hid.name = 'tax_id'; form.appendChild(hid);
            }
            Object.entries(data).forEach(([k, v]) => { if (form[k]) form[k].value = v ?? ''; });
        };

        const openModal = async (entity, op, id = '') => {
            curEntity = entity; curOp = op; editId = id;
            document.getElementById('modalTitle').textContent = (op === 'add' ? '新增' : '編輯') + (entity === 'company' ? '公司' : '門市');
            document.getElementById('modalSaveBtn').textContent = op === 'add' ? '儲存' : '更新';
            let data = {};
            if (op === 'edit') {
                try {
                    const res = await fetch(`api/about.php?action=${actionOf(entity, 'get')}&tax_id=${encodeURIComponent(id)}`);
                    const json = await res.json();
                    if (!json.success) throw Error(json.message);
                    data = json.data;
                } catch (e) { toast('載入編輯資料失敗：' + (e.message || e), 'danger'); return; }
            }
            buildForm(entity, data);
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        };

        /* ------------------ 儲存 ------------------ */
        const saveEntity = async () => {
            const form = document.getElementById('entityForm');
            const body = new URLSearchParams(new FormData(form));
            body.append('action', actionOf(curEntity, curOp === 'add' ? 'add' : 'update'));
            if (curOp === 'edit') body.append('tax_id', editId);
            try {
                const res = await fetch('api/about.php', { method: 'POST', body });
                const json = await res.json();
                if (!json.success) throw Error(json.message);
                toast('操作成功');
                bootstrap.Modal.getInstance(modalEl).hide();
                loadTable(curEntity);
            } catch (e) { toast('操作失敗：' + (e.message || e), 'danger'); }
        };

        /* ------------------ 刪除 ------------------ */
        const delEntity = async (entity, id) => {
            if (!confirm('確定要刪除嗎？')) return;
            const body = new URLSearchParams({ action: actionOf(entity, 'delete'), tax_id: id });
            try {
                const res = await fetch('api/about.php', { method: 'POST', body });
                const json = await res.json();
                if (!json.success) throw Error(json.message);
                toast('刪除成功');
                loadTable(entity);
            } catch (e) { toast('刪除失敗：' + (e.message || e), 'danger'); }
        };

        /* ------------------ 廣告連結 ------------------ */
        document.getElementById('saveAdLinkBtn').addEventListener('click', async () => {
            const adLink = document.getElementById('adLinkInput').value.trim();
            try {
                const res = await fetch('api/UpdateLink.php', { method: 'POST', body: JSON.stringify({ action: 'saveAdLink', adLink }) });
                const json = await res.json();
                if (!json.success) throw Error(json.message);
                toast('更新成功');
            } catch (e) { toast('更新失敗：' + (e.message || e), 'danger'); }
        });

        /* ------------------ 事件委派 ------------------ */
        document.addEventListener('click', e => {
            const t = e.target;
            if (t.classList.contains('js-open-modal')) openModal(t.dataset.entity, t.dataset.op);
            else if (t.classList.contains('js-edit')) openModal(t.dataset.entity, 'edit', t.dataset.id);
            else if (t.classList.contains('js-del')) delEntity(t.dataset.entity, t.dataset.id);
        });
        document.getElementById('modalSaveBtn').addEventListener('click', saveEntity);

        /* ------------------ 初始化 ------------------ */
        loadTable('company');
        loadTable('store');
    })();
</script>