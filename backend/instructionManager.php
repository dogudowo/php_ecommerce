<?php
require_once __DIR__ . '/../api/error_handler.php';
require_once __DIR__ . '/../api/instruction.php';
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-info-circle me-2"></i>購物說明</h3>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4>購物說明圖片管理</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h5>購物說明圖片</h5>
                    <img src="<?= getInstructionImages()['buyContext'] ?>" class="img-fluid mb-3"
                        style="max-height: 300px;">
                    <form class="image-upload-form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="image_type" value="buyContext">
                        <div class="mb-3">
                            <input class="form-control" type="file" name="instruction_image"
                                accept="image/jpeg, image/jpg" required>
                        </div>
                        <button type="submit" class="btn btn-primary">更新圖片</button>
                    </form>
                </div>
                <div class="col-md-6 mb-3">
                    <h5>購物流程圖片</h5>
                    <img src="<?= getInstructionImages()['order'] ?>" class="img-fluid mb-3" style="max-height: 300px;">
                    <form class="image-upload-form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="image_type" value="order">
                        <div class="mb-3">
                            <input class="form-control" type="file" name="instruction_image"
                                accept="image/jpeg, image/jpg" required>
                        </div>
                        <button type="submit" class="btn btn-primary">更新圖片</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>購物Q&A管理</h4>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <h5>新增Q&A</h5>
                <form id="addQAForm">
                    <div class="mb-3">
                        <label class="form-label">問題</label>
                        <input type="text" class="form-control" id="question" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">回答</label>
                        <textarea class="form-control" id="answer" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">新增</button>
                </form>
            </div>

            <div>
                <h5>現有Q&A列表</h5>
                <div id="qaList" class="table-responsive">
                    <!-- AJAX載入QA列表 -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 編輯Q&A的Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">編輯Q&A</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editQAForm">
                <div class="modal-body">
                    <input type="hidden" id="editId">
                    <div class="mb-3">
                        <label class="form-label">問題</label>
                        <input type="text" class="form-control" id="editQuestion" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">回答</label>
                        <textarea class="form-control" id="editAnswer" rows="8" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">儲存變更</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 確認刪除的Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">確認刪除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>確定要刪除這個Q&A嗎？</p>
                <input type="hidden" id="deleteItemId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">確認刪除</button>
            </div>
        </div>
    </div>
</div>

<!-- 通知Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">通知</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="notificationContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">確定</button>
            </div>
        </div>
    </div>
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<script src="api/toast.js"></script>

<script>
    const handlerUrl = 'api/instruction.php';

    // 載入 QA 列表
    async function loadQAList() {
        try {
            const response = await fetch(`${handlerUrl}?action=getQAItems`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const qaItems = await response.json();
            if (!Array.isArray(qaItems)) throw new Error('Invalid QA items data');

            let html = `<table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 20%">問題</th>
                                    <th style="width: 40%">回答</th>
                                    <th style="width: 10%">操作</th>
                                </tr>
                            </thead>
                        <tbody>`;
            qaItems.forEach(item => {
                html += `<tr>
                    <td>${item.question}</td>
                    <td>${item.answer}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary edit-btn"
                                data-id="${item.QA_id}"
                                data-question="${item.question}"
                                data-answer="${item.answer}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${item.QA_id}"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('qaList').innerHTML = html;

            // 綁定編輯按鈕
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('editId').value = btn.dataset.id;
                    document.getElementById('editQuestion').value = btn.dataset.question;
                    document.getElementById('editAnswer').value = btn.dataset.answer;
                    new bootstrap.Modal(document.getElementById('editModal')).show();
                });
            });

            // 綁定刪除按鈕
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('deleteItemId').value = btn.dataset.id;
                    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
                });
            });

        } catch (error) {
            console.error('Error loading QA items:', error);
            showError('載入 QA 列表時發生錯誤', error);
        }
    }

    // 新增 QA
    document.getElementById('addQAForm').addEventListener('submit', async e => {
        e.preventDefault();
        try {
            const payload = {
                question: document.getElementById('question').value,
                answer: document.getElementById('answer').value
            };
            const response = await fetch(`${handlerUrl}?action=addQAItem`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.success) {
                showToast('新增成功', 'success');
                e.target.reset();
                loadQAList();
            } else {
                showToast('新增失敗', 'danger');
            }
        } catch (error) {
            console.error('Error adding QA item:', error);
            showError('新增時發生錯誤', error);
        }
    });

    // 編輯 QA
    document.getElementById('editQAForm').addEventListener('submit', async e => {
        e.preventDefault();
        try {
            const payload = {
                id: document.getElementById('editId').value,
                question: document.getElementById('editQuestion').value,
                answer: document.getElementById('editAnswer').value
            };
            const response = await fetch(`${handlerUrl}?action=updateQAItem`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.success) {
                showToast('更新成功', 'success');
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                loadQAList();
            } else {
                showToast('更新失敗', 'danger');
            }
        } catch (error) {
            console.error('Error updating QA item:', error);
            showError('更新時發生錯誤', error);
        }
    });

    const showError = (title, err) => { console.error(title, err); showToast(`${title}：${err.message || err}`, "danger"); };

    // 初始載入
    document.addEventListener('DOMContentLoaded', () => {
        loadQAList();
        /* 事件委派：監聽整個 deleteConfirmModal，而不是單一按鈕 */
        document
            .getElementById('deleteConfirmModal')
            .addEventListener('click', async (e) => {
                // 只處理真正點到 #confirmDeleteBtn 的情況
                if (!e.target.matches('#confirmDeleteBtn')) return;

                const id = document.getElementById('deleteItemId').value;

                try {
                    const response = await fetch(`${handlerUrl}?action=deleteQAItem`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const result = await response.json();

                    // 關閉 Modal
                    bootstrap.Modal.getInstance(
                        document.getElementById('deleteConfirmModal')
                    ).hide();

                    if (result.success) {
                        showToast('刪除成功', 'success');
                        loadQAList();               // 重新載入列表
                    } else {
                        showToast('刪除失敗', 'danger');
                    }
                } catch (err) {
                    console.error('Error deleting QA item:', err);
                    showError('刪除時發生錯誤', err);
                }
            });

        // 圖片上傳表單處理
        document.querySelectorAll('.image-upload-form').forEach(form => {
            form.addEventListener('submit', async e => {
                e.preventDefault();
                const formData = new FormData(form);
                const imageType = formData.get('image_type');

                try {
                    const response = await fetch(`${handlerUrl}?action=updateInstructionImage`, {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        showToast('圖片更新成功', 'success');
                        window.location.href = window.location.pathname + '?page=instructionManager&image_update=success'; // 重新導向並帶上成功參數
                    } else {
                        showToast(`圖片更新失敗: ${result.error || '未知錯誤'}`, 'danger');
                    }
                } catch (error) {
                    console.error('Error updating image:', error);
                    showError('圖片更新時發生錯誤', error);
                }
            });
        });

        // 檢查 URL 參數並顯示 Toast
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('image_update') && urlParams.get('image_update') === 'success') {
            showToast('圖片更新成功', 'success');
            // 移除 URL 參數以避免重複顯示
            urlParams.delete('image_update');
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            window.history.replaceState({}, document.title, newUrl);
        }
    });
</script>