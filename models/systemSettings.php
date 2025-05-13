<?php
require_once __DIR__ . '/../api/error_handler.php';
// 系統設置管理文件

// 只有後台登入者可存取
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-sliders me-2"></i>社群連結設置</h3>
</div>
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="systemSettingsForm" novalidate>
                        <div class="mb-3">
                            <label for="lineLink" class="form-label">Line 連結</label>
                            <input type="url" class="form-control" id="lineLink" placeholder="https://line.me/..."
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="facebookLink" class="form-label">Facebook 連結</label>
                            <input type="url" class="form-control" id="facebookLink"
                                placeholder="https://facebook.com/..." required>
                        </div>
                        <div class="mb-3">
                            <label for="instagramLink" class="form-label">Instagram 連結</label>
                            <input type="url" class="form-control" id="instagramLink"
                                placeholder="https://instagram.com/..." required>
                        </div>
                        <div class="mb-3">
                            <label for="youtubeLink" class="form-label">YouTube 連結</label>
                            <input type="url" class="form-control" id="youtubeLink"
                                placeholder="https://youtube.com/..." required>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" id="resetSettingsBtn">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                            </button>
                            <button type="button" class="btn btn-primary" id="saveSettingsBtn">
                                <i class="bi bi-save me-1"></i>保存設置
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        /** ================= 常量 ================= */
        const API_URL = 'api/UpdateLink.php';
        const FIELDS = ['line', 'facebook', 'instagram', 'youtube'];
        const inputIds = FIELDS.map(k => `${k}Link`);

        /** ================= Toast ================= */
        let $toastContainer = $('.toast-container');
        if (!$toastContainer.length) {
            $toastContainer = $('<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1055"></div>').appendTo('body');
        }
        const showToast = (msg, lvl = 'info', delay = 5000) => {
            const bg = { success: 'bg-success', danger: 'bg-danger', warning: 'bg-warning', info: 'bg-info' }[lvl] || 'bg-info';
            const toastHtml = `
                <div class="toast align-items-center text-white ${bg} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${delay}">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi ${lvl === 'danger' ? 'bi-exclamation-triangle' : lvl === 'success' ? 'bi-check-circle' : 'bi-info-circle'} me-2"></i>${msg}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            $toastContainer.html(toastHtml).css('z-index', '2000');
            const toastEl = $toastContainer.find('.toast')[0];
            new bootstrap.Toast(toastEl).show();
            $(toastEl).on('hidden.bs.toast', () => $(toastEl).remove());
        };

        /** ================= DOM ================= */
        const resetBtn = document.getElementById('resetSettingsBtn');
        const saveBtn = document.getElementById('saveSettingsBtn');

        /** ================= 狀態 ================= */
        let loadedLinks = {};

        /** ================= 工具函式 ================= */
        const fillForm = links => {
            inputIds.forEach(id => {
                const key = id.replace('Link', '');
                document.getElementById(id).value = links[key] ?? '';
            });
        };
        const collectForm = () => {
            const obj = {};
            inputIds.forEach(id => {
                const key = id.replace('Link', '');
                obj[key] = document.getElementById(id).value.trim();
            });
            return obj;
        };
        const hasInvalidUrl = links => FIELDS.some(k => !/^https?:\/\//i.test(links[k]));

        /** ================= 事件 ================= */
        resetBtn.addEventListener('click', () => {
            fillForm(loadedLinks);
            showToast('已還原為目前設定值。', 'info');
        });

        saveBtn.addEventListener('click', async () => {
            const socialLinks = collectForm();
            if (hasInvalidUrl(socialLinks)) {
                showToast('請輸入有效的 URL。', 'danger');
                return;
            }
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> 保存中...';
            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'saveSocialLinks', socialLinks })
                });
                const json = await res.json();
                showToast(json.message || (json.success ? '更新成功！' : '更新失敗。'), json.success ? 'success' : 'danger');
                if (json.success) {
                    loadedLinks = { ...socialLinks };
                    document.dispatchEvent(new CustomEvent('socialLinksUpdated', { detail: loadedLinks }));
                }
            } catch (err) {
                console.error(err);
                showToast('連線錯誤，請稍後再試。', 'danger');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>保存設置';
            }
        });

        /** ================= 初始化 ================= */
        (async () => {
            try {
                const res = await fetch(`${API_URL}?action=getLinks`);
                const json = await res.json();
                if (json.success && json.data.socialLinks) {
                    loadedLinks = { ...json.data.socialLinks };
                } else {
                    showToast(json.message || '無法讀取設定。', 'warning');
                    loadedLinks = FIELDS.reduce((o, k) => (o[k] = '', o), {});
                }
                fillForm(loadedLinks);
            } catch (e) {
                console.error(e);
                showToast('初始化失敗，請稍後重試。', 'warning');
                loadedLinks = FIELDS.reduce((o, k) => (o[k] = '', o), {});
                fillForm(loadedLinks);
            }
        })();
    });
</script>