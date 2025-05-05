<?php
require_once 'fixedFile/header.php';
require_once __DIR__ . '/api/product.php';

$luggages = getProductsByType('luggage');
$accessories = getProductsByType('accessories');
$travels = getProductsByType('travel');
$featureds = getProductsByType('featured');
?>

<div class="col-12 col-md-10 mx-auto">
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

<div class="container-fulid mt-3">
    <div class="row col-lg-9 col-12 mx-auto m-0 p-0">
        <ul class="nav nav-pills mb-4 col-9 mx-auto justify-content-center gap-4 fs-6" id="product-tabs">
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
        <div class="tab-content">
            <div class="tab-pane show active m-0 p-0" id="tab-luggage">
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
                <div class="row" id="luggage-container">
                    <?php
                    foreach ($luggages as $index => $p) {
                        if (isset($p['isActive']) ? $p['isActive'] == 1 : true)
                            echo renderProductCard($p);
                    }
                    ?>
                </div>
            </div>
            <div class="tab-pane" id="tab-accessories">
                <h4>行李箱配件</h4>
                <div class="row" id="accessories-container"><?php foreach ($accessories as $p)
                    if (isset($p['isActive']) ? $p['isActive'] == 1 : true)
                        echo renderProductCard($p); ?></div>
            </div>
            <div class="tab-pane" id="tab-travel">
                <h4>旅遊周邊</h4>
                <div class="row" id="travel-container"><?php foreach ($travels as $p)
                    if (isset($p['isActive']) ? $p['isActive'] == 1 : true)
                        echo renderProductCard($p); ?></div>
            </div>
            <div class="tab-pane" id="tab-featured">
                <h4>歐印嚴選</h4>
                <div class="row" id="featured-container"><?php foreach ($featureds as $p)
                    if (isset($p['isActive']) ? $p['isActive'] == 1 : true)
                        echo renderProductCard($p); ?></div>
            </div>
        </div>
    </div>
</div>

<script>
    // 你的過濾邏輯
    const sizeSelect = document.getElementById('size-filter');
    const materialSelect = document.getElementById('material-filter');

    function applyFilters() {
        const sizeVal = sizeSelect.value;
        const materialVal = materialSelect.value;

        // 只對行李箱標籤頁應用篩選
        if (document.querySelector('#tab-luggage.active')) {
            const productItems = document.querySelectorAll('#luggage-container .product-item');
            let visibleCount = 0;

            productItems.forEach(item => {
                const itemSize = item.dataset.size;
                const itemMaterial = item.dataset.material;
                const sizes = item.dataset.sizes ? item.dataset.sizes.split(',').filter(s => s !== '') : [];

                const sizeMatch = sizeVal === 'all' ||
                    sizes.some(size => {
                        const numSize = parseInt(size);
                        if (isNaN(numSize) || numSize === -1) return false;

                        if (sizeVal === 'large') return numSize > 28;
                        if (sizeVal === 'medium') return numSize >= 25 && numSize <= 28;
                        if (sizeVal === 'small') return numSize >= 21 && numSize < 25;
                        if (sizeVal === 'cabin') return numSize < 21;
                        return false;
                    });

                const shouldShow = sizeMatch &&
                    (materialVal === 'all' || itemMaterial === materialVal);
                const container = item.closest('.col-md-4') || item.closest('.col-lg-3') || item;
                if (container) {
                    container.classList.toggle('visually-hidden', !shouldShow);
                    container.hidden = false;
                    if (shouldShow) visibleCount++;
                }
            });
        }

        // 顯示無商品提示
        function showEmptyAlert(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;

            let emptyAlert = container.querySelector('.no-products-alert');
            const items = container.querySelectorAll('.product-item');
            const visibleItems = Array.from(items).filter(item =>
                !item.classList.contains('visually-hidden') && !item.hidden
            );

            if (visibleItems.length === 0) {
                if (!emptyAlert) {
                    emptyAlert = document.createElement('div');
                    emptyAlert.className = 'no-products-alert alert alert-info text-center mt-4';
                    emptyAlert.innerHTML = '⚠️ 目前沒有符合篩選的商品';
                    container.appendChild(emptyAlert);
                }
            } else if (emptyAlert) {
                emptyAlert.remove();
            }
        }

        // 檢查每個容器
        const activeTabId = document.querySelector('.tab-pane.active').id;
        if (activeTabId === 'tab-luggage') {
            showEmptyAlert('luggage-container');
        } else if (activeTabId === 'tab-accessories') {
            showEmptyAlert('accessories-container');
        } else if (activeTabId === 'tab-travel') {
            showEmptyAlert('travel-container');
        } else if (activeTabId === 'tab-featured') {
            showEmptyAlert('featured-container');
        }
    }

    // 監聽篩選器變化
    sizeSelect.addEventListener('change', applyFilters);
    materialSelect.addEventListener('change', applyFilters);

    // 監聽標籤頁切換
    document.querySelectorAll('#product-tabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', applyFilters);
    });

    // 初始應用篩選
    applyFilters();
</script>
<?php
require_once 'fixedFile/footer.php';
?>