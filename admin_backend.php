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
    <div class="d-none d-lg-block">
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
                            <div class="tab-pane  show active" id="frontend-content" role="tabpanel"
                                aria-labelledby="frontend-tab">
                                <style>
                                    .nav-pills .nav-link {
                                        text-align: left;
                                        padding-left: 2rem;
                                    }
                                </style>
                                <div class="nav flex-column nav-pills gap-2 mt-2" id="v-pills-tab" role="tablist"
                                    aria-orientation="vertical">
                                    <button
                                        class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'carouselManager') ? 'active' : ''; ?>"
                                        id="v-pills-carousel-tab" data-bs-toggle="pill" data-bs-target="#carousel"
                                        type="button" role="tab" aria-controls="carousel" aria-selected="false">
                                        <i class="bi bi-images me-2"></i> 輪播圖管理
                                    </button>
                                    <button
                                        class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'brandManager') ? 'active' : ''; ?>"
                                        id="v-pills-brand-tab" data-bs-toggle="pill" data-bs-target="#brand"
                                        type="button" role="tab" aria-controls="brand" aria-selected="false">
                                        <i class="bi bi-award me-2"></i> 品牌介紹頁面管理
                                    </button>
                                    <button
                                        class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'instructionManager') ? 'active' : ''; ?>"
                                        id="v-pills-instruction-tab" data-bs-toggle="pill" data-bs-target="#instruction"
                                        type="button" role="tab" aria-controls="instruction" aria-selected="false">
                                        <i class="bi bi-info-circle me-2"></i> 購物說明頁面管理
                                    </button>
                                    <button
                                        class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'repairManager') ? 'active' : ''; ?>"
                                        id="v-pills-repair-page-tab" data-bs-toggle="pill" data-bs-target="#repair-page"
                                        type="button" role="tab" aria-controls="repair-page" aria-selected="false">
                                        <i class="bi bi-tools me-2"></i> 維修頁面管理
                                    </button>
                                    <button
                                        class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'aboutManager') ? 'active' : ''; ?>"
                                        id="v-pills-about-tab" data-bs-toggle="pill" data-bs-target="#about"
                                        type="button" role="tab" aria-controls="about" aria-selected="false">
                                        <i class="bi bi-building me-2"></i> 關於我們頁面管理
                                    </button>
                                </div>
                            </div>

                            <!-- 後台管理 -->
                            <div class="tab-pane " id="backend-content" role="tabpanel" aria-labelledby="backend-tab">
                                <div class="nav flex-column nav-pills gap-2 mt-2" id="v-pills-backend-tab"
                                    role="tablist" aria-orientation="vertical">
                                    <button
                                        class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] === 'productManager') ? 'active' : ''; ?>"
                                        id="v-pills-products-tab" data-bs-toggle="pill" data-bs-target="#products"
                                        type="button" role="tab" aria-controls="products" aria-selected="false">
                                        <i class="bi bi-box-seam me-2"></i> 商品管理
                                    </button>
                                    <button
                                        class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'orderManager') ? 'active' : ''; ?>"
                                        id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#orders"
                                        type="button" role="tab" aria-controls="orders" aria-selected="true">
                                        <i class="bi bi-cart-check me-2"></i> 購買訂單管理
                                    </button>
                                    <button
                                        class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'repairManager') ? 'active' : ''; ?>"
                                        id="v-pills-repairs-tab" data-bs-toggle="pill" data-bs-target="#repairs"
                                        type="button" role="tab" aria-controls="repairs" aria-selected="true">
                                        <i class="bi bi-tools me-2"></i> 維修訂單管理
                                    </button>
                                    <button class="nav-link" id="v-pills-members-tab" data-bs-toggle="pill"
                                        data-bs-target="#members" type="button" role="tab" aria-controls="members"
                                        aria-selected="false">
                                        <i class="bi bi-people me-2"></i> 會員管理
                                    </button>
                                    <button class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill"
                                        data-bs-target="#settings" type="button" role="tab" aria-controls="settings"
                                        aria-selected="false">
                                        <i class="bi bi-sliders me-2"></i>社群連結設置
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
                    <!-- 輪播圖編輯 -->
                    <div class="tab-pane fade" id="carousel" role="tabpanel" aria-labelledby="v-pills-carousel-tab">
                        <?php include "models/backend/carouselManager.php"; ?>
                    </div>
                    <!-- 維修頁面管理 -->
                    <div class="tab-pane fade" id="repair-page" role="tabpanel"
                        aria-labelledby="v-pills-repair-page-tab">
                        <?php include "models/backend/repairManager.php"; ?>
                    </div>
                    <!-- 購物說明頁面管理 -->
                    <div class="tab-pane fade" id="instruction" role="tabpanel"
                        aria-labelledby="v-pills-instruction-tab">
                        <?php include "models/backend/instructionManager.php"; ?>
                    </div>
                    <!-- 品牌介紹頁面管理 -->
                    <div class="tab-pane fade" id="brand" role="tabpanel" aria-labelledby="v-pills-brand-tab">
                        <?php include "models/backend/brandManager.php"; ?>
                    </div>
                    <!-- 關於我們頁面管理 -->
                    <div class="tab-pane fade" id="about" role="tabpanel" aria-labelledby="v-pills-about-tab">
                        <?php include "models/backend/aboutManager.php"; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 確保前台/後台管理標籤切換時，相應的內容標籤頁也跟著切換
    document.addEventListener('DOMContentLoaded', function () {
        // 初始化時顯示後台管理的商品管理標籤頁
        document.querySelectorAll('#members, #settings, #carousel, #orders, #repairs').forEach(function (el) {
            el.classList.remove('show', 'active');
        });

        // 預設顯示後台的商品管理頁面
        document.getElementById('products').classList.add('show', 'active');
        document.getElementById('v-pills-products-tab').classList.add('active');
        document.getElementById('frontend-content').classList.remove('show', 'active');
        document.getElementById('backend-content').classList.add('show', 'active');
        document.getElementById('frontend-tab').classList.remove('active');
        document.getElementById('backend-tab').classList.add('active');

        // 根據URL參數決定是否顯示輪播圖管理標籤頁
        if (!(window.location.search.includes('page=carouselManager'))) {
            document.getElementById('carousel').classList.remove('show', 'active');
        }

        // 前台管理標籤被點擊時
        document.getElementById('frontend-tab').addEventListener('click', function () {
            // 隱藏後台管理相關的標籤頁
            document.querySelectorAll('#members, #settings, #products, #orders, #repairs').forEach(function (el) {
                el.classList.remove('show', 'active');
            });

            // 顯示前台管理的標籤頁（預設顯示輪播圖管理）
            document.getElementById('carousel').classList.add('show', 'active');
            document.getElementById('v-pills-carousel-tab').classList.add('active');

            // 移除後台管理標籤的激活狀態
            document.getElementById('v-pills-members-tab').classList.remove('active');
            document.getElementById('v-pills-settings-tab').classList.remove('active');
            document.getElementById('v-pills-products-tab').classList.remove('active');
            document.getElementById('v-pills-orders-tab').classList.remove('active');
            document.getElementById('v-pills-repairs-tab').classList.remove('active');

            // 移除其他前台管理標籤頁的激活狀態（除了輪播圖管理）
            document.getElementById('v-pills-repair-page-tab').classList.remove('active');
            document.getElementById('v-pills-instruction-tab').classList.remove('active');
            document.getElementById('v-pills-brand-tab').classList.remove('active');
            document.getElementById('v-pills-about-tab').classList.remove('active');
            document.getElementById('repair-page').classList.remove('show', 'active');
            document.getElementById('instruction').classList.remove('show', 'active');
            document.getElementById('brand').classList.remove('show', 'active');
            document.getElementById('about').classList.remove('show', 'active');
        });

        // 後台管理標籤被點擊時
        document.getElementById('backend-tab').addEventListener('click', function () {
            // 隱藏所有前台管理相關的標籤頁
            document.getElementById('carousel').classList.remove('show', 'active');
            document.getElementById('repair-page').classList.remove('show', 'active');
            document.getElementById('instruction').classList.remove('show', 'active');
            document.getElementById('brand').classList.remove('show', 'active');
            document.getElementById('about').classList.remove('show', 'active');

            // 移除所有前台管理標籤的激活狀態
            document.getElementById('v-pills-carousel-tab').classList.remove('active');
            document.getElementById('v-pills-repair-page-tab').classList.remove('active');
            document.getElementById('v-pills-instruction-tab').classList.remove('active');
            document.getElementById('v-pills-brand-tab').classList.remove('active');
            document.getElementById('v-pills-about-tab').classList.remove('active');

            // 顯示後台管理的第一個標籤頁（商品管理）
            document.getElementById('products').classList.add('show', 'active');
            document.getElementById('v-pills-products-tab').classList.add('active');
            document.getElementById('v-pills-members-tab').classList.remove('active');
            document.getElementById('v-pills-settings-tab').classList.remove('active');
            document.getElementById('v-pills-orders-tab').classList.remove('active');
            document.getElementById('v-pills-repairs-tab').classList.remove('active');
        });

        // 確保前台管理標籤頁在相應URL參數時顯示
        if (window.location.search.includes('page=carouselManager') ||
            window.location.search.includes('page=repairManager') ||
            window.location.search.includes('page=instructionManager') ||
            window.location.search.includes('page=brandManager') ||
            window.location.search.includes('page=aboutManager')) {

            // 隱藏所有後台管理標籤頁
            document.querySelectorAll('#products, #orders, #repairs, #members, #settings').forEach(function (el) {
                el.classList.remove('show', 'active');
            });

            // 隱藏所有前台管理標籤頁
            document.querySelectorAll('#carousel, #repair-page, #instruction, #brand, #about').forEach(function (el) {
                el.classList.remove('show', 'active');
            });

            // 移除所有標籤的激活狀態
            document.querySelectorAll('#v-pills-carousel-tab, #v-pills-repair-page-tab, #v-pills-instruction-tab, #v-pills-brand-tab, #v-pills-about-tab, #v-pills-products-tab, #v-pills-orders-tab, #v-pills-repairs-tab, #v-pills-members-tab, #v-pills-settings-tab').forEach(function (el) {
                el.classList.remove('active');
            });

            // 根據URL參數顯示相應的標籤頁
            if (window.location.search.includes('page=carouselManager')) {
                document.getElementById('carousel').classList.add('show', 'active');
                document.getElementById('v-pills-carousel-tab').classList.add('active');
            } else if (window.location.search.includes('page=repairManager')) {
                document.getElementById('repair-page').classList.add('show', 'active');
                document.getElementById('v-pills-repair-page-tab').classList.add('active');
            } else if (window.location.search.includes('page=instructionManager')) {
                document.getElementById('instruction').classList.add('show', 'active');
                document.getElementById('v-pills-instruction-tab').classList.add('active');
            } else if (window.location.search.includes('page=brandManager')) {
                document.getElementById('brand').classList.add('show', 'active');
                document.getElementById('v-pills-brand-tab').classList.add('active');
            } else if (window.location.search.includes('page=aboutManager')) {
                document.getElementById('about').classList.add('show', 'active');
                document.getElementById('v-pills-about-tab').classList.add('active');
            }

            // 激活前台管理標籤，取消激活後台管理標籤
            document.getElementById('frontend-content').classList.add('show', 'active');
            document.getElementById('backend-content').classList.remove('show', 'active');
            document.getElementById('frontend-tab').classList.add('active');
            document.getElementById('backend-tab').classList.remove('active');
        }

        // 確保商品管理、訂單管理和維修訂單管理標籤頁在相應URL參數時顯示
        if (window.location.search.includes('page=productManager') ||
            window.location.search.includes('page=orderManager') ||
            window.location.search.includes('page=repairManager')) {
            // 隱藏所有前台管理標籤頁
            document.querySelectorAll('#carousel, #repair-page, #instruction, #brand, #about').forEach(function (el) {
                el.classList.remove('show', 'active');
            });

            // 移除所有前台管理標籤的激活狀態
            document.querySelectorAll('#v-pills-carousel-tab, #v-pills-repair-page-tab, #v-pills-instruction-tab, #v-pills-brand-tab, #v-pills-about-tab').forEach(function (el) {
                el.classList.remove('active');
            });

            // 激活後台管理標籤，取消激活前台管理標籤
            document.getElementById('frontend-content').classList.remove('show', 'active');
            document.getElementById('backend-content').classList.add('show', 'active');
            document.getElementById('frontend-tab').classList.remove('active');
            document.getElementById('backend-tab').classList.add('active');
        }
    });
</script>
<?php require_once 'fixedFile/footer.php'; ?>