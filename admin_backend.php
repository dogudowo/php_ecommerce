<?php
// 防止直接訪問此文件
session_start();
if (!defined('INCLUDED_FROM_ROUTER') && (empty($_SESSION['logged_in']) || $_SESSION['user_role'] !== 2)) {
    header('Location: login.php');
    exit;
}
// 定義常量，表示這是管理員後台
define('INCLUDED_FROM_ADMIN', true);
require_once 'header.php';

// 導航配置
$sections = [
    'frontend' => [
        'tab_id' => 'frontend-tab',
        'pages' => ['carouselManager', 'brandManager', 'instructionManager', 'repairManager', 'aboutManager'],
    ],
    'backend' => [
        'tab_id' => 'backend-tab',
        'pages' => ['productManager', 'orderManager', 'repairOrderManager', 'memberManager', 'linkManager', 'logoManager'],
    ],
];
// 標籤名稱與圖示對應
$labels = [
    'carouselManager' => '輪播圖管理',
    'brandManager' => '品牌介紹',
    'instructionManager' => '購物說明',
    'repairManager' => '維修頁面',
    'aboutManager' => '關於我們',
    'productManager' => '商品管理',
    'orderManager' => '訂單管理',
    'repairOrderManager' => '維修訂單',
    'memberManager' => '會員管理',
    'linkManager' => '社群連結',
    'logoManager' => '品牌Logo',
];
$icons = [
    'carouselManager' => 'bi-images',
    'brandManager' => 'bi-award',
    'instructionManager' => 'bi-info-circle',
    'repairManager' => 'bi-tools',
    'aboutManager' => 'bi-building',
    'productManager' => 'bi-box-seam',
    'orderManager' => 'bi-cart-check',
    'repairOrderManager' => 'bi-tools',
    'memberManager' => 'bi-people',
    'linkManager' => 'bi-sliders',
    'logoManager' => 'bi-image',
];

// 讀取當前頁面參數，預設為 productManager
$current = $_GET['page'] ?? 'productManager';
?>
<div class="container-fluid mt-4 px-5 mx-5 mx-auto">
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
            <!-- 側邊欄 -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h4 class="card-title">後台管理系統</h4>
                        <p class="text-muted">管理員: <?= htmlspecialchars($_SESSION['user_name']); ?></p>
                        <!-- 主標籤 -->
                        <ul class="nav nav-tabs my-3" id="adminTabs" role="tablist">
                            <?php foreach ($sections as $key => $sec): ?>
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link<?= in_array($current, $sec['pages']) ? ' active' : ''; ?> px-2 mx-0"
                                        id="<?= $sec['tab_id']; ?>" type="button"
                                        onclick="location.search='?page=<?= $sec['pages'][0]; ?>'">
                                        <i class="bi <?= $key === 'frontend' ? 'bi-display' : 'bi-gear'; ?> me-1"></i>
                                        <?= $key === 'frontend' ? '前台' : '後台'; ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <!-- 子選單 -->
                        <?php foreach ($sections as $key => $sec): ?>
                            <div class="tab-content" data-section="<?= $key; ?>"
                                style="display: <?= in_array($current, $sec['pages']) ? 'block' : 'none'; ?>;">
                                <div class="nav flex-column nav-pills gap-2">
                                    <?php foreach ($sec['pages'] as $pageId): ?>
                                        <button class="nav-link<?= $current === $pageId ? ' active' : ''; ?>" type="button"
                                            onclick="location.search='?page=<?= $pageId; ?>'">
                                            <i class="bi <?= $icons[$pageId]; ?> me-2"></i>
                                            <?= $labels[$pageId]; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- 主要內容區 -->
            <div class="col-lg-9">
                <div id="page-content">
                    <?php
                    switch ($current) {
                        case 'carouselManager':
                            include 'backend/carouselManager.php';
                            break;
                        case 'brandManager':
                            include 'backend/brandManager.php';
                            break;
                        case 'instructionManager':
                            include 'backend/instructionManager.php';
                            break;
                        case 'repairManager':
                            include 'backend/repairManager.php';
                            break;
                        case 'aboutManager':
                            include 'backend/aboutManager.php';
                            break;
                        case 'orderManager':
                            include 'backend/backend/orderManager.php';
                            break;
                        case 'repairOrderManager':
                            include 'backend/backend/repairOrderManager.php';
                            break;
                        case 'memberManager':
                            include 'backend/memberManager.php';
                            break;
                        case 'linkManager':
                            include 'backend/linkManager.php';
                            break;
                        case 'logoManager':
                            include 'backend/logoManager.php';
                            break;
                        default:
                            include 'backend/productManager.php';
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>