<?php
require_once __DIR__ . '/../api/error_handler.php';

// 只有後台登入者可存取
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-image me-2"></i>品牌 Logo </h3>
</div>

<div class="row">
    <!-- Title Logo -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">網站標題 Logo (title.png)</div>
            <div class="card-body text-center">
                <img src="imgs/title.png?t=<?php echo time(); ?>" id="titleLogoImg" class="img-fluid mb-3"
                    style="max-height:100px;">
                <form id="titleLogoForm" enctype="multipart/form-data">
                    <input type="file" name="logoFile" class="form-control mb-3" accept=".png" required>
                    <input type="hidden" name="logoType" value="title">
                    <button class="btn btn-primary">更新標題 Logo</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Web Icon -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">網站圖示 (webimg.ico)</div>
            <div class="card-body text-center">
                <img src="imgs/webimg.ico" id="webIconImg" class="img-fluid mb-3" style="height:100px;">
                <form id="webIconForm" enctype="multipart/form-data">
                    <input type="file" name="logoFile" class="form-control mb-3" accept=".ico" required>
                    <input type="hidden" name="logoType" value="favicon">
                    <button class="btn btn-primary">更新網站圖示</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<script src="api/toast.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const forms = ['titleLogoForm', 'webIconForm'];

        forms.forEach(formId => {
            document.getElementById(formId).addEventListener('submit', async e => {
                e.preventDefault();
                await uploadLogo(e.target);
            });
        });

        // 上傳 Logo 並處理結果
        async function uploadLogo(form) {
            try {
                const res = await fetch('api/uploadLogo.php', {
                    method: 'POST',
                    body: new FormData(form)
                });

                const result = await res.json();
                const isTitle = form.id === 'titleLogoForm';

                if (res.ok) {
                    updateLogoImage(isTitle);
                    showToast(result.message, 'success');
                } else {
                    showToast(result.message, 'danger');
                    console.error(err);
                }
            } catch (err) {
                showToast(`發生錯誤：${err.message}`, 'warning');
                console.error(err);
            }
        }

        // 更新圖片（避免瀏覽器快取）
        function updateLogoImage(isTitle) {
            const ts = Date.now();
            const imgId = isTitle ? 'titleLogoImg' : 'webIconImg';
            const imgFile = isTitle ? 'title.png' : 'webimg.ico';
            document.getElementById(imgId).src = `imgs/${imgFile}?${ts}`;
        }

    });
</script>