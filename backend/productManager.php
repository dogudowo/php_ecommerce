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
                    <thead>
                        <tr>
                            <th style="width: 7%;">商品ID</th>
                            <th style="width: 50%;">商品名稱</th>
                            <th style="width: 16%;">類型</th>
                            <th style="width: 10%;">總庫存</th>
                            <th style="width: 10%;">狀態</th>
                            <th style="width: 10%;">操作</th>
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
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<script src="api/toast.js"></script>
<script>
    (() => {
        "use strict";

        /* ---------- Constants ---------- */
        const API_BASE = "api/product.php";
        const GET_URL = "api/getProducts.php";
        const TYPE_OPTIONS = {
            aluminum: "鋁框款",
            zipper: "拉鍊款",
            accessories: "行李箱配件",
            travel: "旅遊周邊",
            featured: "精選商品",
        };
        let CURRENT_PRODUCT_ID = null;

        /* ---------- DOM Cache ---------- */
        const body = document.body;
        const productTableBody = document.getElementById("productTableBody");
        const addProductModalEl = document.getElementById("addProductModal");
        const addProductModal = new bootstrap.Modal(addProductModalEl);
        const editMainImageModalEl = document.getElementById("editMainImageModal");
        const manageGalleryModalEl = document.getElementById("manageGalleryModal");
        const sizePriceContainer = document.getElementById("sizePriceContainer");

        /* ---------- State for Gallery Images ---------- */
        let deletedGallerySrcs = [];
        let newGalleryFiles = [];

        /* ---------- Toast Container ---------- */
        const toastContainer = (() => {
            const div = document.createElement("div");
            div.className = "position-fixed bottom-0 end-0 p-3";
            body.appendChild(div);
            return div;
        })();

        /* ---------- Helper Functions ---------- */
        const jsonFetch = async (url, opt) => {
            const res = await fetch(url, opt);
            if (!res.ok) {
                const errorText = await res.text();
                throw new Error(`HTTP 錯誤 ${res.status}: ${errorText}`);
            }
            return res.json();
        };

        const showError = (title, err) => {
            console.error(title, err);
            showToast(`${title}：${err.message || err}`, "danger");
        };

        /* ---------- Initialize ---------- */
        document.addEventListener("DOMContentLoaded", () => {
            populateTypeSelects();
            loadProducts();
            bindUIEvents();
            bindModalEvents();
        });

        /* ---------- Populate Type Selects ---------- */
        function populateTypeSelects() {
            const selects = [
                document.getElementById("productType"),
                document.getElementById("filterType"),
            ].filter(Boolean);

            selects.forEach(sel => {
                sel.innerHTML = "";
                if (sel.id === "filterType") sel.appendChild(new Option("全部類型", ""));
                Object.entries(TYPE_OPTIONS).forEach(([value, text]) => {
                    const option = new Option(text, value);
                    if (sel.id === "filterType" && value === "<?= $type ?>") {
                        option.selected = true;
                    }
                    sel.appendChild(option);
                });
            });

            const filterStatusSelect = document.getElementById("filterStatus");
            if (filterStatusSelect) {
                Array.from(filterStatusSelect.options).forEach(option => {
                    if (option.value === "<?= $status ?>") {
                        option.selected = true;
                    }
                });
            }
        }

        /* ---------- Load Products ---------- */
        async function loadProducts() {
            try {
                const { products = [] } = await jsonFetch(`${GET_URL}?action=getProducts`);
                renderTable(products);
            } catch (err) {
                showError("載入商品失敗", err);
            }
        }

        /* ---------- Render Table ---------- */
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

        /* ---------- Modal Events ---------- */
        function bindModalEvents() {
            editMainImageModalEl.addEventListener("hidden.bs.modal", () => addProductModal.show());
            manageGalleryModalEl.addEventListener("hidden.bs.modal", () => addProductModal.show());

            addProductModalEl.addEventListener("click", (event) => {
                const target = event.target;
                if (target.classList.contains("btn-minus")) {
                    const input = target.closest(".input-group")?.querySelector(".stock-input");
                    if (input) {
                        let value = parseInt(input.value, 10);
                        if (value > 0) input.value = value - 1;
                    }
                } else if (target.classList.contains("btn-plus")) {
                    const input = target.closest(".input-group")?.querySelector(".stock-input");
                    if (input) {
                        input.value = parseInt(input.value, 10) + 1;
                    }
                }
            });
        }

        /* ---------- Drag and Drop for Gallery Sorting ---------- */
        function enableDragAndDrop(container) {
            let draggedItem = null;

            container.addEventListener("dragstart", (e) => {
                draggedItem = e.target.closest(".col-md-4");
                if (draggedItem) {
                    e.dataTransfer.setData("text/plain", "");
                    draggedItem.classList.add("dragging");
                }
            });

            container.addEventListener("dragend", (e) => {
                if (draggedItem) {
                    draggedItem.classList.remove("dragging");
                    draggedItem = null;
                    updateGalleryOrder();
                }
            });

            container.addEventListener("dragover", (e) => {
                e.preventDefault();
            });

            container.addEventListener("drop", (e) => {
                e.preventDefault();
                if (!draggedItem) return;

                const allItems = Array.from(container.querySelectorAll(".col-md-4"));
                const dropTarget = e.target.closest(".col-md-4");
                if (!dropTarget || dropTarget === draggedItem) return;

                const dropIndex = allItems.indexOf(dropTarget);
                const draggedIndex = allItems.indexOf(draggedItem);

                if (draggedIndex < dropIndex) {
                    container.insertBefore(draggedItem, dropTarget.nextSibling);
                } else {
                    container.insertBefore(draggedItem, dropTarget);
                }
            });

            // Make gallery images draggable
            container.querySelectorAll(".col-md-4").forEach(item => {
                item.draggable = true;
            });
        }

        /* ---------- UI Event Bindings ---------- */
        function bindUIEvents() {
            const buttonHandlers = new Map([
                ["addSizeRow", () => addSizeRow()],
                ["confirmAddProduct", (btn) => handleAddOrUpdate(btn)],
                ["editMainImage", () => initEditMainImageModal()],
                ["manageGallery", () => initManageGalleryModal()],
                ["saveMainImageBtn", () => saveMainImage()],
                ["saveGalleryBtn", () => saveGallery()],
                ["addGalleryImage", () => addProductModalEl.querySelector("#galleryImageInput").click()],
            ]);

            body.addEventListener("click", (e) => {
                const btn = e.target.closest("button");
                if (!btn) return;

                if (btn.classList.contains("edit-product")) return handleEdit(btn);
                if (btn.classList.contains("remove-size-btn")) return removeSizeRow(btn);
                if (btn.classList.contains("add-color-btn")) return addColorRow(btn);
                if (btn.classList.contains("remove-color-btn")) return removeColorRow(btn);
                if (btn.classList.contains("delete-gallery-image")) return handleDeleteGalleryImage(btn);
                if (e.target.closest(".remove-new-gallery-image")) {
                    const card = e.target.closest(".col-md-4");
                    const imgElement = card.querySelector("img");
                    if (imgElement && imgElement.src.startsWith("blob:")) {
                        newGalleryFiles = newGalleryFiles.filter(file => URL.createObjectURL(file) !== imgElement.src);
                    }
                    card.remove();
                    return;
                }

                const handler = buttonHandlers.get(btn.id);
                if (handler) handler(btn);
            });

            body.addEventListener("change", (e) => {
                const t = e.target;
                if (t.id === "productActive") updateStatusText(t);
                else if (t.id === "mainImageInput") previewMainImage(t);
                else if (t.id === "galleryImageInput") previewGalleryImages(t);
                else if (t.id === "filterType" || t.id === "filterStatus") filterProducts();
                else if (t.id === "newGalleryImageInput") handleNewGalleryUpload(t);
            });

            document.getElementById("searchProduct")?.addEventListener("input", filterProducts);

            document.querySelectorAll('[data-bs-target="#addProductModal"]').forEach(btn =>
                btn.addEventListener("click", () => {
                    if (!btn.dataset.editMode) resetForm();
                })
            );

            addProductModalEl.addEventListener("shown.bs.modal", () => {
                updateSizeButtons();
                addProductModalEl.querySelectorAll(".size-price-block").forEach(block => {
                    updateColorButtons(block);
                });
            });
        }

        /* ---------- Image Management Functions ---------- */
        function initEditMainImageModal() {
            const mainImagePreview = addProductModalEl.querySelector("#mainImagePreview");
            const editMainImagePreview = editMainImageModalEl.querySelector("#editMainImagePreview");
            const newMainImageInput = editMainImageModalEl.querySelector("#newMainImageInput");

            editMainImagePreview.src = mainImagePreview.src && mainImagePreview.style.display !== "none" ? mainImagePreview.src + '?' + new Date().getTime() : "";
            editMainImagePreview.style.display = editMainImagePreview.src ? "block" : "none";
            if (newMainImageInput) newMainImageInput.value = "";
        }

        function saveMainImage() {
            try {
                const fileInput = editMainImageModalEl.querySelector("#newMainImageInput");
                if (!fileInput?.files.length) {
                    bootstrap.Modal.getInstance(editMainImageModalEl).hide();
                    return;
                }

                const file = fileInput.files[0];
                const mainImageInput = addProductModalEl.querySelector("#mainImageInput");
                const mainImagePreview = addProductModalEl.querySelector("#mainImagePreview");

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                mainImageInput.files = dataTransfer.files;

                const reader = new FileReader();
                reader.onload = (e) => {
                    mainImagePreview.src = e.target.result;
                    mainImagePreview.style.display = "block";
                };
                reader.readAsDataURL(file);

                bootstrap.Modal.getInstance(editMainImageModalEl).hide();
            } catch (error) {
                showError("儲存主要圖片失敗", error);
                bootstrap.Modal.getInstance(editMainImageModalEl).hide();
            }
        }

        function initManageGalleryModal() {
            const gallerySortable = manageGalleryModalEl.querySelector("#gallerySortable");
            gallerySortable.innerHTML = "";

            const galleryImagesPreview = addProductModalEl.querySelector("#galleryImagesPreview");
            Array.from(galleryImagesPreview.querySelectorAll("img")).forEach(img => {
                // 取得「不含 ?timestamp」的原始路徑
                const fullSrc = img.src;
                const originalSrc = fullSrc.split("?")[0];
                const imgDiv = document.createElement("div");
                imgDiv.className = "col-md-4 mb-2";
                const isNewBlob = originalSrc.startsWith("blob:");
                imgDiv.innerHTML = `
                <div class="card ${isNewBlob ? "new-gallery-card" : ""}">
                    <img src="${originalSrc + "?" + Date.now()}" class="rounded mx-auto d-block" style="width: 200px; height: 200px;">
                    <div class="card-body p-2">
                        <button type="button" class="btn btn-sm btn-danger w-100 
                            ${isNewBlob ? "remove-new-gallery-image" : "delete-gallery-image"}" 
                            data-src="${originalSrc}">
                            <i class="bi bi-trash"></i> 刪除
                        </button>
                    </div>
                </div>`;
                gallerySortable.appendChild(imgDiv);
            });

            enableDragAndDrop(gallerySortable);
            const newGalleryImageInput = manageGalleryModalEl.querySelector("#newGalleryImageInput");
            if (newGalleryImageInput) newGalleryImageInput.value = "";
        }

        function handleNewGalleryUpload(input) {
            const gallerySortable = manageGalleryModalEl.querySelector("#gallerySortable");
            Array.from(input.files).forEach(file => {
                newGalleryFiles.push(file);
                const reader = new FileReader();
                reader.onload = (e) => {
                    const html = `
                    <div class="col-md-4 mb-2">
                        <div class="card new-gallery-card ">
                            <img src="${e.target.result}" class="rounded mx-auto d-block" style="width: 200px; height: 200px;">
                            <div class="card-body p-2">
                                <button type="button" class="btn btn-sm btn-danger w-100 remove-new-gallery-image">
                                    <i class="bi bi-trash"></i> 刪除
                                </button>
                            </div>
                        </div>
                    </div>`;
                    gallerySortable.insertAdjacentHTML("beforeend", html);
                    // Re-enable drag-and-drop for new items
                    const newItem = gallerySortable.lastElementChild;
                    newItem.draggable = true;
                };
                reader.readAsDataURL(file);
            });
            input.value = "";
        }

        function handleDeleteGalleryImage(btn) {
            const srcToDelete = btn.dataset.src;
            const cardToRemove = btn.closest(".col-md-4");
            if (!srcToDelete.startsWith("blob:")) {
                deletedGallerySrcs.push(srcToDelete);
            } else {
                newGalleryFiles = newGalleryFiles.filter(file => URL.createObjectURL(file) !== srcToDelete);
            }
            cardToRemove.remove();
        }

        function updateGalleryOrder() {
            // Placeholder for saving the order of images if needed
            // Currently, the order is handled by the DOM structure and sent to the server in saveGallery
        }

        async function saveGallery() {
            const galleryImagesPreview = addProductModalEl.querySelector("#galleryImagesPreview");
            galleryImagesPreview.innerHTML = "";

            const currentImagesInModal = Array.from(manageGalleryModalEl.querySelectorAll("#gallerySortable .card img"));
            currentImagesInModal.forEach(img => {
                const im = document.createElement("img");
                im.src = img.src;
                im.className = "img-thumbnail";
                im.style.cssText = "width:100px;height:100px;margin:4px";
                galleryImagesPreview.appendChild(im);
            });

            bootstrap.Modal.getInstance(manageGalleryModalEl).hide();
            updateGalleryButtons();
        }

        async function handleEdit(btn) {
            try {
                const productId = btn.dataset.id;
                const { product } = await jsonFetch(`${GET_URL}?action=getProduct&id=${productId}`);
                CURRENT_PRODUCT_ID = product.ProductID;
                fillForm(product);
                addProductModal.show();
            } catch (err) {
                showError("獲取商品詳情失敗", err);
            }
        }

        /* ---------- Add/Update Product ---------- */
        async function handleAddOrUpdate(confirmBtn) {
            const isEdit = !!confirmBtn.dataset.editMode;
            const allData = collectAllFormData(isEdit);

            try {
                const productFd = new FormData();
                Object.entries(allData.product).forEach(([key, value]) => {
                    if (key === "mainImageFile") {
                        if (value) productFd.append("mainImage", value);
                    } else {
                        productFd.append(key, value);
                    }
                });

                const productRes = await jsonFetch(API_BASE, { method: "POST", body: productFd });
                if (!productRes.success) throw new Error(productRes.message);

                const productId = productRes.productId || allData.product.productId;
                if (!productId) throw new Error("無法取得商品ID，請檢查API響應");

                const galleryFd = new FormData();
                galleryFd.append("action", "updateGallery");
                galleryFd.append("productId", productId);
                if (allData.gallery.remainingSrcs.length) {
                    galleryFd.append("remainingSrcs", JSON.stringify(allData.gallery.remainingSrcs));
                }
                if (allData.gallery.deletedSrcs.length) {
                    galleryFd.append("deletedSrcs", JSON.stringify(allData.gallery.deletedSrcs));
                }
                allData.gallery.newFiles.forEach((file) => {
                    galleryFd.append("newGalleryImages[]", file, file.name);
                });

                const galleryRes = await jsonFetch(API_BASE, { method: "POST", body: galleryFd });
                if (!galleryRes.success) throw new Error("圖庫更新失敗：" + galleryRes.message);

                showToast(isEdit ? "商品更新完成！" : "商品新增完成！", "success");
                addProductModal.hide();
                loadProducts();
                resetForm();
            } catch (err) {
                showError("操作失敗", err);
            }
        }

        /* ---------- Filter Products ---------- */
        async function filterProducts() {
            const params = new URLSearchParams();
            const type = document.getElementById("filterType").value;
            const status = document.getElementById("filterStatus").value;
            const search = document.getElementById("searchProduct").value;
            if (type) params.append("type", type);
            if (status !== "") params.append("status", status);
            if (search) params.append("search", search);
            try {
                const { products = [] } = await jsonFetch(`${API_BASE}?action=filter&${params}`);
                renderTable(products);
            } catch (err) {
                showError("篩選失敗", err);
            }
        }

        /* ---------- Size and Color Management ---------- */
        function createColorRow({ OptionID = "", Color = "", Stock = 0 } = {}) {
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

        function createSizeBlock({ Size = "", SizeDescription = "", Price = "", colors = [] } = {}) {
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

            const colorCt = wrap.querySelector(".color-stock-container");
            if (colors.length) {
                colors.forEach(c => colorCt.appendChild(createColorRow(c)));
            } else {
                colorCt.appendChild(createColorRow());
            }

            return wrap;
        }

        function updateSizeButtons() {
            const blocks = addProductModalEl.querySelectorAll(".size-price-block");
            const removeBtns = addProductModalEl.querySelectorAll(".size-price-block .remove-size-btn");
            removeBtns.forEach(btn => (btn.disabled = blocks.length <= 1));
        }

        function updateColorButtons(block) {
            const rows = block.querySelectorAll(".color-row");
            const removeBtns = block.querySelectorAll(".remove-color-btn");
            removeBtns.forEach(btn => (btn.disabled = rows.length <= 1));
        }

        function addSizeRow(data = {}) {
            const newBlock = createSizeBlock(data);
            sizePriceContainer.appendChild(newBlock);
            updateSizeButtons();
            updateColorButtons(newBlock);
        }

        function removeSizeRow(btn) {
            btn.closest(".size-price-block").remove();
            updateSizeButtons();
        }

        function addColorRow(btn) {
            const block = btn.closest(".size-price-block");
            const colorCt = block.querySelector(".color-stock-container");
            colorCt.appendChild(createColorRow());
            updateColorButtons(block);
        }

        function removeColorRow(btn) {
            const row = btn.closest(".color-row");
            const block = btn.closest(".size-price-block");
            row.remove();
            updateColorButtons(block);
        }

        /* ---------- UI Updates ---------- */
        const updateStatusText = chk => addProductModalEl.querySelector("#statusText").textContent = chk.checked ? "上架" : "下架";

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
            galleryImagesPreview.innerHTML = "";

            Array.from(input.files).forEach(f => {
                newGalleryFiles.push(f);
                const img = document.createElement("img");
                img.src = URL.createObjectURL(f);
                img.className = "img-thumbnail";
                img.style.cssText = "width:100px;height:100px;margin:4px";
                galleryImagesPreview.appendChild(img);
            });
            updateGalleryButtons();
        }

        function fillForm(p) {
            resetForm();

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

            const mainImg = addProductModalEl.querySelector("#mainImagePreview");
            if (mainImg) {
                mainImg.src = p.mainImage + "?" + Date.now();
                mainImg.style.display = p.mainImage ? "block" : "none";
            }

            const galleryImagesPreview = addProductModalEl.querySelector("#galleryImagesPreview");
            galleryImagesPreview.innerHTML = "";
            if (p.galleryImage?.length) {
                p.galleryImage.forEach(src => {
                    const im = document.createElement("img");
                    im.src = src + "?" + Date.now();
                    im.className = "img-thumbnail";
                    im.style.cssText = "width:100px;height:100px;margin:4px";
                    galleryImagesPreview.appendChild(im);
                });
            }
            updateGalleryButtons();

            sizePriceContainer.innerHTML = "";
            if (p.sizeOptions?.length) {
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
                addSizeRow();
            }
        }

        function resetForm() {
            addProductModalEl.querySelector("#addProductModalLabel").textContent = "新增商品";
            const confirmBtn = addProductModalEl.querySelector("#confirmAddProduct");
            confirmBtn.textContent = "新增商品";
            delete confirmBtn.dataset.editMode;
            delete confirmBtn.dataset.productId;

            addProductModalEl.querySelector("form").reset();
            addProductModalEl.querySelector("#productActive").checked = false;
            addProductModalEl.querySelector("#statusText").textContent = "下架";

            const mainImg = addProductModalEl.querySelector("#mainImagePreview");
            if (mainImg) {
                mainImg.src = "";
                mainImg.style.display = "none";
            }
            const mainInput = addProductModalEl.querySelector("#mainImageInput");
            if (mainInput) mainInput.value = "";
            const newMainInput = editMainImageModalEl.querySelector("#newMainImageInput");
            if (newMainInput) newMainInput.value = "";

            deletedGallerySrcs = [];
            newGalleryFiles = [];
            const galleryImagesPreview = addProductModalEl.querySelector("#galleryImagesPreview");
            galleryImagesPreview.innerHTML = "";
            const galleryInput = addProductModalEl.querySelector("#galleryImageInput");
            if (galleryInput) galleryInput.value = "";
            const newGalleryInput = manageGalleryModalEl.querySelector("#newGalleryImageInput");
            if (newGalleryInput) newGalleryInput.value = "";
            updateGalleryButtons();

            sizePriceContainer.innerHTML = "";
            addSizeRow();
        }

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

            const currentGalleryImages = Array.from(
                addProductModalEl.querySelectorAll("#galleryImagesPreview img")
            ).map(img => {
                const full = img.src;
                return full.split("?")[0];  // 只保留「?」前面的原始路徑
            });
            data.gallery.remainingSrcs = currentGalleryImages.filter(src => !src.startsWith("blob:"));
            data.gallery.deletedSrcs = deletedGallerySrcs;
            data.gallery.newFiles = newGalleryFiles;

            document.querySelectorAll(".size-price-block").forEach(block => {
                // 把這裡改成：若尺寸沒有填入，就改成 -1
                let size = block.querySelector(".size-input").value;
                size = size === "" ? "-1" : size;

                const desc = block.querySelector(".size-desc-input").value;
                const price = block.querySelector(".price-input").value;
                block.querySelectorAll(".color-row").forEach(row => {
                    const optId = row.querySelector(".color-option-id-input").value;
                    const color = row.querySelector(".color-input").value.trim();
                    const stock = row.querySelector(".stock-input").value || 0;
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
            data.product.sizes = JSON.stringify(data.options);

            return data;
        }

        function updateGalleryButtons() {
            const galleryImagesPreview = addProductModalEl.querySelector("#galleryImagesPreview");
            const addBtn = addProductModalEl.querySelector("#addGalleryImage");
            const manageBtn = addProductModalEl.querySelector("#manageGallery");
            const hasImages = galleryImagesPreview.querySelectorAll("img").length > 0;

            addBtn.style.display = hasImages ? "none" : "";
            manageBtn.style.display = hasImages ? "" : "none";
            if (!hasImages) {
                galleryImagesPreview.innerHTML = '<div class="text-muted">暫無詳細圖片</div>';
            }
        }

        window.addSizeRow = () => addSizeRow();
        window.addColorRow = btn => addColorRow(btn);
    })();
</script>