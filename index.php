<?php
session_start();
require_once __DIR__ . '/php/APIs.php';

// 1. 資料獲取與分類
$allProducts = getProducts();
$luggages = $accessories = $travels = $featureds = [];

foreach ($allProducts as $p) {
    $details = getProductById($p['ProductID']);
    $p['details'] = $details;
    switch ($p['Type']) {
        case 'aluminum':
        case 'zipper':
            $luggages[] = $p;
            break;
        case 'accessories':
            $accessories[] = $p;
            break;
        case 'travel':
            $travels[] = $p;
            break;
        case 'featured':
            $featureds[] = $p;
            break;
    }
}

function renderProductCard(array $product)
{
    $sizes = array_column($product['details']['sizeOptions'] ?? [], 'size');
    $sizeClass = '';
    if (!empty($sizes)) {
        $max = max($sizes);
        if ($max > 28)
            $sizeClass = 'large';
        elseif ($max >= 25)
            $sizeClass = 'medium';
        elseif ($max >= 21)
            $sizeClass = 'small';
        else
            $sizeClass = 'cabin';
    }
    $img = "imgs/products/{$product['ProductID']}/main.jpg";
    ?>
    <div class="col-lg-4 col-md-6 mb-3 d-flex justify-content-center product-item" data-size="<?= $sizeClass ?>"
        data-material="<?= htmlspecialchars($product['Type'], ENT_QUOTES) ?>">
        <div class="card" style="width:100%; max-width:320px; border-radius:20px; transition:transform .2s;"
            onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
            <!-- 點擊區域 -->
            <a href="productDetail.php?product_id=<?= $product['ProductID'] ?>" class="text-decoration-none text-black">
                <img src="<?= $img ?>" class="card-img-top" style="border-radius:20px 20px 0 0; object-fit:cover;"
                    alt="<?= htmlspecialchars($product['ProductName'], ENT_QUOTES) ?>">
                <div class="card-body pb-0 mb-2">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($product['ProductName'], ENT_QUOTES) ?></h5>
                    <?php if (!empty($sizes) || !($sizes = -1)): ?>
                        <p class="card-text mb-0">尺寸：<?= implode(', ', $sizes) ?> 吋</p>
                    <?php endif; ?>
                    <p class="mb-0">
                        價格：
                        <span class="text-decoration-line-through text-muted">
                            $<?= number_format(round($product['MaxPrice'] * 1.2), 0) ?>
                        </span>
                    </p>
                    <p class="text-danger fs-4 mt-0">
                        <strong>$<?= number_format($product['MinPrice'], 0) ?></strong>
                    </p>
                </div>
            </a>
            <!-- 卡片底部按鈕 -->
            <div class="card-footer bg-transparent border-0 text-center pb-3">
                <a href="productDetail.php?product_id=<?= $product['ProductID'] ?>" class="btn btn-success"
                    style="border-radius:20px; width:80%;">
                    查看詳情
                </a>
            </div>
        </div>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>歐印精品-首頁</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/navPillsStyles.css">
    <style>
        .card {
            transition: box-shadow .3s;
        }

        .card:hover {
            box-shadow: 0 0 7px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <!-- Carousel -->
    <div class="containe-fluid">
        <?php
        $dir = 'imgs/carousel/';
        $imgs = glob($dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        ?>
        <div id="homeCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach ($imgs as $i => $img): ?>
                    <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="<?= $i ?>"
                        class="<?= !$i ? 'active' : '' ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($imgs as $i => $img): ?>
                    <div class="carousel-item <?= !$i ? 'active' : '' ?>">
                        <img src="<?= $img ?>" class="d-block w-100" alt="slide">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" data-bs-target="#homeCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" data-bs-target="#homeCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>

    <!-- Products & Filters -->
    <div class="container-fluid mt-3 px-3 mx-auto">
        <div class="row col-lg-8 col-12 mx-auto">
            <ul class="nav nav-pills mb-4 justify-content-center gap-4 fs-6" id="product-tabs">
                <li class="nav-item">
                    <a class="nav-link active" style="border-radius:20px;" href="#" data-bs-toggle="pill"
                        data-bs-target="#tab-luggage">全部行李箱</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" style="border-radius:20px;" href="#" data-bs-toggle="pill"
                        data-bs-target="#tab-accessories">行李箱配件</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" style="border-radius:20px;" href="#" data-bs-toggle="pill"
                        data-bs-target="#tab-travel">旅行周邊</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " style="border-radius:20px;" href="#" data-bs-toggle="pill"
                        data-bs-target="#tab-featured">歐印精選</a>
                </li>
            </ul>
            <div class="col-md-9">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-luggage">
                        <h4>全部行李箱</h4>
                        <!-- 篩選 Controls -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <h5>尺寸篩選</h5>
                                <select id="size-filter" class="form-select">
                                    <option value="all" selected>全部尺寸</option>
                                    <option value="large">28吋以上</option>
                                    <option value="medium">25~28吋</option>
                                    <option value="small">21~24吋</option>
                                    <option value="cabin">20吋以下</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h5>材質篩選</h5>
                                <select id="material-filter" class="form-select">
                                    <option value="all" selected>全部材質</option>
                                    <option value="aluminum">鋁框款</option>
                                    <option value="zipper">拉鍊款</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-4" id="luggage-container">
                            <?php foreach ($luggages as $p)
                                renderProductCard($p); ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-accessories">
                        <h4>行李箱配件</h4>
                        <div class="row g-4"><?php foreach ($accessories as $p)
                            renderProductCard($p); ?></div>
                    </div>
                    <div class="tab-pane fade" id="tab-travel">
                        <h4>旅遊周邊</h4>
                        <div class="row g-4"><?php foreach ($travels as $p)
                            renderProductCard($p); ?></div>
                    </div>
                    <div class="tab-pane fade" id="tab-featured">
                        <h4>歐印嚴選</h4>
                        <div class="row g-4"><?php foreach ($featureds as $p)
                            renderProductCard($p); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 你的過濾邏輯
        const sizeSelect = document.getElementById('size-filter');
        const materialSelect = document.getElementById('material-filter');
        const productItems = document.querySelectorAll('#luggage-container .product-item');

        function applyFilters() {
            const sizeVal = sizeSelect.value;
            const materialVal = materialSelect.value;
            let visibleCount = 0;
            productItems.forEach(item => {
                const itemSize = item.dataset.size;
                const itemMaterial = item.dataset.material;
                const shouldShow = (sizeVal === 'all' || itemSize === sizeVal)
                    && (materialVal === 'all' || itemMaterial === materialVal);
                const container = item.closest('.col-md-3') || item.closest('.col-lg-4') || item;
                if (container) {
                    container.classList.toggle('visually-hidden', !shouldShow);
                    container.hidden = false;
                    if (shouldShow) visibleCount++;
                }
            });

            // 无商品提示
            const container = document.getElementById('luggage-container');
            let emptyAlert = container.querySelector('.no-products-alert');
            if (visibleCount === 0) {
                if (!emptyAlert) {
                    emptyAlert = document.createElement('div');
                    emptyAlert.className = 'no-products-alert alert alert-info text-center mt-4';
                    emptyAlert.innerHTML = '⚠️ 目前没有符合筛选条件的商品，请尝试其他筛选组合';
                    container.appendChild(emptyAlert);
                }
            } else if (emptyAlert) {
                emptyAlert.remove();
            }
        }

        sizeSelect.addEventListener('change', applyFilters);
        materialSelect.addEventListener('change', applyFilters);
        applyFilters();
    </script>
</body>

</html>