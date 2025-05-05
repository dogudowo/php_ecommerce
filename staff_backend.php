<?php
// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ROUTER') && (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'staff')) {
    header('Location: login.php');
    exit;
}

// 定義常量，表示這是員工後台
define('INCLUDED_FROM_ADMIN', true);

// 定義員工可以訪問的頁面
$staff_accessible = ['orderManager', 'repairManager'];
$current_page = isset($_GET['page']) ? $_GET['page'] : 'orderManager'; // 預設顯示訂單管理

// 確保只能訪問允許的頁面
if (!in_array($current_page, $staff_accessible)) {
    $current_page = 'orderManager';
}
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
                        員工: <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </p>

                    <!-- 員工導航 -->
                    <div class="nav flex-column nav-pills gap-2 mt-2" id="v-pills-tab" role="tablist"
                        aria-orientation="vertical">
                        <button class="nav-link <?php echo ($current_page === 'orderManager') ? 'active' : ''; ?>"
                            id="v-pills-orders-tab" data-bs-toggle="pill" data-bs-target="#orders" type="button"
                            role="tab" aria-controls="orders" aria-selected="true"
                            onclick="window.location.href='backend.php?page=orderManager'">
                            <i class="bi bi-cart-check me-2"></i> 購買訂單管理
                        </button>
                        <button class="nav-link <?php echo ($current_page === 'repairManager') ? 'active' : ''; ?>"
                            id="v-pills-repairs-tab" data-bs-toggle="pill" data-bs-target="#repairs" type="button"
                            role="tab" aria-controls="repairs" aria-selected="true"
                            onclick="window.location.href='backend.php?page=repairManager'">
                            <i class="bi bi-tools me-2"></i> 維修訂單管理
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 主要內容區 -->
        <div class="col-lg-9">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- 訂單管理 -->
                <div class="tab-pane fade <?php echo ($current_page === 'orderManager') ? 'show active' : ''; ?>"
                    id="orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
                    <?php include "models/backend/orderManager.php"; ?>
                </div>
                <!-- 維修訂單管理 -->
                <div class="tab-pane fade <?php echo ($current_page === 'repairManager') ? 'show active' : ''; ?>"
                    id="repairs" role="tabpanel" aria-labelledby="v-pills-repairs-tab">
                    <?php include "models/backend/repairOrderManager.php"; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'fixedFile/footer.php'; ?>