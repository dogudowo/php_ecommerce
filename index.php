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

                        <!-- 篩選控制項 -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <h5>尺寸篩選</h5>
                                <select class="form-select" id="size-filter">
                                    <option value="all" selected>全部尺寸</option>
                                    <option value="large">28吋以上</option>
                                    <option value="medium">25~28吋</option>
                                    <option value="small">21~24吋</option>
                                    <option value="cabin">20吋以下</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h5>材質篩選</h5>
                                <select class="form-select" id="material-filter">
                                    <option value="all" selected>全部材質</option>
                                    <option value="aluminum">鋁框款</option>
                                    <option value="zipper">拉鍊款</option>
                                </select>
                            </div>
                        </div>

                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="luggage-container">
                            <?php
                            require_once __DIR__ . '/php/APIs.php';
                            $products = getProducts();
                            foreach ($products as $product) {
                                if ($product['Type'] === 'aluminum' || $product['Type'] === 'zipper') {
                                    $productDetails = getProductById($product['ProductID']);
                                    $sizes = [];
                                    if (isset($productDetails['sizeOptions'])) {
                                        foreach ($productDetails['sizeOptions'] as $sizeOption) {
                                            $sizes[] = $sizeOption['size'];
                                        }
                                    }

                                    // 將尺寸和材質信息添加為data屬性
                                    $sizeClass = '';
                                    if (!empty($sizes)) {
                                        $maxSize = max($sizes);
                                        if ($maxSize > 28) {
                                            $sizeClass = 'large';
                                        } elseif ($maxSize >= 25) {
                                            $sizeClass = 'medium';
                                        } elseif ($maxSize >= 21) {
                                            $sizeClass = 'small';
                                        } else {
                                            $sizeClass = 'cabin';
                                        }
                                    }
                                    ?>
                                    <div class="col product-item" data-size="<?= $sizeClass ?>"
                                        data-material="<?= $product['Type'] ?>">
                                        <a href="productDetail.php?product_id=<?= $product['ProductID'] ?>"
                                            class="text-decoration-none">
                                            <div class="card h-100" style="cursor: pointer;">
                                                <img src="imgs/products/<?= $product['ProductName'] ?>/main.jpg"
                                                    class="card-img-top" alt="<?= $product['ProductName'] ?>">
                                                <div class="card-body text-black pb-0 mb-0">
                                                    <h5 class="card-title mb-1"><?= $product['ProductName'] ?></h5>
                                                    <p class="mb-0 mt-2">尺寸:
                                                        <?php
                                                        if (isset($productDetails['sizeOptions'])) {
                                                            echo implode(', ', array_column($productDetails['sizeOptions'], 'size'));
                                                        }
                                                        ?> 吋
                                                    </p>
                                                    <p class="fs-6 mb-0">價錢: <span
                                                            class="text-decoration-line-through">$<?= $product['MaxPrice'] ?></span>
                                                    </p>
                                                    <p class="fs-4 text-danger"> <strong>$<?= $product['MinPrice'] ?></strong>
                                                    </p>
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

            // 篩選功能
            let currentSizeFilter = 'all';
            let currentMaterialFilter = 'all';

            // 尺寸篩選下拉選單變更事件
            document.getElementById('size-filter').addEventListener('change', function () {
                currentSizeFilter = this.value;
                applyFilters();
            });

            // 材質篩選下拉選單變更事件
            document.getElementById('material-filter').addEventListener('change', function () {
                currentMaterialFilter = this.value;
                applyFilters();
            });

            // 應用篩選函數
            function applyFilters() {
                const productItems = document.querySelectorAll('.product-item');
                let visibleCount = 0;

                productItems.forEach(item => {
                    const itemSize = item.getAttribute('data-size');
                    const itemMaterial = item.getAttribute('data-material');

                    // 檢查是否符合當前篩選條件
                    const sizeMatch = currentSizeFilter === 'all' || itemSize === currentSizeFilter;
                    const materialMatch = currentMaterialFilter === 'all' || itemMaterial === currentMaterialFilter;

                    // 如果同時符合尺寸和材質篩選條件，則顯示該項目，否則隱藏
                    if (sizeMatch && materialMatch) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // 檢查是否有顯示的產品
                const noResultsMessage = document.getElementById('no-results-message');

                // 只有當沒有可見產品時才顯示提示信息
                if (visibleCount === 0) {
                    if (!noResultsMessage) {
                        const container = document.getElementById('luggage-container');
                        const message = document.createElement('div');
                        message.id = 'no-results-message';
                        message.className = 'col-12 text-center my-5';
                        message.innerHTML = '<h5>沒有符合條件的商品</h5>';
                        container.appendChild(message);
                    }
                } else {
                    // 如果有顯示的產品且有提示信息，則移除提示
                    if (noResultsMessage) {
                        noResultsMessage.remove();
                    }
                }
            }
        });
    </script>
</body>

</html>