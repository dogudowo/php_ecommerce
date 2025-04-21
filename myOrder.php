<?php
session_start();

// 檢查用戶是否已登入，如果沒有則顯示Bootstrap Modal提示並重定向到登入頁面
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // 保存當前頁面作為登入後重定向的目標
    $_SESSION['redirect_to'] = 'myOrder.php';
    // 輸出完整的HTML頁面，包含Bootstrap Modal
    ?>
    <!DOCTYPE html>
    <html lang="zh-TW">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
        <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
        <title>歐印精品-需要登入</title>
    </head>

    <body>
        <!-- 登入提示的 Modal -->
        <div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">需要登入</h5>
                    </div>
                    <div class="modal-body">
                        <p>請先登入以查看您的訂單！</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="goToLoginBtn">前往登入</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ"
            crossorigin="anonymous"></script>
        <script>
            // 頁面加載後顯示Modal
            document.addEventListener('DOMContentLoaded', function () {
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();

                // 點擊前往登入按鈕時跳轉
                document.getElementById('goToLoginBtn').addEventListener('click', function () {
                    window.location.href = 'login.php';
                });
            });
        </script>
    </body>

    </html>
    <?php
    exit;
}

// 如果是管理員，重定向到後台管理頁面
if ($_SESSION['user_role'] === 'admin') {
    header('Location: backend.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
    <style>
        html,
        body {
            overflow-x: hidden;
            overflow-y: scroll;
            /* 防止橫向滾動條 */
        }
    </style>
    <title>歐印精品-我的訂單</title>
</head>

<body>
    <?php include "header.php"; ?>
    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" img-fluid>
        <h1 class="h1">我的訂單</h1>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h3>會員資訊</h3>
                    </div>
                    <div class="card-body">
                        <h4><?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
                        <p class="mb-0">
                            <strong>身份：</strong> 會員
                        </p>
                        <p class="mb-0">
                            <strong>手機號碼：</strong> <?php echo htmlspecialchars($_SESSION['user_phone']); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h3>您的訂單</h3>
                    </div>
                    <div class="card-body">
                        <!-- 一般會員看到的訂單列表 -->
                        <?php if ($_SESSION['user_phone'] === '0912345679'): ?>
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">訂單號碼: ORD-20230001</h5>
                                        <small>2023/12/15</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-3" style="width: 60px; height: 60px;">
                                            <img src="imgs/products/iphone_case.jpg" alt="iPhone 玻璃貼" class="img-fluid"
                                                style="max-height: 100%;">
                                        </div>
                                        <div>
                                            <p class="mb-1">【網皮直營特選】秒貼除塵貼 iPhone滿版曲面9H玻璃保護貼 護眼防藍 蘋果 13/14/15/16 玻璃貼</p>
                                            <p class="mb-0 text-secondary">規格: 高清亮面,14 = 13 = 13 PRO</p>
                                            <p class="mb-0">數量: 1 件</p>
                                            <p class="mb-0"><del class="text-secondary">$300</del> <span
                                                    class="text-danger fw-bold">$69</span></p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-2 mt-3 pt-2 border-top">
                                        <div class="me-3" style="width: 60px; height: 60px;">
                                            <img src="imgs/products/dhc_lip.jpg" alt="DHC護唇膏" class="img-fluid"
                                                style="max-height: 100%;">
                                        </div>
                                        <div>
                                            <p class="mb-1">【網皮直營特選】DHC純欖護唇膏1.5g 官方直營</p>
                                            <p class="mb-0 text-secondary">規格: 一入</p>
                                            <p class="mb-0">數量: 1 件</p>
                                            <p class="mb-0"><del class="text-secondary">$350</del> <span
                                                    class="text-danger fw-bold">$199</span></p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                        <div>
                                            <span class="text-success">狀態: 已完成</span>
                                        </div>
                                        <div class="text-end">
                                            <p class="mb-0">訂單金額: <span class="text-danger fw-bold">$238</span></p>
                                            <button class="btn btn-outline-secondary btn-sm mt-2">再買一次</button>
                                            <button class="btn btn-outline-primary btn-sm mt-2">聯絡賣家</button>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="card-text">目前沒有訂單記錄。</p>
                        <?php endif; ?>
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