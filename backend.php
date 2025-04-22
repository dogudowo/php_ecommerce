<?php
session_start();

// 檢查用戶是否已登入，且是管理員
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/navPillsStyles.css">
    <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
    <title>歐印精品-後台管理</title>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="text-center border-bottom border-black pb-3 mb-4">
            <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
            <h1 class="h1">後台管理系統</h1>
        </div>

        <!-- Offcanvas 觸發按鈕：僅在小於 lg 時顯示 -->
        <div class="d-lg-none mb-3">
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                <i class="bi bi-list"></i> 管理選單
            </button>
        </div>

        <div class="row">
            <!-- Offcanvas 側邊欄：小於 lg 時使用 -->
            <div class="offcanvas offcanvas-start w-75" tabindex="-1" id="adminSidebar"
                aria-labelledby="adminSidebarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="adminSidebarLabel">後台管理系統</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="關閉"></button>
                </div>
                <div class="offcanvas-body">
                    <p class="text-muted mb-4">管理員: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                    <div class="nav flex-column nav-pills gap-2" id="v-pills-tab-offcanvas" role="tablist"
                        aria-orientation="vertical">
                        <button class="nav-link active" id="v-pills-products-tab-offcanvas" data-bs-toggle="pill"
                            data-bs-target="#products" type="button" role="tab" aria-controls="products"
                            aria-selected="false" data-bs-dismiss="offcanvas">
                            <i class="bi bi-box-seam me-2"></i> 商品管理
                        </button>
                        <button class="nav-link " id="v-pills-orders-tab-offcanvas" data-bs-toggle="pill"
                            data-bs-target="#orders" type="button" role="tab" aria-controls="orders"
                            aria-selected="true" data-bs-dismiss="offcanvas">
                            <i class="bi bi-cart-check me-2"></i> 訂單管理
                        </button>
                        <button class="nav-link" id="v-pills-members-tab-offcanvas" data-bs-toggle="pill"
                            data-bs-target="#members" type="button" role="tab" aria-controls="members"
                            aria-selected="false" data-bs-dismiss="offcanvas">
                            <i class="bi bi-people me-2"></i> 會員管理
                        </button>
                    </div>
                </div>
            </div>

            <!-- 大螢幕側邊欄 -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title text-center">後台管理系統</h4>
                        <p class="card-text text-muted text-center">管理員:
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </p>
                        <div class="nav flex-column nav-pills gap-2 mt-4" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <button class="nav-link active" id="v-pills-products-tab" data-bs-toggle="pill"
                                data-bs-target="#products" type="button" role="tab" aria-controls="products"
                                aria-selected="false">
                                <i class="bi bi-box-seam me-2"></i> 商品管理
                            </button>
                            <button class="nav-link " id="v-pills-orders-tab" data-bs-toggle="pill"
                                data-bs-target="#orders" type="button" role="tab" aria-controls="orders"
                                aria-selected="true">
                                <i class="bi bi-cart-check me-2"></i> 訂單管理
                            </button>
                            <button class="nav-link" id="v-pills-members-tab" data-bs-toggle="pill"
                                data-bs-target="#members" type="button" role="tab" aria-controls="members"
                                aria-selected="false">
                                <i class="bi bi-people me-2"></i> 會員管理
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 主要內容區 -->
            <div class="col-lg-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <!-- 商品管理 -->
                    <?php include "backend/productManager.php"; ?>
                    <!-- 訂單管理 -->
                    <?php include "backend/orderManager.php"; ?>
                    <!-- 會員管理 -->
                    <?php include "backend/memberManager.php"; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ"
        crossorigin="anonymous"></script>
</body>

</html>