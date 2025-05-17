<?php
// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ROUTER') && (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 1)) {
    header('Location: login.php');
    exit;
}

// 定義常量，表示這是員工後台
define('INCLUDED_FROM_ADMIN', true);

// 定義員工可以訪問的頁面
$staff_accessible = ['orderManager', 'repairManager', 'memberManager'];
$current_page = isset($_GET['page']) ? $_GET['page'] : 'orderManager'; // 預設顯示訂單管理

// 確保只能訪問允許的頁面
if (!in_array($current_page, $staff_accessible)) {
    $current_page = 'orderManager';
}

// 標籤名稱與圖示對應 (員工後台)
$labels = [
    'orderManager' => '購買訂單管理',
    'repairManager' => '維修訂單管理',
    'memberManager' => '會員管理',
];

$icons = [
    'orderManager' => 'bi-cart-check',
    'repairManager' => 'bi-tools',
    'memberManager' => 'bi-people',
];
?>
<?php require_once 'header.php'; ?>
<div class="container-fulid mt-3 mt-4 px-5 mx-5 mx-auto">
    <!-- 小螢幕提示訊息 -->
    <div class=" d-lg-none mb-4">
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>請注意</h4>
            <p>後台管理系統需要較大的螢幕空間才能正常操作。</p>
            <hr>
            <p class="mb-0">請使用電腦或平板電腦（橫向）開啟此頁面以獲得最佳體驗。</p>
        </div>
    </div>

    <!-- 大螢幕才顯示的內容 -->
    <div class="d-none d-lg-block">
        <div class="row">
            <!-- 大螢幕側邊欄 -->
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title text-center">後台管理系統</h4>
                        <p class="card-text text-muted text-center">
                            員工: <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </p>

                        <!-- 員工導航 -->
                        <div class="nav flex-column nav-pills gap-2 mt-2" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <?php foreach ($staff_accessible as $pageId): ?>
                                <button class="nav-link<?= $current_page === $pageId ? ' active' : ''; ?>" type="button"
                                    onclick="location.search='?page=<?= $pageId; ?>'">
                                    <i class="bi <?= $icons[$pageId]; ?> me-2"></i>
                                    <?= $labels[$pageId]; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 主要內容區 -->
            <div class="col-lg-9">
                <div id="page-content">
                    <?php
                    switch ($current_page) {
                        case 'orderManager':
                            include 'models/backend/orderManager.php';
                            break;
                        case 'repairManager':
                            include 'models/backend/repairOrderManager.php';
                            break;
                        case 'memberManager':
                            include 'models/memberManager.php';
                            break;
                        default:
                            include 'models/backend/orderManager.php';
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>