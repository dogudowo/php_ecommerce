<?php
require_once __DIR__ . '/../../api/error_handler.php';
// 系統設置管理文件

// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../../login.php');
    exit;
}
?>
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>系統設置</h5>
                </div>
                <div class="card-body">
                    <form id="systemSettingsForm">
                        <h5 class="mb-3">社群連結設定</h5>
                        <div class="mb-3">
                            <label for="lineLink" class="form-label">Line 連結</label>
                            <input type="url" class="form-control" id="lineLink"
                                value="https://line.me/R/ti/p/_idjyEv115">
                        </div>
                        <div class="mb-3">
                            <label for="facebookLink" class="form-label">Facebook 連結</label>
                            <input type="url" class="form-control" id="facebookLink"
                                value="https://www.facebook.com/legendwalkertaiwan/">
                        </div>
                        <div class="mb-3">
                            <label for="instagramLink" class="form-label">Instagram 連結</label>
                            <input type="url" class="form-control" id="instagramLink"
                                value="https://www.instagram.com/all_en.boutique/">
                        </div>
                        <div class="mb-3">
                            <label for="youtubeLink" class="form-label">YouTube 連結</label>
                            <input type="url" class="form-control" id="youtubeLink"
                                value="https://www.youtube.com/user/legendwalker6000">
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

<script>
    document.addEventListener('DOMContentLoaded', async () => { // Make async for await
        // Toast 容器
        let $toastContainer = $('.toast-container');
        if (!$toastContainer.length) {
            $toastContainer = $('<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;"></div>').appendTo('body');
        }

        const defaults = {
            line: 'https://line.me/R/ti/p/_idjyEv115',
            facebook: 'https://www.facebook.com/legendwalkertaiwan/',
            instagram: 'https://www.instagram.com/all_en.boutique/',
            youtube: 'https://www.youtube.com/user/legendwalker6000'
        };

        await loadSettings(); // Load settings on page load
        const form = document.getElementById('systemSettingsForm');
        const resetBtn = document.getElementById('resetSettingsBtn');
        const saveBtn = document.getElementById('saveSettingsBtn');
        // 表單預設值

        // Function to load settings from the server
        async function loadSettings() {
            try {
                const response = await fetch('models/backend/functions/system/updateSettings.php?action=getSystemSettings', {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                });
                const settings = await response.json();
                if (settings.success && settings.data) {
                    document.getElementById('lineLink').value = settings.data.line || defaults.line;
                    document.getElementById('facebookLink').value = settings.data.facebook || defaults.facebook;
                    document.getElementById('instagramLink').value = settings.data.instagram || defaults.instagram;
                    document.getElementById('youtubeLink').value = settings.data.youtube || defaults.youtube;
                } else {
                    // Load defaults if fetching fails or no data
                    loadDefaultSettings();
                    showToast(settings.message || '無法載入目前設定，已載入預設值。', 'warning');
                }
            } catch (error) {
                console.error('Error loading settings:', error);
                loadDefaultSettings();
                showToast('載入設定時發生錯誤，已載入預設值。', 'danger');
            }
        }

        // Function to load default settings into the form
        function loadDefaultSettings() {
            document.getElementById('lineLink').value = defaults.line;
            document.getElementById('facebookLink').value = defaults.facebook;
            document.getElementById('instagramLink').value = defaults.instagram;
            document.getElementById('youtubeLink').value = defaults.youtube;
        }

        resetBtn.addEventListener('click', () => {
            document.getElementById('lineLink').value = defaults.line;
            document.getElementById('facebookLink').value = defaults.facebook;
            document.getElementById('instagramLink').value = defaults.instagram;
            document.getElementById('youtubeLink').value = defaults.youtube;
            showToast('表單已重置為預設值。', 'info');
        });

        saveBtn.addEventListener('click', async () => {
            const payload = {
                action: 'saveSystemSettings',
                socialLinks: {
                    line: document.getElementById('lineLink').value,
                    facebook: document.getElementById('facebookLink').value,
                    instagram: document.getElementById('instagramLink').value,
                    youtube: document.getElementById('youtubeLink').value
                }
            };

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 保存中...';

            try {
                const res = await fetch('models/backend/functions/system/updateSettings.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                showToast(result.success ? (result.message || '系統設置已成功更新！') : (result.message || '系統設置更新失敗。'),
                    result.success ? 'success' : 'danger');

                if (result.success) {
                    // 派發自定義事件通知 footer 更新
                    const event = new CustomEvent('socialLinksUpdated');
                    document.dispatchEvent(event);
                }
            } catch (err) {
                console.error(err);
                showToast('保存系統設置時發生錯誤，請稍後再試。', 'danger');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-save me-1"></i>保存設置';
            }
        });

        function showToast(message, type = 'info') {
            const toastId = 'toast-' + Date.now();
            const toastClass = type === 'success' ? 'bg-success' : (type === 'danger' ? 'bg-danger' : 'bg-info');
            const toastIcon = type === 'success' ? '<i class="bi bi-check-circle-fill me-2"></i>' : (type === 'danger' ? '<i class="bi bi-x-circle-fill me-2"></i>' : '<i class="bi bi-info-circle-fill me-2"></i>');

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

            $toastContainer.append($toast);
            const toast = new bootstrap.Toast($toast[0]);
            toast.show();

            $toast.on('hidden.bs.toast', function () {
                $(this).remove();
            });
        }
    });
</script>