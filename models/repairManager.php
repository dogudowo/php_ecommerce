<?php
// Define target paths (relative to the htdocs/public_html root)
define('CH_CONTEXT_IMG_PATH', 'imgs/repair/context-ch/repairContext.jpg');
define('CH_RUNDOWN_IMG_PATH', 'imgs/repair/context-ch/repairRundown.jpg');
define('EN_CONTEXT_IMG_PATH', 'imgs/repair/context-en/repairContextUS.jpg');
define('EN_RUNDOWN_IMG_PATH', 'imgs/repair/context-en/repairRundownEN.jpg');
define('PRICE_IMG_PATH', 'imgs/repair/price/AllenRepairPrice0609.jpg');
define('KEY_REPORT_DOC_PATH', 'src/KeyReport.doc');

?>
<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <div class="alert alert-success" role="alert">
        內容已成功更新！
    </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
    <div class="alert alert-danger" role="alert">
        更新失敗：<?php echo htmlspecialchars($_GET['message'] ?? '未知錯誤'); ?>
    </div>
<?php endif; ?>

<ul class="nav nav-tabs" id="repairTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ch-content-tab" data-bs-toggle="tab" data-bs-target="#ch-content"
            type="button" role="tab" aria-controls="ch-content" aria-selected="true">中文內容</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="en-content-tab" data-bs-toggle="tab" data-bs-target="#en-content" type="button"
            role="tab" aria-controls="en-content" aria-selected="false">英文內容</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="price-doc-tab" data-bs-toggle="tab" data-bs-target="#price-doc" type="button"
            role="tab" aria-controls="price-doc" aria-selected="false">價格圖與文件</button>
    </li>
</ul>

<form id="repairForm" enctype="multipart/form-data">
    <div class="tab-content" id="repairTabContent">
        <!-- 中文內容 Tab -->
        <div class="tab-pane fade show active" id="ch-content" role="tabpanel" aria-labelledby="ch-content-tab">
            <div class="mb-3 pt-3">
                <label for="ch_context_img" class="form-label">中文維修內容圖片 (repairContext.jpg):</label>
                <input class="form-control" type="file" id="ch_context_img" name="ch_context_img"
                    accept="image/jpeg, image/png">
                <p class="current-img-label mt-2">目前圖片:</p>
                <img src="<?php echo CH_CONTEXT_IMG_PATH; ?>?t=<?php echo time(); ?>"
                    alt="Current Chinese Context Image" class="img-preview" style="max-width: 30%; height: auto;">
            </div>
            <div class="mb-3">
                <label for="ch_rundown_img" class="form-label">中文維修流程圖片 (repairRundown.jpg):</label>
                <input class="form-control" type="file" id="ch_rundown_img" name="ch_rundown_img"
                    accept="image/jpeg, image/png">
                <p class="current-img-label mt-2">目前圖片:</p>
                <img src="<?php echo CH_RUNDOWN_IMG_PATH; ?>?t=<?php echo time(); ?>"
                    alt="Current Chinese Rundown Image" class="img-preview" style="max-width: 30%; height: auto;">
            </div>
        </div>

        <!-- 英文內容 Tab -->
        <div class="tab-pane fade" id="en-content" role="tabpanel" aria-labelledby="en-content-tab">
            <div class="mb-3 pt-3">
                <label for="en_context_img" class="form-label">英文維修內容圖片 (repairContextUS.jpg):</label>
                <input class="form-control" type="file" id="en_context_img" name="en_context_img"
                    accept="image/jpeg, image/png">
                <p class="current-img-label mt-2">目前圖片:</p>
                <img src="<?php echo EN_CONTEXT_IMG_PATH; ?>?t=<?php echo time(); ?>"
                    alt="Current English Context Image" class="img-preview" style="max-width: 30%; height: auto;">
            </div>
            <div class="mb-3">
                <label for="en_rundown_img" class="form-label">英文維修流程圖片 (repairRundownEN.jpg):</label>
                <input class="form-control" type="file" id="en_rundown_img" name="en_rundown_img"
                    accept="image/jpeg, image/png">
                <p class="current-img-label mt-2">目前圖片:</p>
                <img src="<?php echo EN_RUNDOWN_IMG_PATH; ?>?t=<?php echo time(); ?>"
                    alt="Current English Rundown Image" class="img-preview" style="max-width: 30%; height: auto;">
            </div>
        </div>

        <!-- 價格圖與文件 Tab -->
        <div class="tab-pane fade" id="price-doc" role="tabpanel" aria-labelledby="price-doc-tab">
            <div class="my-3">
                <label for="price_img" class="form-label">價格表圖片 (AllenRepairPrice0609.jpg):</label>
                <input class="form-control" type="file" id="price_img" name="price_img" accept="image/jpeg, image/png">
                <p class="current-img-label mt-2">目前圖片:</p>
                <img src="<?php echo PRICE_IMG_PATH; ?>?t=<?php echo time(); ?>" alt="Current Price Image"
                    class="img-preview" style="max-width: 30%; height: auto;">
            </div>
            <div class="mb-3">
                <label for="key_report_doc" class="form-label">鑰匙價格表文件 (KeyReport.doc):</label>
                <input class="form-control" type="file" id="key_report_doc" name="key_report_doc"
                    accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                <p class="current-img-label mt-2">目前文件: <a href="<?php echo 'src/KeyReport.doc'; ?>"
                        target="_blank">KeyReport.doc</a></p>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-primary mt-3" onclick="handleFormSubmit()">儲存變更</button>
</form>

<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<script>
    async function handleFormSubmit() {
        const form = document.getElementById('repairForm');
        const formData = new FormData(form);

        try {
            const response = await fetch('api/repairPageHandler.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // 更新所有圖片預覽
                document.querySelectorAll('.img-preview').forEach(img => {
                    img.src = img.src.split('?')[0] + '?t=' + new Date().getTime();
                });

                // 顯示成功toast通知
                const toastContainer = document.createElement('div');
                toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '11';

                toastContainer.innerHTML = `
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle me-2"></i>內容已成功更新！
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;

                document.body.appendChild(toastContainer);

                // 自動顯示toast
                const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
                toast.show();

                // 3秒後自動移除
                setTimeout(() => {
                    toast.dispose();
                    toastContainer.remove();
                }, 3000);
            } else {
                // 顯示錯誤toast通知
                const toastContainer = document.createElement('div');
                toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '11';

                toastContainer.innerHTML = `
                    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-exclamation-triangle me-2"></i>更新失敗：${result.message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;

                document.body.appendChild(toastContainer);

                // 自動顯示toast
                const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
                toast.show();

                // 3秒後自動移除
                setTimeout(() => {
                    toast.dispose();
                    toastContainer.remove();
                }, 3000);
            }
        } catch (error) {
            console.error('Error:', error);
            // 顯示錯誤toast通知
            const toastContainer = document.createElement('div');
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '11';

            toastContainer.innerHTML = `
                <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-exclamation-triangle me-2"></i>更新失敗：發生未知錯誤
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            document.body.appendChild(toastContainer);

            // 自動顯示toast
            const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
            toast.show();

            // 3秒後自動移除
            setTimeout(() => {
                toast.dispose();
                toastContainer.remove();
            }, 3000);
        }
    }
</script>