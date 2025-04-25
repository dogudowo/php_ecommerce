<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="css/navPillsStyles.css">
    <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
    <title>歐印精品-首頁</title>
</head>

<body>
    <?php include "header.php"; ?>
    <!-- 輪播照片 -->
    <div class="container">
        <div id="homeCarsousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators w-75 justify-content-center mx-auto align-items-center">
                <button type="button" data-bs-target="#homeCarsousel" data-bs-slide-to="0" class="active"
                    aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#homeCarsousel" data-bs-slide-to="1"
                    aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#homeCarsousel" data-bs-slide-to="2"
                    aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#homeCarsousel" data-bs-slide-to="3"
                    aria-label="Slide 4"></button>
            </div>
            <div class="carousel-inner w-100 justify-content-center mx-auto align-items-center">
                <div class="carousel-item active">
                    <img src="imgs/carousel/8e0df82ae92ea1f199311609bef37ade_1.jpg"
                        class="d-block w-100 justify-content-center" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="imgs/carousel/2025movingwith.jpg" class="d-block w-100 justify-content-center" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="imgs/carousel/6021.jpg" class="d-block w-100 justify-content-center" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="imgs/carousel/allen1.jpg" class="d-block w-100 justify-content-center" alt="...">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#homeCarsousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homeCarsousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <br>
    <!-- 商品區域 -->
    <div class="container mt-3">
        <!-- Offcanvas trigger button for small screens -->
        <div class="d-md-none mb-3">
            <button class="btn btn-success" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasNavProducts" aria-controls="offcanvasNavProducts">
                商品選項
            </button>
        </div>

        <!-- Offcanvas component for navigation -->
        <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="offcanvasNavProducts"
            aria-labelledby="offcanvasNavProductsLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavProductsLabel">商品選項</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="關閉"></button>
            </div>
            <div class="offcanvas-body">
                <div class="nav flex-column nav-pills gap-2" id="v-pills-tabOffcanvas" role="tablist"
                    aria-orientation="vertical">
                    <button class="nav-link active" id="v-pills-products-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-products" type="button" role="tab" aria-controls="v-pills-products"
                        aria-selected="true" data-bs-dismiss="offcanvas">
                        全部行李箱
                    </button>
                    <button class="nav-link" id="v-pills-accessories-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-accessories" type="button" role="tab"
                        aria-controls="v-pills-accessories" aria-selected="false" data-bs-dismiss="offcanvas">
                        行李箱配件
                    </button>
                    <button class="nav-link" id="v-pills-travel-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-travel" type="button" role="tab" aria-controls="v-pills-travel"
                        aria-selected="false" data-bs-dismiss="offcanvas">
                        旅遊周邊
                    </button>
                    <button class="nav-link" id="v-pills-selected-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-selected" type="button" role="tab" aria-controls="v-pills-selected"
                        aria-selected="false" data-bs-dismiss="offcanvas">
                        歐印嚴選
                    </button>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <!-- Sidebar navigation for medium and larger screens -->
            <div class="col-md-3 d-none d-md-block">
                <div class="nav flex-column nav-pills me-3 gap-1" id="v-pills-tab" role="tablist"
                    aria-orientation="vertical">
                    <button class="nav-link active" id="v-pills-products-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-products" type="button" role="tab" aria-controls="v-pills-products"
                        aria-selected="true">
                        全部行李箱
                    </button>
                    <button class="nav-link" id="v-pills-accessories-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-accessories" type="button" role="tab"
                        aria-controls="v-pills-accessories" aria-selected="false">
                        行李箱配件
                    </button>
                    <button class="nav-link" id="v-pills-travel-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-travel" type="button" role="tab" aria-controls="v-pills-travel"
                        aria-selected="false">
                        旅遊周邊
                    </button>
                    <button class="nav-link" id="v-pills-selected-tab" data-bs-toggle="pill"
                        data-bs-target="#v-pills-selected" type="button" role="tab" aria-controls="v-pills-selected"
                        aria-selected="false">
                        歐印嚴選
                    </button>
                </div>
            </div>

            <!-- Tab content -->
            <div class="col-md-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-products" role="tabpanel"
                        aria-labelledby="v-pills-products-tab">
                        <!-- 全部行李箱內容 -->
                        <h4>全部行李箱</h4>

                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php
                            require_once __DIR__ . '/php/APIs.php';
                            $products = getProducts();
                            foreach ($products as $product) {
                                if ($product['Type'] === 'aluminum' || $product['Type'] === 'zipper') {
                                    ?>
                                    <div class="col">
                                        <a href="productDetail.php?product_id=<?= $product['ProductID'] ?>"
                                            class="text-decoration-none">
                                            <div class="card h-100" style="cursor: pointer;">
                                                <img src="imgs/products/<?= $product['ProductName'] ?>/main.jpg"
                                                    class="card-img-top" alt="<?= $product['ProductName'] ?>">
                                                <div class="card-body text-black pb-0 mb-0">
                                                    <h5 class="card-title mb-1"><?= $product['ProductName'] ?></h5>
                                                    <p class="mb-0 mt-2">尺寸:
                                                        <?php
                                                        $productDetails = getProductById($product['ProductID']);
                                                        if (isset($productDetails['sizeOptions'])) {
                                                            echo implode(', ', array_column($productDetails['sizeOptions'], 'size'));
                                                        }
                                                        ?> 吋
                                                    </p>
                                                    <p class="fs-6 mb-0">價錢: <span
                                                            class="text-decoration-line-through">$<?= $product['MaxPrice'] ?></span>
                                                    </p>
                                                    <p class="fs-4 text-danger">$<?= $product['MinPrice'] ?></p>
                                                </div>
                                                <p class="text-center text-success border-top py-2 my-0">查看詳情</p>
                                            </div>
                                        </a>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-accessories" role="tabpanel"
                        aria-labelledby="v-pills-accessories-tab">
                        <!-- 行李箱配件內容 -->
                        <h4>行李箱配件</h4>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php
                            foreach ($products as $product) {
                                if ($product['Type'] === 'accessories') {
                                    ?>
                                    <div class="col">
                                        <a href="productDetail.php?product_id=<?= $product['ProductID'] ?>"
                                            class="text-decoration-none">
                                            <div class="card h-100" style="cursor: pointer;">
                                                <img src="imgs/products/<?= $product['ProductName'] ?>/main.jpg"
                                                    class="card-img-top" alt="<?= $product['ProductName'] ?>">
                                                <div class="card-body text-black pb-0 mb-0">
                                                    <h5 class="card-title mb-1"><?= $product['ProductName'] ?></h5>
                                                    <p class="fs-6 mb-0">價錢: <span
                                                            class="text-decoration-line-through">$<?= $product['MaxPrice'] ?></span>
                                                    </p>
                                                    <p class="fs-4 text-danger">$<?= $product['MinPrice'] ?></p>
                                                </div>
                                                <p class="text-center text-success border-top py-2 my-0">查看詳情</p>
                                            </div>
                                        </a>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-travel" role="tabpanel" aria-labelledby="v-pills-travel-tab">
                        <!-- 旅遊周邊內容 -->
                        <h4>旅遊周邊</h4>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php
                            foreach ($products as $product) {
                                if ($product['Type'] === 'travel') {
                                    ?>
                                    <div class="col">
                                        <a href="productDetail.php?product_id=<?= $product['ProductID'] ?>"
                                            class="text-decoration-none">
                                            <div class="card h-100" style="cursor: pointer;">
                                                <img src="imgs/products/<?= $product['ProductName'] ?>/main.jpg"
                                                    class="card-img-top" alt="<?= $product['ProductName'] ?>">
                                                <div class="card-body text-black pb-0 mb-0">
                                                    <h5 class="card-title mb-1"><?= $product['ProductName'] ?></h5>
                                                    <p class="fs-6 mb-0">價錢: <span
                                                            class="text-decoration-line-through">$<?= $product['MaxPrice'] ?></span>
                                                    </p>
                                                    <p class="fs-4 text-danger">$<?= $product['MinPrice'] ?></p>
                                                </div>
                                                <p class="text-center text-success border-top py-2 my-0">查看詳情</p>
                                            </div>
                                        </a>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-selected" role="tabpanel"
                        aria-labelledby="v-pills-selected-tab">
                        <!-- 歐印嚴選內容 -->
                        <h4>歐印嚴選</h4>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php
                            foreach ($products as $product) {
                                if ($product['Type'] === 'featured') {
                                    ?>
                                    <div class="col">
                                        <a href="productDetail.php?product_id=<?= $product['ProductID'] ?>"
                                            class="text-decoration-none">
                                            <div class="card h-100" style="cursor: pointer;">
                                                <img src="imgs/products/<?= $product['ProductName'] ?>/main.jpg"
                                                    class="card-img-top" alt="<?= $product['ProductName'] ?>">
                                                <div class="card-body text-black pb-0 mb-0">
                                                    <h5 class="card-title mb-1"><?= $product['ProductName'] ?></h5>
                                                    <p class="fs-6 mb-0">價錢: <span
                                                            class="text-decoration-line-through">$<?= $product['MaxPrice'] ?></span>
                                                    </p>
                                                    <p class="fs-4 text-danger">$<?= $product['MinPrice'] ?></p>
                                                </div>
                                                <p class="text-center text-success border-top py-2 my-0">查看詳情</p>
                                            </div>
                                        </a>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
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
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Grab the offcanvas element and create/get its Bootstrap instance
            const offcanvasEl = document.getElementById("offcanvasNavProducts");
            if (!offcanvasEl) return;
            const offcanvasInstance = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

            // Attach a click handler to every nav‑link INSIDE the offcanvas
            offcanvasEl.querySelectorAll(".nav-link").forEach((btn) => {
                btn.addEventListener("click", () => offcanvasInstance.hide());
            });
        });
    </script>
</body>

</html>