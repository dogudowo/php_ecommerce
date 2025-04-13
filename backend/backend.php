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
    <?php
    ob_start();
    include 'nav.php';
    $navContent = ob_get_clean();
    $navContent = preg_replace('/<a href="\.\.\/Shopping-cart\.php"[^>]*>.*?<\/a>/s', '<form action="logout.php" method="post" class="mb-0"><button type="submit" class="btn btn-danger">登出</button></form>', $navContent);
    echo $navContent;
    ?>

    <div class="modal fade" id="logoutReminderModal" tabindex="-1" aria-labelledby="logoutReminderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="logoutReminderModalLabel">後台管理提示</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                class="bi bi-exclamation-triangle-fill text-warning me-3" viewBox="0 0 16 16">
                                <path
                                    d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="mb-2">您目前處於後台管理模式。</p>
                            <p class="mb-0">操作完成後，請記得點擊右上角的「<span class="text-danger fw-bold">登出</span>」按鈕，以確保系統安全。</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">我知道了</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container text-center border-bottom border-black my-3">
        <!-- <img src="imgs/title.png" alt="歐印精品" img-fluid> -->
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
                        <?php include 'product_management.php'; ?>
                        <!-- 商品管理內容... -->

                    </div>
                    <div class="tab-pane fade" id="v-pills-orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
                        <?php include 'order_management.php'; ?>
                        <!-- 訂單管理內容... -->
                    </div>
                    <div class="tab-pane fade" id="v-pills-discounts" role="tabpanel"
                        aria-labelledby="v-pills-discounts-tab">
                        <?php include 'discount_management.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <?php include 'footer.php'; ?> -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var logoutModal = new bootstrap.Modal(document.getElementById('logoutReminderModal'), {
                backdrop: 'static'  // 防止點擊背景關閉modal
            });
            logoutModal.show();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>