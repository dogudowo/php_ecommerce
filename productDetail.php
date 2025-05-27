<?php
// productDetail.php
// 接收 product_id 和 edit(optionId)
$product_id = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
$edit_option_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
?>
<?php include "header.php"; ?>

<div class="container text-center border-bottom border-black ">
    <img src="imgs/title.png" style="max-height: 100px;" alt="歐印精品" class="img-fluid">
    <h1 class="h1" id="product-name"></h1>
</div>

<div class="container" id="product-container">
    <!-- 商品資訊將由 AJAX 載入 -->
</div>

<!-- Alert 顯示位置 -->
<div id="alertPlaceholder" class="position-fixed top-50 start-50 translate-middle-x mt-3" style="z-index: 1100;"></div>

<!-- 登入提示 Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">登入提示</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                請先登入會員才能加入購物車
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <a href="login.php" class="btn btn-primary">前往登入</a>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<!-- 先把 PHP 變數傳給 JS -->
<script>
    const productId = <?= $product_id ?>;
    let productData = null;

    async function loadProductDetails() {
        try {
            const resp = await fetch(`api/product.php?action=getProductById&product_id=${productId}`);
            const json = await resp.json();
            if (!json.success || !json.data || json.data.isActive == 0) {
                window.location.href = '404.php';
                return;
            }
            productData = json.data;
            renderProduct(productData);
        } catch (err) {
            console.error(err);
            window.location.href = '404.php';
        }
    }

    function renderProduct(p) {
        document.getElementById('product-name').textContent = p.ProductName || '無商品名稱';
        const mainImg = p.mainImage || '';
        const intro = p.Introdution || '';
        const firstSize = p.sizeOptions[0] || {};
        const initPrice = firstSize.Price || 0;
        const initOrig = Math.round(initPrice * 1.2);

        // build HTML
        const html = `
        <div class="row">
            <div class="col-lg-6 col-none">
                <img src="${mainImg}" class="img-fluid" alt="main image" />
            </div>
            <div class="col-lg-6 col-12">
                <div class="container my-4">
                    <p class="text-start">${intro}</p>
                </div>
                <div class="container">
                    <!-- 尺寸 -->
                    <div class="mb-4">
                        <p class="fw-bold mb-1">尺寸</p>
                        <div id="sizes" class="d-flex flex-wrap"></div>
                    </div>
                    <!-- 顏色 -->
                    <div class="mb-4">
                        <p class="fw-bold mb-1">顏色</p>
                        <div id="colors" class="d-flex flex-wrap"></div>
                    </div>
                    <!-- 數量 -->
                    <div class="mb-4">
                        <p class="fw-bold mb-1">數量</p>
                        <div class="input-group" style="max-width: 150px">
                            <button class="btn btn-outline-success" id="decrease-qty">
                                -
                            </button>
                            <input
                                type="number"
                                class="form-control text-center border-success"
                                id="quantity"
                                value="1"
                                min="1"
                            />
                            <button class="btn btn-outline-success" id="increase-qty">
                                +
                            </button>
                        </div>
                    </div>
                    <!-- 價格 -->
                    <div class="mb-4">
                        <p class="fw-bold mb-1">價格</p>
                        <div>
                            <span
                                class="fs-6 text-decoration-line-through"
                                id="original-price"
                            >
                                NT$ ${initOrig.toLocaleString()}
                            </span>
                            <div class="h3 text-danger fw-bold" id="product-price">
                                NT$ ${initPrice.toLocaleString()}
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-3" id="add-to-cart">
                        加入購物車
                    </button>
                </div>
            </div>
            <!-- 圖庫 -->
            <div class="row mt-5 col-12 col-lg-9 mx-auto m-0 p-0" id="gallery-container">
                ${p.galleryImages.map(img => `
                <div class="text-center">
                    <img src="${img.url}" class="img-fluid" alt="gallery image" />
                </div>
                `).join('')}
            </div>
        </div>
        `;
        document.getElementById('product-container').innerHTML = html;

        bindQtyButtons();
        renderSizes();
        // 初始選第一個尺寸會同時 render 顏色
        selectSize(0);
        bindAddToCart();
    }

    function bindQtyButtons() {
        document.getElementById('decrease-qty').addEventListener('click', () => {
            const q = document.getElementById('quantity');
            if (q.value > 1) q.value = parseInt(q.value) - 1;
        });
        document.getElementById('increase-qty').addEventListener('click', () => {
            const q = document.getElementById('quantity');
            q.value = parseInt(q.value) + 1;
        });
    }

    function renderSizes() {
        const container = document.getElementById('sizes');
        container.innerHTML = '';
        productData.sizeOptions.forEach((opt, idx) => {
            const btn = document.createElement('button');
            btn.className = 'btn btn-outline-success me-1 mb-1 size-option';
            btn.textContent = opt.SizeDescription;
            btn.dataset.idx = idx;
            btn.addEventListener('click', () => selectSize(idx));
            container.appendChild(btn);
        });
    }

    function selectSize(idx) {
        // 樣式切換
        document.querySelectorAll('.size-option').forEach(btn => {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
        });
        const sel = document.querySelector(`.size-option[data-idx="${idx}"]`);
        sel.classList.remove('btn-outline-success');
        sel.classList.add('btn-success');

        // 更新價格
        const opt = productData.sizeOptions[idx];
        const price = opt.Price || 0;
        const orig = Math.round(price * 1.2);
        document.getElementById('product-price').textContent = `NT$ ${price.toLocaleString()}`;
        document.getElementById('original-price').textContent = `NT$ ${orig.toLocaleString()}`;

        // 產生顏色按鈕
        renderColors(opt);
    }

    function renderColors(sizeOpt) {
        const container = document.getElementById('colors');
        container.innerHTML = '';
        // sizeOpt.Colors 是逗號分隔字串
        const names = sizeOpt.Colors.split(',');
        // sizeOpt.colors 陣列與 names 一一對應
        sizeOpt.colors.forEach((c, i) => {
            const btn = document.createElement('button');
            btn.className = 'btn btn-outline-success me-1 mb-1 color-option';
            btn.textContent = names[i] || `顏色${i + 1}`;
            btn.dataset.optionId = c.OptionID;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.color-option').forEach(b => {
                    b.classList.remove('btn-success');
                    b.classList.add('btn-outline-success');
                });
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-success');
            });
            container.appendChild(btn);
        });
        // 自動選第一個
        const first = container.querySelector('button');
        if (first) first.click();
    }

    function bindAddToCart() {
        document.getElementById('add-to-cart').addEventListener('click', async () => {
            const memberId = <?= isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0 ?>;
            if (!memberId) {
                const modal = new bootstrap.Modal(document.getElementById('loginModal'));
                modal.show();
                return;
            }
            const qty = parseInt(document.getElementById('quantity').value);
            const colorBtn = document.querySelector('.color-option.btn-success');
            const optionId = colorBtn?.dataset.optionId;
            if (!optionId) return alert('請選擇顏色');
            try {
                // 如果有edit參數，先刪除原有商品
                <?php if ($edit_option_id > 0): ?>
                    await fetch('api/cart.php?action=removeFromCart', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            memberId: memberId,
                            optionId: <?= $edit_option_id ?>
                        })
                    });
                <?php endif; ?>

                // 加入新規格商品
                const response = await fetch('api/cart.php?action=addToCart', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ memberId, optionId, quantity: qty })
                });
                const res = await response.json();

                const alertPlaceholder = document.getElementById('alertPlaceholder');
                if (!alertPlaceholder) {
                    console.error('Alert placeholder not found');
                    return;
                }

                const wrapper = document.createElement('div');
                wrapper.innerHTML = [
                    `<div class="alert alert-${res.success ? 'success' : 'danger'} alert-dismissible fade show" role="alert">`,
                    `   <div>${res.success ? '已加入購物車' : '加入失敗：' + (res.message || '')}</div>`,
                    '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                    '</div>'
                ].join('');

                alertPlaceholder.append(wrapper);

                // 1.5秒後自動消失
                setTimeout(() => {
                    const alert = wrapper.querySelector('.alert');
                    if (alert) {
                        bootstrap.Alert.getOrCreateInstance(alert).close();
                    }
                }, 1500);
            } catch (e) {
                console.error(e);
                const modal = new bootstrap.Modal(document.getElementById('messageModal'));
                document.getElementById('modalBody').textContent = '發生錯誤';
                modal.show();
            }
        });
    }

    // 如果有edit參數，預先選中對應的規格
    function preselectOption() {
        if (!productData || !productData.sizeOptions) return;

        // 找出對應的optionId在sizeOptions中的位置
        for (let i = 0; i < productData.sizeOptions.length; i++) {
            const sizeOpt = productData.sizeOptions[i];
            if (sizeOpt.colors) {
                for (let j = 0; j < sizeOpt.colors.length; j++) {
                    if (sizeOpt.colors[j].OptionID == <?= $edit_option_id ?>) {
                        // 選中對應的尺寸和顏色
                        selectSize(i);
                        const colorBtn = document.querySelector(`.color-option[data-option-id="<?= $edit_option_id ?>"]`);
                        if (colorBtn) colorBtn.click();
                        return;
                    }
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadProductDetails().then(preselectOption);
    });
</script>