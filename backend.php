<?php
session_start();

// 檢查使用者是否已登入且身份為管理員
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // 如果不是管理員，重定向到登入頁面
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="icon" href="./imgs/webimg.ico" />
    <title>歐印精品-後台管理</title>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" img-fluid>
        <h1 class="h1">後台管理</h1>
    </div>

    <div class="container">
        <!-- Offcanvas trigger button for small screens -->
        <div class="d-md-none mb-3">
            <button class="btn btn-success" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNav"
                aria-controls="#offcanvasNav">
                開啟選單
            </button>
        </div>

        <!-- Offcanvas component for navigation -->
        <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="offcanvasNav" aria-labelledby="offcanvasNavLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavLabel">選單</h5>
            </div>
            <div class="offcanvas-body">
                <div class="nav flex-column nav-pills gap-2" id="v-pills-tabOffcanvas" role="tablist"
                    aria-orientation="vertical">
                    <a class="btn btn-success active" id="v-pills-products-tabOffcanvas" data-bs-toggle="pill"
                        href="#v-pills-products" role="tab" aria-controls="v-pills-products"
                        aria-selected="true">商品管理</a>
                    <a class="btn btn-success" id="v-pills-orders-tabOffcanvas" data-bs-toggle="pill"
                        href="#v-pills-orders" role="tab" aria-controls="v-pills-orders" aria-selected="false">訂單管理</a>
                    <a class="btn btn-success" id="v-pills-discounts-tabOffcanvas" data-bs-toggle="pill"
                        href="#v-pills-discounts" role="tab" aria-controls="v-pills-discounts"
                        aria-selected="false">優惠管理</a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-3 d-none d-md-block">
                <div class="nav flex-column nav-pills me-3 gap-1" id="v-pills-tab" role="tablist"
                    aria-orientation="vertical">
                    <a class="btn btn-success  active" id="v-pills-products-tab" data-bs-toggle="pill"
                        href="#v-pills-products" role="tab" aria-controls="v-pills-products"
                        aria-selected="true">商品管理</a>
                    <a class="btn btn-success" id="v-pills-orders-tab" data-bs-toggle="pill" href="#v-pills-orders"
                        role="tab" aria-controls="v-pills-orders" aria-selected="false">訂單管理</a>
                    <a class="btn btn-success" id="v-pills-discounts-tab" data-bs-toggle="pill"
                        href="#v-pills-discounts" role="tab" aria-controls="v-pills-discounts"
                        aria-selected="false">優惠管理</a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-products" role="tabpanel"
                        aria-labelledby="v-pills-products-tab">
                        商品管理內容...

                    </div>
                    <div class="tab-pane fade" id="v-pills-orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
                        訂單管理內容...
                    </div>
                    <div class="tab-pane fade" id="v-pills-discounts" role="tabpanel"
                        aria-labelledby="v-pills-discounts-tab">
                        優惠管理內容...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>