<?php
require_once __DIR__ . '/../api/error_handler.php';
// 防止直接訪問此文件
defined('INCLUDED_FROM_ADMIN') || exit(header('Location: ../login.php'));

require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/product.php'; // 確保 product 相關功能可用

// 處理 GET 參數，用於初始化篩選條件
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 從資料庫獲取商品類型 (目前用於未來擴充性，實際篩選選項在JS中硬編碼)
$pdo = db_connect();
$stmt = $pdo->query("SELECT DISTINCT Type FROM Product");
$productTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container-fluid p-0">
    <!-- 標題 & 按鈕 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-box-seam me-2"></i>商品管理</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            新增商品
        </button>
    </div>

    <!-- 篩選區 -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">商品類型</label>
                    <select id="filterType" class="form-select">
                        <option value="">全部類型</option>
                        <!-- 使用PHP預設選中狀態 -->
                        <option value="aluminum" <?= $type === 'aluminum' ? 'selected' : '' ?>>鋁框款</option>
                        <option value="zipper" <?= $type === 'zipper' ? 'selected' : '' ?>>拉鍊款</option>
                        <option value="accessories" <?= $type === 'accessories' ? 'selected' : '' ?>>行李箱配件</option>
                        <option value="travel" <?= $type === 'travel' ? 'selected' : '' ?>>旅遊周邊</option>
                        <option value="featured" <?= $type === 'featured' ? 'selected' : '' ?>>精選商品</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">商品狀態</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">全部狀態</option>
                        <!-- 使用PHP預設選中狀態 -->
                        <option value="1" <?= $status === '1' ? 'selected' : '' ?>>啟用</option>
                        <option value="0" <?= $status === '0' ? 'selected' : '' ?>>停用</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">搜尋商品</label>
                    <input id="searchProduct" type="text" class="form-control" placeholder="輸入商品名稱或ID"
                        value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- 商品列表 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="productTable" class="table table-hover align-middle">
                    <!-- 固定欄寬 -->
                    <colgroup>
                        <col style="width: 10%;"> <!-- ID -->
                        <col style="width: 44%;"> <!-- 商品名稱 -->
                        <col style="width: 16%;"> <!-- 類型 -->
                        <col style="width: 10%;"> <!-- 總庫存 -->
                        <col style="width: 10%;"> <!-- 狀態 -->
                        <col style="width: 10%;"> <!-- 操作 -->
                    </colgroup>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>商品名稱</th>
                            <th>類型</th>
                            <th>總庫存</th>
                            <th>狀態</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <!-- 商品資料將由 product.js 中的 fetch 請求獲取並填充 -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 編輯主要圖片模態框 -->
    <div class="modal fade" id="editMainImageModal" tabindex="-1" aria-labelledby="editMainImageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editMainImageModalLabel">編輯主要圖片</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">選擇新圖片</label>
                        <input type="file" class="form-control" id="newMainImageInput"
                            accept="image/jpeg,image/jpg,image/png">
                    </div>
                    <div class="border rounded p-2 text-center mb-3" style="height: 200px;">
                        <img id="editMainImagePreview" src="" class="img-fluid h-100">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="saveMainImageBtn">儲存變更</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 管理詳細圖片模態框 -->
    <div class="modal fade" id="manageGalleryModal" tabindex="-1" aria-labelledby="manageGalleryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="manageGalleryModalLabel">管理詳細圖片</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">新增圖片</label>
                        <input type="file" class="form-control" id="newGalleryImageInput"
                            accept="image/jpeg,image/jpg,image/png" multiple>
                    </div>
                    <div id="gallerySortable" class="row g-2">
                        <!-- 圖片將在這裡動態生成並可拖曳排序 -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="saveGalleryBtn">儲存變更</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 新增商品模態框 -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addProductModalLabel">新增商品</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="productName" class="form-label">商品名稱</label>
                                    <input type="text" class="form-control" id="productName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="productType" class="form-label">商品類型</label>
                                    <select class="form-select" id="productType" required>
                                        <option value="" selected disabled>選擇商品類型</option>
                                        <option value="aluminum">鋁框款</option>
                                        <option value="zipper">拉鍊款</option>
                                        <option value="accessories">行李箱配件</option>
                                        <option value="travel">旅遊周邊</option>
                                        <option value="featured">精選商品</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="productIntro" class="form-label">商品介紹</label>
                                    <textarea class="form-control" id="productIntro" rows="5" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch d-flex align-items-center"
                                        style="margin-left: 1rem;">
                                        <input class="form-check-input" type="checkbox" id="productActive"
                                            style="transform: scale(1.5); margin-right: 1.5rem;">
                                        <label class="form-check-label" for="productActive"><span
                                                id="statusText">下架</span></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">主要圖片</label>
                                    <div class="border rounded p-2 text-center"
                                        style="height: 200px; overflow: hidden;">
                                        <img id="mainImagePreview" src="" class="img-fluid"
                                            style="display: none; max-width: 100%; max-height: 100%; object-fit: contain;">
                                    </div>
                                    <div class="btn-group mt-2" role="group">
                                        <button type="button" class="btn btn-outline-secondary" id="editMainImage"
                                            data-bs-toggle="modal" data-bs-target="#editMainImageModal">
                                            <i class="bi bi-pencil"></i> 編輯
                                        </button>
                                    </div>
                                    <input type="file" id="mainImageInput" accept="image/jpeg,image/jpg,image/png"
                                        style="display: none;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">詳細圖片</label>
                                    <div id="galleryImagesContainer" class="border rounded p-2">
                                        <div class="d-flex flex-wrap gap-2 align-items-center"
                                            id="galleryImagesPreview">
                                            <!-- 暫無詳細圖片 -->
                                        </div>
                                        <div class="btn-group mt-2" role="group">
                                            <button type="button" class="btn btn-outline-primary" id="addGalleryImage">
                                                <i class="bi bi-plus"></i> 添加
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" id="manageGallery"
                                                data-bs-toggle="modal" data-bs-target="#manageGalleryModal">
                                                <i class="bi bi-gear"></i> 管理
                                            </button>
                                        </div>
                                        <input type="file" id="galleryImageInput"
                                            accept="image/jpeg,image/jpg,image/png" multiple style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>尺寸與價格</h5>
                                <div id="sizePriceContainer">
                                    <div class="card mb-3 size-price-block">
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">尺寸</label>
                                                    <input type="number" class="form-control size-input"
                                                        placeholder="尺寸">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">尺寸描述</label>
                                                    <input type="text" class="form-control size-desc-input"
                                                        placeholder="尺寸描述">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">價格</label>
                                                    <input type="number" class="form-control price-input"
                                                        placeholder="價格">
                                                </div>
                                                <div class="col-md-3 text-end">
                                                    <button type="button"
                                                        class="btn btn-outline-danger remove-size-btn">
                                                        <i class="bi bi-trash"></i> 刪除
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <button type="button" class="btn btn-outline-primary add-color-btn">
                                                        <i class="bi bi-plus"></i> 添加顏色
                                                    </button>
                                                    <div class="color-stock-container mt-3">
                                                        <div class="mb-2 color-row">
                                                            <div class="row g-2">
                                                                <div class="col-md-6">
                                                                    <input type="text" class="form-control color-input"
                                                                        placeholder="顏色描述">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="input-group">
                                                                        <button
                                                                            class="btn btn-outline-secondary btn-minus"
                                                                            type="button">-</button>
                                                                        <input type="number"
                                                                            class="form-control text-center stock-input"
                                                                            value="0" min="0">
                                                                        <button
                                                                            class="btn btn-outline-secondary btn-plus"
                                                                            type="button">+</button>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2 " style=" align-self: end;">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger w-100 remove-color-btn"
                                                                        disabled>
                                                                        <i class="bi bi-trash"></i> 刪除
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary mt-2" id="addSizeRow">
                                    <i class="bi bi-plus"></i> 添加尺寸
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="confirmAddProduct">新增商品</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        "use strict";

        /* ----------  常數 ---------- */
        const API_BASE = "api/product.php";
        const GET_URL = "api/getProducts.php"; // 假設此 API 用於獲取產品列表及單一產品詳情
        const TYPE_OPTIONS = {
            aluminum: "鋁框款",
            zipper: "拉鍊款",
            accessories: "行李箱配件",
            travel: "旅遊周邊",
            featured: "精選商品",
        };
        let CURRENT_PRODUCT_ID = null; // 追蹤目前編輯的商品 ID

        /* ----------  DOM 快取 ---------- */
        const body = document.body;
        const productTableBody = document.getElementById("productTableBody");
        const addProductModalEl = document.getElementById("addProductModal");
        const addProductModal = new bootstrap.Modal(addProductModalEl); // Modal 實例
        const editMainImageModalEl = document.getElementById('editMainImageModal');
        const manageGalleryModalEl = document.getElementById('manageGalleryModal');
        const sizePriceContainer = document.getElementById('sizePriceContainer');

        /* 暫存詳細圖片處理狀態 (全局變數，由不同函數修改和使用) */
        let deletedGallerySrcs = []; // 儲存已刪除的舊圖片路徑 (非 blob URL)
        let newGalleryFiles = [];    // 儲存新增上傳的圖片檔案 (File 物件)

        /* Toast 容器 */
        const toastContainer = (() => {
            const div = document.createElement("div");
            div.className = "position-fixed bottom-0 end-0 p-3";
            body.appendChild(div);
            return div;
        })();

        /* ----------  輔助函式 ---------- */
        const jsonFetch = async (url, opt) => {
            const res = await fetch(url, opt);
            if (!res.ok) {
                const errorText = await res.text();
                throw new Error(`HTTP 錯誤 ${res.status}: ${errorText}`);
            }
            return res.json();
        };

        const showToast = (msg, type = "success") => {
            const el = document.createElement("div");
            el.className = `toast align-items-center text-white bg-${type} border-0`;
            el.role = "alert";
            el.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${type === "success" ? "bi-check-circle" : "bi-exclamation-triangle"} me-2"></i>${msg}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>`;
            toastContainer.appendChild(el);
            new bootstrap.Toast(el, { delay: 3000 }).show();
        };

        const showError = (title, err) => {
            console.error(title, err);
            showToast(`${title}：${err.message || err}`, "danger");
        };

        /* ----------  初始化 ---------- */
        document.addEventListener("DOMContentLoaded", () => {
            populateTypeSelects();
            loadProducts();
            bindUIEvents();
            bindModalEvents();
        });

        /* ----------  下拉選單 ---------- */
        function populateTypeSelects() {
            const selects = [
                document.getElementById("productType"),
                document.getElementById("filterType"),
            ].filter(Boolean); // 過濾掉可能不存在的元素

            selects.forEach(sel => {
                const withAllOption = sel.id === "filterType";
                sel.innerHTML = "";
                if (withAllOption) sel.appendChild(new Option("全部類型", ""));
                Object.entries(TYPE_OPTIONS).forEach(([value, text]) => {
                    const option = new Option(text, value);
                    // 如果是篩選類型，且PHP傳遞的type參數符合，則選中
                    if (sel.id === 'filterType' && value === '<?= $type ?>') {
                        option.selected = true;
                    }
                    sel.appendChild(option);
                });
            });

            // 針對 filterStatus，確保 PHP 傳遞的值被選中
            const filterStatusSelect = document.getElementById("filterStatus");
            if (filterStatusSelect) {
                Array.from(filterStatusSelect.options).forEach(option => {
                    if (option.value === '<?= $status ?>') {
                        option.selected = true;
                    }
                });
            }
        }

        /* ----------  讀取產品 ---------- */
        async function loadProducts() {
            try {
                const { products = [] } = await jsonFetch(`${GET_URL}?action=getProducts`);
                renderTable(products);
            } catch (err) { showError("載入商品失敗", err); }
        }

        /* ----------  表格呈現 ---------- */
        const getTypeName = t => TYPE_OPTIONS[t?.trim().toLowerCase()] || t || "";

        const createTableRow = p => `
        <tr>
            <td>${p.ProductID}</td>
            <td title="${p.ProductName}">${p.ProductName}</td>
            <td>${getTypeName(p.Type)}</td>
            <td>${p.TotalStock}</td>
            <td><span class="badge bg-${p.isActive == 1 ? "success" : "secondary"}">${p.isActive == 1 ? "上架" : "下架"}</span></td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary edit-product" data-id="${p.ProductID}"><i class="bi bi-pencil"></i></button>
                </div>
            </td>
        </tr>`;

        function renderTable(products) {
            productTableBody.innerHTML = products.length
                ? products.map(createTableRow).join("")
                : '<tr><td colspan="6" class="text-center">暫無商品資料</td></tr>';
        }

        /* ----------  模態框事件 ---------- */
        function bindModalEvents() {
            // 當編輯主要圖片模態框關閉時，重新顯示新增商品模態框
            editMainImageModalEl.addEventListener('hidden.bs.modal', () => {
                addProductModal.show();
            });

            // 當管理詳細圖片模態框關閉時，重新顯示新增商品模態框
            manageGalleryModalEl.addEventListener('hidden.bs.modal', () => {
                addProductModal.show();
            });

            // 處理庫存數量按鈕事件 (使用事件委派在 addProductModalEl 上)
            addProductModalEl.addEventListener('click', (event) => {
                const target = event.target;
                if (target.classList.contains('btn-minus')) {
                    const input = target.closest('.input-group')?.querySelector('.stock-input');
                    if (input) {
                        let value = parseInt(input.value, 10);
                        if (value > 0) input.value = value - 1;
                    }
                } else if (target.classList.contains('btn-plus')) {
                    const input = target.closest('.input-group')?.querySelector('.stock-input');
                    if (input) {
                        let value = parseInt(input.value, 10);
                        input.value = value + 1;
                    }
                }
            });
        }

        /* ----------  事件繫結 ---------- */
        function bindUIEvents() {
            // 使用 Map 集中管理不同按鈕的處理函數，提高可讀性和擴展性
            const buttonHandlers = new Map([
                ['addSizeRow', () => addSizeRow()],
                ['confirmAddProduct', (btn) => handleAddOrUpdate(btn)],
                ['editMainImage', () => initEditMainImageModal()],
                ['manageGallery', () => initManageGalleryModal()],
                ['saveMainImageBtn', () => saveMainImage()],
                ['saveGalleryBtn', () => saveGallery()],
                ['addGalleryImage', () => addProductModalEl.querySelector("#galleryImageInput").click()],
            ]);

            body.addEventListener("click", e => {
                const btn = e.target.closest("button");
                if (!btn) return;

                // 委派處理通用類別按鈕
                if (btn.classList.contains("edit-product")) return handleEdit(btn);
                if (btn.classList.contains("remove-size-btn")) return removeSizeRow(btn);
                if (btn.classList.contains("add-color-btn")) return addColorRow(btn);
                if (btn.classList.contains("remove-color-btn")) return removeColorRow(btn);
                if (btn.classList.contains("delete-gallery-image")) return handleDeleteGalleryImage(btn);

                // 處理新增檔案後新創建的圖片刪除按鈕 (此按鈕的 `data-src` 可能為 `blob:` 開頭)
                // 注意：這裡使用 `e.target.closest(".remove-new-gallery-image")` 而非 `btn.classList.contains`，因為 btn 可能不是直接的按鈕
                if (e.target.closest(".remove-new-gallery-image")) {
                    const card = e.target.closest(".col-md-4");
                    // 根據圖片來源，從 newGalleryFiles 中移除對應的檔案
                    const imgElement = card.querySelector('img');
                    if (imgElement && imgElement.src.startsWith('blob:')) {
                        newGalleryFiles = newGalleryFiles.filter(file => URL.createObjectURL(file) !== imgElement.src);
                    }
                    card.remove(); // 從 DOM 中移除卡片
                    return;
                }

                // 處理特定 ID 按鈕
                const handler = buttonHandlers.get(btn.id);
                if (handler) handler(btn);
            });

            // 初始化 jQuery UI Sortable
            $(document).ready(function () {
                $("#gallerySortable").sortable({
                    placeholder: "ui-state-highlight",
                    update: function (event, ui) {
                        updateGalleryOrder(); // 重新排序後，如果需要保存順序，在此處處理
                    }
                });
                $("#gallerySortable").disableSelection();
            });

            /* 其他表單事件 */
            body.addEventListener("change", e => {
                const t = e.target;
                if (t.id === "productActive") updateStatusText(t);
                else if (t.id === "mainImageInput") previewMainImage(t);
                else if (t.id === "galleryImageInput") previewGalleryImages(t);
                else if (t.id === "filterType" || t.id === "filterStatus") filterProducts();
                else if (t.id === "newGalleryImageInput") handleNewGalleryUpload(t);
            });

            document.getElementById("searchProduct")?.addEventListener("input", filterProducts);

            // 開啟 modal 時若非編輯模式則重置表單
            document.querySelectorAll('[data-bs-target="#addProductModal"]').forEach(btn =>
                btn.addEventListener("click", () => {
                    if (!btn.dataset.editMode) {
                        resetForm();
                    }
                })
            );

            // 初始時呼叫一次，以確保一開始就正確 disable 尺寸/顏色按鈕
            addProductModalEl.addEventListener('shown.bs.modal', () => {
                updateSizeButtons();
                addProductModalEl.querySelectorAll('.size-price-block').forEach(block => {
                    updateColorButtons(block);
                });
            });
        }


        /* ----------  圖片管理函數 ---------- */
        function initEditMainImageModal() {
            const mainImagePreview = addProductModalEl.querySelector('#mainImagePreview');
            const editMainImagePreview = editMainImageModalEl.querySelector('#editMainImagePreview');
            const newMainImageInput = editMainImageModalEl.querySelector('#newMainImageInput');

            // 確保有圖片來源才設定，否則清除
            if (mainImagePreview.src && mainImagePreview.style.display !== 'none') {
                editMainImagePreview.src = mainImagePreview.src;
                editMainImagePreview.style.display = 'block'; // 確保預覽顯示
            } else {
                editMainImagePreview.src = '';
                editMainImagePreview.style.display = 'none';
            }
            // 清空編輯模態框的 file input，避免重複選擇同一個文件不觸發change事件
            if (newMainImageInput) {
                newMainImageInput.value = '';
            }
        }

        function saveMainImage() {
            try {
                const fileInput = editMainImageModalEl.querySelector('#newMainImageInput');
                // 如果沒有選擇新圖片，直接關閉模態框
                if (!fileInput || fileInput.files.length === 0) {
                    bootstrap.Modal.getInstance(editMainImageModalEl).hide();
                    return;
                }

                const file = fileInput.files[0];
                const mainImageInput = addProductModalEl.querySelector('#mainImageInput');
                const mainImagePreview = addProductModalEl.querySelector('#mainImagePreview');

                // 將檔案從編輯模態框複製到主表單的 hidden input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                mainImageInput.files = dataTransfer.files;

                // 更新主表單的預覽
                const reader = new FileReader();
                reader.onload = function (e) {
                    mainImagePreview.src = e.target.result;
                    mainImagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);

                bootstrap.Modal.getInstance(editMainImageModalEl).hide();
            } catch (error) {
                console.error('儲存主要圖片時發生錯誤:', error);
                showError('儲存主要圖片失敗', error);
                bootstrap.Modal.getInstance(editMainImageModalEl).hide();
            }
        }

        function initManageGalleryModal() {
            const gallerySortable = manageGalleryModalEl.querySelector('#gallerySortable');
            gallerySortable.innerHTML = ''; // 清空現有圖片顯示

            // 將主表單預覽區的所有圖片（包括舊圖和新圖）轉移到管理模態框
            const galleryImagesPreview = addProductModalEl.querySelector('#galleryImagesPreview');
            Array.from(galleryImagesPreview.querySelectorAll('img')).forEach(img => {
                const imgDiv = document.createElement('div');
                imgDiv.className = 'col-md-4 mb-2';
                // 檢查圖片是否是新上傳的 blob URL，如果是，則添加對應的 class 和按鈕類型
                const isNewBlob = img.src.startsWith('blob:');
                imgDiv.innerHTML = `
                    <div class="card ${isNewBlob ? 'new-gallery-card' : ''}">
                        <img src="${img.src}" class="card-img-top">
                        <div class="card-body p-2">
                            <button type="button" class="btn btn-sm btn-danger w-100 ${isNewBlob ? 'remove-new-gallery-image' : 'delete-gallery-image'}"
                                data-src="${img.src}">
                                <i class="bi bi-trash"></i> 刪除
                            </button>
                        </div>
                    </div>
                `;
                gallerySortable.appendChild(imgDiv);
            });

            // 清空管理模態框的 file input，避免重複選擇同一個文件不觸發change事件
            const newGalleryImageInput = manageGalleryModalEl.querySelector('#newGalleryImageInput');
            if (newGalleryImageInput) {
                newGalleryImageInput.value = '';
            }
        }

        function handleNewGalleryUpload(input) {
            Array.from(input.files).forEach(file => {
                // 將新檔案加入全局 newGalleryFiles 列表
                newGalleryFiles.push(file);

                // 為每個新檔案創建一個預覽卡片並插入到管理模態框的 sortable 容器中
                const reader = new FileReader();
                reader.onload = e => {
                    const html = `
                    <div class="col-md-4 mb-2">
                        <div class="card new-gallery-card">
                            <img src="${e.target.result}" class="card-img-top">
                            <div class="card-body p-2">
                                <button type="button" class="btn btn-sm btn-danger w-100 remove-new-gallery-image">
                                    <i class="bi bi-trash"></i> 刪除
                                </button>
                            </div>
                        </div>
                    </div>`;
                    manageGalleryModalEl.querySelector("#gallerySortable")
                        .insertAdjacentHTML("beforeend", html);
                };
                reader.readAsDataURL(file);
            });
            // 清空 input，以便重覆選同檔也會觸發 change 事件
            input.value = "";
        }

        function handleDeleteGalleryImage(btn) {
            const srcToDelete = btn.dataset.src; // 圖片的 src (可能是舊圖的伺服器路徑或新圖的 blob URL)
            const cardToRemove = btn.closest('.col-md-4');

            // 如果圖片來源不是 blob URL (表示是舊圖)，則加入刪除清單
            if (!srcToDelete.startsWith('blob:')) {
                deletedGallerySrcs.push(srcToDelete);
            } else {
                // 如果是 blob URL (新上傳但還未保存的圖片)，則從 newGalleryFiles 中移除對應的檔案
                newGalleryFiles = newGalleryFiles.filter(file => URL.createObjectURL(file) !== srcToDelete);
            }
            cardToRemove.remove(); // 從 DOM 中移除卡片
        }

        function updateGalleryOrder() {
            // 這個函數用於 jQuery UI Sortable 的 update 事件。
            // 如果需要保存排序，應該在此處讀取 #gallerySortable 中圖片的新順序，
            // 並更新一個內部狀態（例如一個圖片 URL 陣列），該狀態最終將在 saveGallery 或 handleAddOrUpdate 時發送到後端。
            // 目前程式碼中未實現排序保存邏輯，僅為佔位符。
        }

        async function saveGallery() {
            const galleryImagesPreview = addProductModalEl.querySelector('#galleryImagesPreview');
            galleryImagesPreview.innerHTML = ''; // 清空主預覽區

            // 1. 取得管理模態框中所有圖片（包括舊圖和新圖）的當前順序
            const currentImagesInModal = Array.from(manageGalleryModalEl.querySelectorAll('#gallerySortable .card img'));

            // 2. 更新主預覽區的圖片顯示
            currentImagesInModal.forEach(img => {
                const im = document.createElement("img");
                im.src = img.src;
                im.className = "img-thumbnail";
                im.style.cssText = "width:100px;height:100px;margin:4px";
                galleryImagesPreview.appendChild(im);
            });

            // 3. 關閉管理模態框
            bootstrap.Modal.getInstance(manageGalleryModalEl).hide();
            // 4. 更新詳細圖片按鈕狀態
            updateGalleryButtons();
        }

        async function handleEdit(btn) {
            try {
                const productId = btn.dataset.id;
                const { product } = await jsonFetch(`${GET_URL}?action=getProduct&id=${productId}`);
                CURRENT_PRODUCT_ID = product.ProductID;
                fillForm(product); // 填入表單資料並重置臨時狀態
                addProductModal.show();
            } catch (err) { showError("獲取商品詳情失敗", err); }
        }

        /* ----------  新增 / 更新 ---------- */
        async function handleAddOrUpdate(confirmBtn) {
            const isEdit = !!confirmBtn.dataset.editMode;
            // 收集所有表單數據，包括 product、gallery 和 options
            const allData = collectAllFormData(isEdit);

            try {
                // 準備商品主資料的 FormData
                const productFd = new FormData();
                Object.entries(allData.product).forEach(([key, value]) => {
                    if (key === "mainImageFile") {
                        if (value) productFd.append("mainImage", value);
                    } else {
                        productFd.append(key, value);
                    }
                });

                // 1) 第一次 API 請求：新增/更新商品主資料 (Product, ProductOption)
                const productRes = await jsonFetch(API_BASE, { method: "POST", body: productFd });
                if (!productRes.success) throw new Error(productRes.message);

                // 獲取商品ID (如果是新增，則從響應中獲取；如果是編輯，則從現有資料中獲取)
                const productId = productRes.productId || allData.product.productId;
                if (!productId) throw new Error("無法取得商品ID，請檢查API響應");

                // 準備詳細圖片的 FormData
                const galleryFd = new FormData();
                galleryFd.append("action", "updateGallery");
                galleryFd.append("productId", productId);

                // 將保留的舊圖路徑傳入
                if (allData.gallery.remainingSrcs.length) {
                    galleryFd.append("remainingSrcs", JSON.stringify(allData.gallery.remainingSrcs));
                }
                // 將已刪除的舊圖路徑傳入
                if (allData.gallery.deletedSrcs.length) {
                    galleryFd.append("deletedSrcs", JSON.stringify(allData.gallery.deletedSrcs));
                }
                // 將新上傳的檔案傳入
                allData.gallery.newFiles.forEach((file) => {
                    galleryFd.append("newGalleryImages[]", file, file.name);
                });

                // 2) 第二次 API 請求：更新詳細圖片 (ProductGallery)
                const galleryRes = await jsonFetch(API_BASE, { method: "POST", body: galleryFd });
                if (!galleryRes.success) throw new Error("圖庫更新失敗：" + galleryRes.message);

                showToast(isEdit ? "商品更新完成！" : "商品新增完成！", "success");
                addProductModal.hide(); // 關閉模態框
                loadProducts();      // 重新載入商品列表
                resetForm();         // 操作成功後重置表單和臨時狀態
            } catch (err) {
                showError("操作失敗", err);
            }
        }

        /* ----------  篩選 ---------- */
        async function filterProducts() {
            const params = new URLSearchParams();
            const type = document.getElementById("filterType").value;
            const status = document.getElementById("filterStatus").value;
            const search = document.getElementById("searchProduct").value;
            if (type) params.append("type", type);
            if (status !== '') params.append("status", status); // '0'也應被考慮為有效值
            if (search) params.append("search", search);
            try {
                const { products = [] } = await jsonFetch(`${API_BASE}?action=filter&${params}`);
                renderTable(products);
            } catch (err) { showError("篩選失敗", err); }
        }

        /* ====================================================================== */
        /* =========================  Modal 相關函式  =========================== */
        /* ====================================================================== */

        /* --- 生成 DOM：顏色列 --- */
        function createColorRow({ OptionID = '', Color = '', Stock = 0 } = {}) {
            const div = document.createElement("div");
            div.className = "mb-2 color-row";
            div.innerHTML = `
                <input type="hidden" class="color-option-id-input" value="${OptionID}">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control color-input" placeholder="顏色描述" value="${Color}">
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary btn-minus">-</button>
                            <input type="number" class="form-control text-center stock-input" value="${Stock}" min="0">
                            <button type="button" class="btn btn-outline-secondary btn-plus">+</button>
                        </div>
                    </div>
                    <div class="col-md-2" style="align-self:end;">
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-color-btn">
                            <i class="bi bi-trash"></i> 刪除
                        </button>
                    </div>
                </div>`;
            return div;
        }

        /* --- 生成 DOM：尺寸卡片 --- */
        function createSizeBlock({ Size = '', SizeDescription = '', Price = '', colors = [] } = {}) {
            const wrap = document.createElement("div");
            wrap.className = "card mb-3 size-price-block";

            wrap.innerHTML = `
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">尺寸</label>
                            <input type="number" class="form-control size-input" value="${Size}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">尺寸描述</label>
                            <input type="text" class="form-control size-desc-input" value="${SizeDescription}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">價格</label>
                            <input type="number" class="form-control price-input" value="${Price}">
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="button" class="btn btn-outline-danger remove-size-btn">
                                <i class="bi bi-trash"></i> 刪除
                            </button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-primary add-color-btn">
                                <i class="bi bi-plus"></i> 添加顏色
                            </button>
                            <div class="color-stock-container mt-3"></div>
                        </div>
                    </div>
                </div>`;

            // 填入顏色列
            const colorCt = wrap.querySelector(".color-stock-container");
            if (colors.length) {
                colors.forEach(c => colorCt.appendChild(createColorRow(c)));
            } else {
                colorCt.appendChild(createColorRow()); // 預設添加一個空的顏色列
            }

            return wrap;
        }

        /* --- 尺寸 / 顏色增刪按鈕狀態更新 --- */
        function updateSizeButtons() {
            const blocks = addProductModalEl.querySelectorAll('.size-price-block');
            const removeBtns = addProductModalEl.querySelectorAll('.size-price-block .remove-size-btn');
            removeBtns.forEach(btn => btn.disabled = (blocks.length <= 1));
        }

        function updateColorButtons(block) {
            const rows = block.querySelectorAll('.color-row');
            const removeBtns = block.querySelectorAll('.remove-color-btn');
            removeBtns.forEach(btn => btn.disabled = (rows.length <= 1));
        }

        // 新增尺寸：加入後更新按鈕狀態
        function addSizeRow(data = {}) {
            const newBlock = createSizeBlock(data);
            sizePriceContainer.appendChild(newBlock);
            updateSizeButtons();
            updateColorButtons(newBlock);
        }

        // 刪除尺寸：刪除後更新按鈕狀態
        function removeSizeRow(btn) {
            btn.closest('.size-price-block').remove();
            updateSizeButtons();
        }

        // 新增顏色：加入後更新該 block 的按鈕狀態
        function addColorRow(btn) {
            const block = btn.closest('.size-price-block');
            const colorCt = block.querySelector('.color-stock-container');
            colorCt.appendChild(createColorRow());
            updateColorButtons(block);
        }

        // 刪除顏色：刪除後更新該 block 的按鈕狀態
        function removeColorRow(btn) {
            const row = btn.closest('.color-row');
            const block = btn.closest('.size-price-block');
            row.remove();
            updateColorButtons(block);
        }

        /* --- 其他 UI 更新 --- */
        const updateStatusText = chk => addProductModalEl.querySelector("#statusText").textContent = chk.checked ? "上架" : "下架";

        /* 圖片預覽 */
        function previewMainImage(input) {
            const file = input.files[0];
            const img = addProductModalEl.querySelector("#mainImagePreview");
            if (file && img) {
                img.src = URL.createObjectURL(file);
                img.style.display = "block";
            } else if (img) {
                img.src = "";
                img.style.display = "none";
            }
        }

        function previewGalleryImages(input) {
            const galleryImagesPreview = addProductModalEl.querySelector("#galleryImagesPreview");
            galleryImagesPreview.innerHTML = ""; // 清空現有預覽

            // 將新選擇的檔案添加到 newGalleryFiles 並預覽
            Array.from(input.files).forEach(f => {
                newGalleryFiles.push(f); // 將檔案加入全局新圖片列表
                const img = document.createElement("img");
                img.src = URL.createObjectURL(f);
                img.className = "img-thumbnail";
                img.style.cssText = "width:100px;height:100px;margin:4px";
                galleryImagesPreview.appendChild(img);
            });
            updateGalleryButtons(); // 更新詳細圖片按鈕狀態
        }

        /* --- 填入/重置表單 --- */
        function fillForm(p) {
            resetForm(); // 先重置表單以清除舊狀態和臨時圖片陣列

            addProductModalEl.querySelector("#addProductModalLabel").textContent = "編輯商品";
            const confirmBtn = addProductModalEl.querySelector("#confirmAddProduct");
            confirmBtn.textContent = "更新商品";
            confirmBtn.dataset.editMode = "true";
            confirmBtn.dataset.productId = p.ProductID;

            addProductModalEl.querySelector("#productName").value = p.ProductName;
            addProductModalEl.querySelector("#productType").value = p.Type.toString().toLowerCase();
            addProductModalEl.querySelector("#productIntro").value = p.Introdution;

            const activeChk = addProductModalEl.querySelector("#productActive");
            activeChk.checked = p.isActive == 1;
            updateStatusText(activeChk);

            /* 主圖 */
            const mainImg = addProductModalEl.querySelector("#mainImagePreview");
            if (mainImg) {
                mainImg.src = p.mainImage || '';
                mainImg.style.display = p.mainImage ? "block" : "none";
            }

            /* 詳細圖片 */
            const galleryImagesPreview = addProductModalEl.querySelector("#galleryImagesPreview");
            galleryImagesPreview.innerHTML = ""; // 清空預覽區

            if (p.galleryImage?.length) {
                p.galleryImage.forEach(src => {
                    const im = document.createElement("img");
                    im.src = src;
                    im.className = "img-thumbnail";
                    im.style.cssText = "width:100px;height:100px;margin:4px";
                    galleryImagesPreview.appendChild(im);
                });
            } else {
                // 如果沒有圖片，會由 updateGalleryButtons 處理顯示提示文字
            }
            updateGalleryButtons(); // 根據是否有圖片更新按鈕狀態

            // --- 處理 尺寸／顏色 塊 ---
            sizePriceContainer.innerHTML = ""; // 清空現有尺寸區塊

            if (p.sizeOptions?.length) {
                // 將產品選項按尺寸、尺寸描述、價格分組
                const groupedOptions = p.sizeOptions.reduce((acc, o) => {
                    const key = `${o.Size}||${o.SizeDescription}||${o.Price}`;
                    if (!acc[key]) {
                        acc[key] = {
                            Size: o.Size,
                            SizeDescription: o.SizeDescription,
                            Price: o.Price,
                            colors: []
                        };
                    }
                    acc[key].colors.push({
                        OptionID: o.OptionID,
                        Color: o.Color,
                        Stock: o.Stock
                    });
                    return acc;
                }, {});

                // 根據分組結果創建尺寸區塊
                Object.values(groupedOptions).forEach(blockData => {
                    const block = createSizeBlock({
                        Size: blockData.Size,
                        SizeDescription: blockData.SizeDescription,
                        Price: blockData.Price,
                        colors: blockData.colors
                    });
                    sizePriceContainer.appendChild(block);
                    updateSizeButtons();
                    updateColorButtons(block);
                });
            } else {
                addSizeRow(); // 如果沒有尺寸選項，添加一個預設的尺寸列
            }
        }

        function resetForm() {
            // 標題與按鈕回復
            addProductModalEl.querySelector("#addProductModalLabel").textContent = "新增商品";
            const confirmBtn = addProductModalEl.querySelector("#confirmAddProduct");
            confirmBtn.textContent = "新增商品";
            delete confirmBtn.dataset.editMode;
            delete confirmBtn.dataset.productId;

            // 清空表單欄位
            addProductModalEl.querySelector("form").reset();
            addProductModalEl.querySelector("#productActive").checked = false; // 確保下架狀態
            addProductModalEl.querySelector("#statusText").textContent = "下架";

            // 清空主要圖片預覽和輸入
            const mainImg = addProductModalEl.querySelector("#mainImagePreview");
            if (mainImg) {
                mainImg.src = "";
                mainImg.style.display = "none";
            }
            const mainInput = addProductModalEl.querySelector("#mainImageInput");
            if (mainInput) mainInput.value = "";
            const newMainInput = editMainImageModalEl.querySelector("#newMainImageInput");
            if (newMainInput) newMainInput.value = "";

            // 清空詳細圖片相關狀態和預覽
            deletedGallerySrcs = []; // 清空已刪除舊圖列表
            newGalleryFiles = [];    // 清空新增圖片列表

            const galleryImagesPreview = addProductModalEl.querySelector("#galleryImagesPreview");
            galleryImagesPreview.innerHTML = ''; // 清空預覽區內容
            const galleryInput = addProductModalEl.querySelector("#galleryImageInput");
            if (galleryInput) galleryInput.value = ""; // 清空檔案輸入
            const newGalleryInput = manageGalleryModalEl.querySelector("#newGalleryImageInput");
            if (newGalleryInput) newGalleryInput.value = "";
            updateGalleryButtons(); // 更新詳細圖片按鈕狀態，會自動顯示 "暫無詳細圖片"

            // 清空尺寸／顏色區塊並添加一個預設行
            sizePriceContainer.innerHTML = "";
            addSizeRow();
        }

        /* --- 收集所有表單資料 --- */
        function collectAllFormData(isEdit) {
            const data = {
                product: {},
                gallery: {
                    remainingSrcs: [],
                    deletedSrcs: [],
                    newFiles: []
                },
                options: []
            };

            data.product.action = isEdit ? "updateProduct" : "addProduct";
            if (isEdit) data.product.productId = addProductModalEl.querySelector("#confirmAddProduct").dataset.productId;

            data.product.productName = addProductModalEl.querySelector("#productName").value;
            data.product.productType = addProductModalEl.querySelector("#productType").value;
            data.product.productIntro = addProductModalEl.querySelector("#productIntro").value;
            data.product.productActive = addProductModalEl.querySelector("#productActive").checked ? "1" : "0";
            data.product.mainImageFile = addProductModalEl.querySelector("#mainImageInput").files[0];

            // 獲取詳細圖片的當前狀態 (從主預覽區獲取，因為 saveGallery 已將所有已選擇的圖片轉移到此處)
            const currentGalleryImages = Array.from(
                addProductModalEl.querySelectorAll('#galleryImagesPreview img')
            ).map(img => img.src);
            // 篩選出舊圖 (非 blob: 開頭的 URL)
            data.gallery.remainingSrcs = currentGalleryImages.filter(src => !src.startsWith('blob:'));
            // 獲取已刪除的舊圖 (由 handleDeleteGalleryImage 填充)
            data.gallery.deletedSrcs = deletedGallerySrcs;
            // 獲取新上傳的檔案 (由 handleNewGalleryUpload 或 previewGalleryImages 填充)
            data.gallery.newFiles = newGalleryFiles;

            // 收集尺寸與價格選項
            document.querySelectorAll(".size-price-block").forEach(block => {
                const size = block.querySelector(".size-input").value;
                const desc = block.querySelector(".size-desc-input").value;
                const price = block.querySelector(".price-input").value;
                block.querySelectorAll(".color-row").forEach(row => {
                    const optId = row.querySelector(".color-option-id-input").value;
                    const color = row.querySelector(".color-input").value.trim();
                    const stock = row.querySelector(".stock-input").value || 0;
                    // 只有當尺寸和顏色都不為空時才添加此選項
                    if (size && color) {
                        data.options.push({
                            OptionID: optId,
                            Size: size,
                            SizeDescription: desc,
                            Price: price,
                            Color: color,
                            Stock: stock
                        });
                    }
                });
            });
            data.product.sizes = JSON.stringify(data.options); // 將選項 JSON 化放入 product 資料中

            return data;
        }

        /* ----------  圖片管理函數 - 更新按鈕顯示 ---------- */
        function updateGalleryButtons() {
            const galleryImagesPreview = addProductModalEl.querySelector("#galleryImagesPreview");
            const addBtn = addProductModalEl.querySelector("#addGalleryImage");
            const manageBtn = addProductModalEl.querySelector("#manageGallery");

            // 檢查是否存在實際圖片元素，而不是只有 "暫無詳細圖片" 文字
            const hasImages = galleryImagesPreview.querySelectorAll('img').length > 0;

            if (hasImages) {
                addBtn.style.display = 'none';
                manageBtn.style.display = ''; // 顯示管理按鈕
            } else {
                addBtn.style.display = ''; // 顯示添加按鈕
                manageBtn.style.display = 'none';
                galleryImagesPreview.innerHTML = '<div class="text-muted">暫無詳細圖片</div>'; // 確保顯示提示文字
            }
        }

        /* ----------  對外 (for inline) ---------- */
        // 為了確保這些函數在任何地方都能被調用 (例如可能在舊版瀏覽器或特定框架中)，保留它們在 window 物件上的暴露
        window.addSizeRow = () => addSizeRow();
        window.addColorRow = btn => addColorRow(btn);
    })();
</script>