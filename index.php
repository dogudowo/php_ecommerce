<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="icon" href="./imgs/webimg.ico" />
    <title>歐印精品-首頁</title>
</head>

<body class="align-content-center" style="width:98vw;">
    <?php include 'nav.php'; ?>

    <div class="container mt-3">
        <!-- Offcanvas trigger button for small screens -->
        <div class="d-md-none mb-3">
            <button class="btn btn-success" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNav"
                aria-controls="offcanvasNav">
                商品選項
            </button>
        </div>

        <!-- Offcanvas component for navigation -->
        <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="offcanvasNav" aria-labelledby="offcanvasNavLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavLabel">商品選項</h5>
            </div>
            <div class="offcanvas-body">
                <div class="nav flex-column nav-pills gap-2" id="v-pills-tabOffcanvas" role="tablist"
                    aria-orientation="vertical">
                    <a class="btn btn-success active" id="v-pills-products-tab" data-bs-toggle="pill"
                        href="#v-pills-products" role="tab" aria-controls="v-pills-products" aria-selected="true">
                        全部行李箱
                    </a>
                    <a class="btn btn-success" id="v-pills-accessories-tab" data-bs-toggle="pill"
                        href="#v-pills-accessories" role="tab" aria-controls="v-pills-accessories"
                        aria-selected="false">
                        行李箱配件
                    </a>
                    <a class="btn btn-success" id="v-pills-travel-tab" data-bs-toggle="pill" href="#v-pills-travel"
                        role="tab" aria-controls="v-pills-travel" aria-selected="false">
                        旅遊周邊
                    </a>
                    <a class="btn btn-success" id="v-pills-selected-tab" data-bs-toggle="pill" href="#v-pills-selected"
                        role="tab" aria-controls="v-pills-selected" aria-selected="false">
                        歐印嚴選
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-3 d-none d-md-block">
                <div class="nav flex-column nav-pills me-3 gap-1" id="v-pills-tab" role="tablist"
                    aria-orientation="vertical">
                    <a class="btn btn-success active" id="v-pills-products-tab" data-bs-toggle="pill"
                        href="#v-pills-products" role="tab" aria-controls="v-pills-products" aria-selected="true">
                        全部行李箱
                    </a>
                    <a class="btn btn-success" id="v-pills-accessories-tab" data-bs-toggle="pill"
                        href="#v-pills-accessories" role="tab" aria-controls="v-pills-accessories"
                        aria-selected="false">
                        行李箱配件
                    </a>
                    <a class="btn btn-success" id="v-pills-travel-tab" data-bs-toggle="pill" href="#v-pills-travel"
                        role="tab" aria-controls="v-pills-travel" aria-selected="false">
                        旅遊周邊
                    </a>
                    <a class="btn btn-success" id="v-pills-selected-tab" data-bs-toggle="pill" href="#v-pills-selected"
                        role="tab" aria-controls="v-pills-selected" aria-selected="false">
                        歐印嚴選
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-products" role="tabpanel"
                        aria-labelledby="v-pills-products-tab">
                        <!-- 全部行李箱內容 -->
                        <div class="d-flex gap-3 mb-3">
                            <div>
                                <label for="filter-size" class="form-label me-2">篩選尺寸:</label>
                                <select id="filter-size" class="form-select d-inline-block w-auto">
                                    <option value="">全部</option>
                                    <option value="below-20">20吋以下登機箱</option>
                                    <option value="21-24">21~24吋行李箱</option>
                                    <option value="25-28">25~28吋行李箱</option>
                                    <option value="above-28">28吋以上行李箱</option>
                                </select>
                            </div>
                            <div>
                                <label for="filter-style" class="form-label me-2">篩選類型:</label>
                                <select id="filter-style" class="form-select d-inline-block w-auto">
                                    <option value="">全部</option>
                                    <option value="aluminum">鋁框款</option>
                                    <option value="zipper">拉鍊款</option>
                                </select>
                            </div>
                        </div>
                        <!-- 顯示篩選後的結果 -->
                        <div id="filtered-products">
                            <?php
                            $current_category = 'luggage';
                            include 'product_filter_content.php';
                            ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-accessories" role="tabpanel"
                        aria-labelledby="v-pills-accessories-tab">
                        <!-- 行李箱配件內容 -->
                        <?php
                        $current_category = 'Accessories';
                        include 'product_filter_content.php';
                        ?>
                    </div>
                    <div class="tab-pane fade" id="v-pills-travel" role="tabpanel" aria-labelledby="v-pills-travel-tab">
                        <!-- 旅遊周邊內容 -->
                        <?php
                        $current_category = 'travel';
                        include 'product_filter_content.php';
                        ?>
                    </div>
                    <div class="tab-pane fade" id="v-pills-selected" role="tabpanel"
                        aria-labelledby="v-pills-selected-tab">
                        <!-- 歐印嚴選內容 -->
                        <?php
                        $current_category = 'selected';
                        include 'product_filter_content.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterSize = document.getElementById('filter-size');
            const filterStyle = document.getElementById('filter-style');

            filterSize.addEventListener('change', filterProducts);
            filterStyle.addEventListener('change', filterProducts);

            function filterProducts() {
                // 找出目前活躍的 tab-pane（顯示中的 tab）
                const activeTab = document.querySelector('.tab-pane.active');
                // 僅選取該 tab 內的商品
                const productForms = activeTab.querySelectorAll('.col-6.col-md-3');

                const sizeFilter = filterSize.value;
                const styleFilter = filterStyle.value;

                productForms.forEach(form => {
                    const card = form.querySelector('.card');
                    const productType = card.getAttribute('data-type');
                    const variantName = card.querySelector('p').textContent.toLowerCase();

                    let showBySize = true;
                    if (sizeFilter) {
                        const size = extractSize(variantName);
                        if (sizeFilter === 'below-20' && size > 20) showBySize = false;
                        else if (sizeFilter === '21-24' && (size < 21 || size > 24)) showBySize = false;
                        else if (sizeFilter === '25-28' && (size < 25 || size > 28)) showBySize = false;
                        else if (sizeFilter === 'above-28' && size <= 28) showBySize = false;
                    }

                    let showByStyle = true;
                    if (styleFilter && styleFilter !== '') {
                        if (styleFilter !== productType) showByStyle = false;
                    }

                    if (showBySize && showByStyle) {
                        form.style.display = '';
                    } else {
                        form.style.display = 'none';
                    }
                });
            }

            function extractSize(variantText) {
                // Extract size from variant name like "6021-64/26吋升級快閃"
                const match = variantText.match(/(\d+)吋/);
                return match ? parseInt(match[1]) : 0;
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
</body>

</html>