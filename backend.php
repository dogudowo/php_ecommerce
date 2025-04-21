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
                        <button class="nav-link active" id="v-pills-orders-tab-offcanvas" data-bs-toggle="pill"
                            data-bs-target="#orders" type="button" role="tab" aria-controls="orders"
                            aria-selected="true" data-bs-dismiss="offcanvas">
                            <i class="bi bi-cart-check me-2"></i> 訂單管理
                        </button>
                        <button class="nav-link" id="v-pills-products-tab-offcanvas" data-bs-toggle="pill"
                            data-bs-target="#products" type="button" role="tab" aria-controls="products"
                            aria-selected="false" data-bs-dismiss="offcanvas">
                            <i class="bi bi-box-seam me-2"></i> 商品管理
                        </button>
                        <button class="nav-link" id="v-pills-members-tab-offcanvas" data-bs-toggle="pill"
                            data-bs-target="#members" type="button" role="tab" aria-controls="members"
                            aria-selected="false" data-bs-dismiss="offcanvas">
                            <i class="bi bi-people me-2"></i> 會員管理
                        </button>
                        <button class="nav-link" id="v-pills-coupons-tab-offcanvas" data-bs-toggle="pill"
                            data-bs-target="#coupons" type="button" role="tab" aria-controls="coupons"
                            aria-selected="false" data-bs-dismiss="offcanvas">
                            <i class="bi bi-ticket-perforated me-2"></i> 優惠券管理
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
                            <button class="nav-link active" id="v-pills-orders-tab" data-bs-toggle="pill"
                                data-bs-target="#orders" type="button" role="tab" aria-controls="orders"
                                aria-selected="true">
                                <i class="bi bi-cart-check me-2"></i> 訂單管理
                            </button>
                            <button class="nav-link" id="v-pills-products-tab" data-bs-toggle="pill"
                                data-bs-target="#products" type="button" role="tab" aria-controls="products"
                                aria-selected="false">
                                <i class="bi bi-box-seam me-2"></i> 商品管理
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
                    <!-- 訂單管理 -->
                    <div class="tab-pane fade show active" id="orders" role="tabpanel"
                        aria-labelledby="v-pills-orders-tab">
                        <h3 class="mb-3">訂單管理</h3>
                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">所有訂單</h5>
                                <div class="input-group w-50">
                                    <input type="text" class="form-control" placeholder="搜尋訂單...">
                                    <button class="btn btn-outline-secondary" type="button">搜尋</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>訂單編號</th>
                                                <th>客戶</th>
                                                <th>金額</th>
                                                <th>狀態</th>
                                                <th>日期</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>ORD-20230001</td>
                                                <td>李小明</td>
                                                <td>NT$5,800</td>
                                                <td><span class="badge bg-success">已完成</span></td>
                                                <td>2023/12/15</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info">查看</button>
                                                    <button class="btn btn-sm btn-outline-warning">編輯</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>ORD-20230002</td>
                                                <td>張美美</td>
                                                <td>NT$3,200</td>
                                                <td><span class="badge bg-warning text-dark">處理中</span></td>
                                                <td>2023/12/20</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info">查看</button>
                                                    <button class="btn btn-sm btn-outline-warning">編輯</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>ORD-20230003</td>
                                                <td>王大明</td>
                                                <td>NT$7,600</td>
                                                <td><span class="badge bg-danger">已取消</span></td>
                                                <td>2023/12/22</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info">查看</button>
                                                    <button class="btn btn-sm btn-outline-warning">編輯</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>顯示 1 到 3 筆，共 3 筆</span>
                                    <nav>
                                        <ul class="pagination mb-0">
                                            <li class="page-item disabled"><a class="page-link" href="#">上一頁</a>
                                            </li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item disabled"><a class="page-link" href="#">下一頁</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 商品管理 -->
                    <div class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="v-pills-products-tab">
                        <h3 class="mb-3">商品管理</h3>
                        <div class="mb-3">
                            <button class="btn btn-primary">新增商品</button>
                        </div>
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">商品列表</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>商品圖片</th>
                                                <th>商品名稱</th>
                                                <th>價格</th>
                                                <th>庫存</th>
                                                <th>狀態</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td><img src="https://via.placeholder.com/50" alt="產品圖片"></td>
                                                <td>Legend Walker 5109-48 行李箱</td>
                                                <td>NT$5,800</td>
                                                <td>15</td>
                                                <td><span class="badge bg-success">上架中</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-warning">編輯</button>
                                                    <button class="btn btn-sm btn-outline-danger">下架</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td><img src="https://via.placeholder.com/50" alt="產品圖片"></td>
                                                <td>RIMOWA Classic Cabin S 行李箱</td>
                                                <td>NT$74,500</td>
                                                <td>5</td>
                                                <td><span class="badge bg-success">上架中</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-warning">編輯</button>
                                                    <button class="btn btn-sm btn-outline-danger">下架</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 會員管理 -->
                    <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="v-pills-members-tab">
                        <h3 class="mb-3">會員管理</h3>
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">會員列表</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>姓名</th>
                                                <th>電話</th>
                                                <th>註冊日期</th>
                                                <th>訂單數</th>
                                                <th>狀態</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>李小明</td>
                                                <td>0912345678</td>
                                                <td>2023/10/15</td>
                                                <td>1</td>
                                                <td><span class="badge bg-success">正常</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info">查看</button>
                                                    <button class="btn btn-sm btn-outline-warning">編輯</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>張美美</td>
                                                <td>0923456789</td>
                                                <td>2023/11/20</td>
                                                <td>1</td>
                                                <td><span class="badge bg-success">正常</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info">查看</button>
                                                    <button class="btn btn-sm btn-outline-warning">編輯</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
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