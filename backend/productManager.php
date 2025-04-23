<div class="tab-pane fade show active" id="products" role="tabpanel" aria-labelledby="v-pills-products-tab">
    <h3 class="mb-3">商品管理</h3>
    <div class="mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">新增商品</button>
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
                            <td>
                                <button class="btn btn-sm btn-outline-warning">編輯</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 新增商品 Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">新增商品</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" enctype="multipart/form-data" method="POST">
                    <div class="mb-3">
                        <label for="productName" class="form-label">商品名稱</label>
                        <input type="text" class="form-control" id="productName" name="productName" required>
                    </div>
                    <div class="mb-3">
                        <label for="productType" class="form-label">商品類型</label>
                        <select class="form-select" id="productType" name="productType" required>
                            <option value="" disabled selected>請選擇商品類型</option>
                            <option value="aluminum">鋁框款</option>
                            <option value="zipper">拉鍊款</option>
                            <option value="accessories">行李箱配件</option>
                            <option value="travel">旅遊周邊</option>
                            <option value="featured">精選商品</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">商品敘述</label>
                        <textarea class="form-control" id="productDescription" name="productDescription"
                            rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="mainImage" class="form-label">主要圖片</label>
                        <input type="file" class="form-control" id="mainImage" name="mainImage" accept="image/*"
                            required>
                        <div id="mainImagePreview" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="galleryImages" class="form-label">相簿圖片</label>
                        <input type="file" class="form-control" id="galleryImages" name="galleryImages[]"
                            accept="image/*" multiple>
                        <div id="galleryImagesPreview" class="mt-2"></div>
                    </div>
                    <div id="sizeContainer">
                        <div class="size-section mb-3">
                            <label class="form-label">尺寸與價格</label>
                            <div class="row g-2 mb-2">
                                <div class="col">
                                    <input type="text" class="form-control" name="sizes[]" placeholder="尺寸" required>
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="prices[]" placeholder="價格" required>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-danger remove-size">移除尺寸</button>
                                </div>
                            </div>
                            <div class="color-stock-container mt-2">
                                <div class="color-stock mb-2">
                                    <div class="row g-2">
                                        <div class="col">
                                            <input type="text" class="form-control" name="colors[0][]" placeholder="顏色"
                                                required>
                                        </div>
                                        <div class="col">
                                            <input type="number" class="form-control" name="stocks[0][]"
                                                placeholder="庫存" required>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary add-color-stock">新增顏色</button>
                                            <button type="button" class="btn btn-danger remove-color-stock">移除</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="addSize">新增尺寸</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary" form="addProductForm">儲存</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('mainImage').addEventListener('change', function (event) {
        const file = event.target.files[0];
        const preview = document.getElementById('mainImagePreview');
        preview.innerHTML = '';
        if (file) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '100px';
            img.style.maxHeight = '100px';
            preview.appendChild(img);
        }
    });

    document.getElementById('galleryImages').addEventListener('change', function (event) {
        const files = event.target.files;
        const preview = document.getElementById('galleryImagesPreview');
        preview.innerHTML = '';
        Array.from(files).forEach(file => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '100px';
            img.style.maxHeight = '100px';
            img.style.marginRight = '5px';
            preview.appendChild(img);
        });
    });

    let sizeIndex = 0;

    document.getElementById('addSize').addEventListener('click', function () {
        const sizeContainer = document.getElementById('sizeContainer');
        const newSizeSection = document.querySelector('.size-section').cloneNode(true);
        sizeIndex++;

        // 清空尺寸和價格輸入框
        newSizeSection.querySelector('input[name="sizes[]"]').value = '';
        newSizeSection.querySelector('input[name="prices[]"]').value = '';

        const colorStockContainer = newSizeSection.querySelector('.color-stock-container');
        colorStockContainer.innerHTML = ''; // 清空顏色/庫存區域

        const newColorStock = document.querySelector('.color-stock').cloneNode(true);
        newColorStock.querySelectorAll('input').forEach(input => input.value = '');
        newColorStock.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, `[${sizeIndex}]`);
        });

        colorStockContainer.appendChild(newColorStock);
        sizeContainer.appendChild(newSizeSection);
    });

    document.getElementById('sizeContainer').addEventListener('click', function (event) {
        if (event.target.classList.contains('add-color-stock')) {
            const colorStockContainer = event.target.closest('.size-section').querySelector('.color-stock-container');
            const newColorStock = document.querySelector('.color-stock').cloneNode(true);
            newColorStock.querySelectorAll('input').forEach(input => input.value = '');

            const sizeIndex = Array.from(document.querySelectorAll('.size-section')).indexOf(event.target.closest('.size-section'));
            newColorStock.querySelectorAll('input').forEach(input => {
                input.name = input.name.replace(/\[\d+\]/, `[${sizeIndex}]`);
            });

            colorStockContainer.appendChild(newColorStock);
        } else if (event.target.classList.contains('remove-color-stock')) {
            // 檢查當前尺寸區塊中的顏色項目數量
            const currentSection = event.target.closest('.size-section');
            const colorStocks = currentSection.querySelectorAll('.color-stock');

            if (colorStocks.length > 1) {
                event.target.closest('.color-stock').remove();
            } else {
                alert('每個尺寸至少需要一個顏色選項');
            }
        } else if (event.target.classList.contains('remove-size')) {
            const sizeSections = document.querySelectorAll('.size-section');
            if (sizeSections.length > 1) {
                event.target.closest('.size-section').remove();
            } else {
                alert('至少需要一個尺寸');
            }
        }
    });

    document.getElementById('addProductForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        const form = this;
        const formData = new FormData(form);

        // 調試：列印所有欄位
        console.group('新增商品送出資料');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }
        console.groupEnd();

        try {
            const response = await fetch('/public_html/php/addProductHandler.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`網路錯誤，狀態碼：${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                alert('商品新增成功');
                bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
                form.reset();
                document.getElementById('mainImagePreview').innerHTML = '';
                document.getElementById('galleryImagesPreview').innerHTML = '';
                location.reload();
            } else {
                alert('商品新增失敗：' + (data.message || '未知錯誤'));
                console.error('伺服器回傳錯誤：', data);
            }
        } catch (error) {
            console.error('發生例外錯誤：', error);
            alert('發生錯誤：' + error.message);
        }
    });
</script>