<?php
// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ROUTER') && (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin')) {
    header('Location: login.php');
    exit;
}

// 定義常量，表示這是管理員後台
define('INCLUDED_FROM_ADMIN', true);
?>
<?php require_once 'fixedFile/header.php'; ?>
<div class="container mt-3">
    <!-- 小螢幕提示訊息 -->
    <div class="d-lg-none mb-4">
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>請注意</h4>
            <p>後台管理系統需要較大的螢幕空間才能正常操作。</p>
            <hr>
            <p class="mb-0">請使用電腦或平板電腦（橫向）開啟此頁面以獲得最佳體驗。</p>
        </div>
    </div>

    <div class="row">
        <!-- 大螢幕側邊欄 -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title text-center">後台管理系統</h4>
                    <p class="card-text text-muted text-center">
                        管理員: <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </p>

                    <!-- 導航標籤頁 -->
                    <ul class="nav nav-tabs mb-3" id="adminTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="frontend-tab" data-bs-toggle="tab"
                                data-bs-target="#frontend-content" type="button" role="tab"
                                aria-controls="frontend-content" aria-selected="true">
                                <i class="bi bi-display me-1"></i>前台
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="backend-tab" data-bs-toggle="tab"
                                data-bs-target="#backend-content" type="button" role="tab"
                                aria-controls="backend-content" aria-selected="false">
                                <i class="bi bi-gear me-1"></i>後台
                            </button>
                        </li>
                    </ul>

                    <!-- 標籤內容 -->
                    <div class="tab-content" id="adminTabsContent">
                        <!-- 前台管理 -->
                        <div class="tab-pane fade show active" id="frontend-content" role="tabpanel"
                            aria-labelledby="frontend-tab">
                            <div class="nav flex-column nav-pills gap-2 mt-2" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <button
                                    class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] === 'productManager') ? 'active' : ''; ?>"
                                    id="v-pills-products-tab" data-bs-toggle="pill" data-bs-target="#products"
                                    type="button" role="tab" aria-controls="products" aria-selected="false">
                                    <i class="bi bi-box-seam me-2"></i> 商品管理
                                </button>
                                <button
                                    class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'orderManager') ? 'active' : ''; ?>"
                                    id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#orders" type="button"
                                    role="tab" aria-controls="orders" aria-selected="true">
                                    <i class="bi bi-cart-check me-2"></i> 購買訂單管理
                                </button>
                                <button
                                    class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'repairManager') ? 'active' : ''; ?>"
                                    id="v-pills-repairs-tab" data-bs-toggle="pill" data-bs-target="#repairs"
                                    type="button" role="tab" aria-controls="repairs" aria-selected="true">
                                    <i class="bi bi-tools me-2"></i> 維修訂單管理
                                </button>
                            </div>
                        </div>

                        <!-- 後台管理 -->
                        <div class="tab-pane fade" id="backend-content" role="tabpanel" aria-labelledby="backend-tab">
                            <div class="nav flex-column nav-pills gap-2 mt-2" id="v-pills-backend-tab" role="tablist"
                                aria-orientation="vertical">
                                <button class="nav-link" id="v-pills-members-tab" data-bs-toggle="pill"
                                    data-bs-target="#members" type="button" role="tab" aria-controls="members"
                                    aria-selected="false">
                                    <i class="bi bi-people me-2"></i> 會員管理
                                </button>
                                <button class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill"
                                    data-bs-target="#settings" type="button" role="tab" aria-controls="settings"
                                    aria-selected="false">
                                    <i class="bi bi-sliders me-2"></i> 系統設置
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 主要內容區 -->
        <div class="col-lg-9">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- 商品管理 -->
                <div class="tab-pane fade <?php echo (!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] === 'productManager')) ? 'show active' : ''; ?>"
                    id="products" role="tabpanel" aria-labelledby="v-pills-products-tab">
                    <?php include "models/backend/productManager.php"; ?>
                </div>
                <!-- 訂單管理 -->
                <div class="tab-pane fade <?php echo (isset($_GET['page']) && $_GET['page'] === 'orderManager') ? 'show active' : ''; ?>"
                    id="orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
                    <?php include "models/backend/orderManager.php"; ?>
                </div>
                <!-- 維修訂單管理 -->
                <div class="tab-pane fade <?php echo (isset($_GET['page']) && $_GET['page'] === 'repairManager') ? 'show active' : ''; ?>"
                    id="repairs" role="tabpanel" aria-labelledby="v-pills-repairs-tab">
                    <?php include "models/backend/repairOrderManager.php"; ?>
                </div>
                <!-- 會員管理 -->
                <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="v-pills-members-tab">
                    <?php include "models/backend/memberManager.php"; ?>
                </div>
                <!-- 系統設置 -->
                <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                    <?php include "models/backend/systemSettings.php"; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // 確保前台/後台管理標籤切換時，相應的內容標籤頁也跟著切換
    document.addEventListener('DOMContentLoaded', function () {
        // 初始化時隱藏後台管理相關的標籤頁
        document.querySelectorAll('#members, #settings').forEach(function (el) {
            el.classList.remove('show', 'active');
        });

        // 前台管理標籤被點擊時
        document.getElementById('frontend-tab').addEventListener('click', function () {
            // 隱藏後台管理相關的標籤頁
            document.querySelectorAll('#members, #settings').forEach(function (el) {
                el.classList.remove('show', 'active');
            });

            // 顯示前台管理的第一個標籤頁（商品管理）
            document.getElementById('products').classList.add('show', 'active');
            document.getElementById('v-pills-products-tab').classList.add('active');
            document.getElementById('v-pills-members-tab').classList.remove('active');
            document.getElementById('v-pills-settings-tab').classList.remove('active');
        });

        // 後台管理標籤被點擊時
        document.getElementById('backend-tab').addEventListener('click', function () {
            // 隱藏前台管理相關的標籤頁
            document.querySelectorAll('#products, #orders, #repairs').forEach(function (el) {
                el.classList.remove('show', 'active');
            });

            // 顯示後台管理的第一個標籤頁（會員管理）
            document.getElementById('members').classList.add('show', 'active');
            document.getElementById('v-pills-members-tab').classList.add('active');
            document.getElementById('v-pills-products-tab').classList.remove('active');
            document.getElementById('v-pills-orders-tab').classList.remove('active');
            document.getElementById('v-pills-repairs-tab').classList.remove('active');
        });
    });
</script>
<?php require_once 'fixedFile/footer.php'; ?>