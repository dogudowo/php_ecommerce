<?php
require_once __DIR__ . '/../../api/error_handler.php';
// admin/products.php

// 防止直接訪問此文件
defined('INCLUDED_FROM_ADMIN') || exit(header('Location: ../../login.php'));

require_once __DIR__ . '/../../api/db.php';
require_once __DIR__ . '/../../api/product.php';

// 處理 GET 參數
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 從資料庫獲取商品類型
$pdo = db_connect();
$stmt = $pdo->query("SELECT DISTINCT Type FROM Product");
$productTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 商品資料將由 product.js 中的 fetch 請求獲取
$products = [];
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
                        <!--  ...  -->
                    </tbody>
                </table>
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
                                    <div class="border rounded p-2 text-center" style="height: 150px;">
                                        <img id="mainImagePreview" src="" class="img-fluid h-100"
                                            style="display: none;">
                                        <button type="button" class="btn btn-outline-primary mt-3" id="uploadMainImage">
                                            <i class="bi bi-upload"></i> 上傳圖片
                                        </button>
                                        <input type="file" id="mainImageInput" accept="image/jpeg,image/jpg,image/png"
                                            style="display: none;">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">詳細圖片</label>
                                    <div id="galleryImagesContainer" class="border rounded p-2">
                                        <div class="d-flex flex-wrap gap-2 align-items-center"
                                            id="galleryImagesPreview">
                                        </div>
                                        <button type="button" class="btn btn-outline-primary mt-2" id="addGalleryImage">
                                            <i class="bi bi-plus"></i> 添加圖片
                                        </button>
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
                                                                    <input type="number"
                                                                        class="form-control stock-input"
                                                                        placeholder="庫存">
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
        const GET_URL = "models/backend/functions/product/getProducts.php";
        const TYPE_OPTIONS = {
            aluminum: "鋁框款",
            zipper: "拉鍊款",
            accessories: "行李箱配件",
            travel: "旅遊周邊",
            featured: "精選商品",
        };

        /* ----------  DOM 快取 ---------- */
        const body = document.body;
        const tableBody = document.getElementById("productTableBody");
        const modalEl = document.getElementById("addProductModal");
        const modal = new bootstrap.Modal(modalEl);

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
            if (!res.ok) throw new Error(res.status);
            return res.json();
        };

        /* ----------  初始化 ---------- */
        document.addEventListener("DOMContentLoaded", () => {
            populateTypeSelects();
            loadProducts();
            bindUIEvents();
        });

        /* ----------  下拉選單 ---------- */
        function populateTypeSelects() {
            const selects = [
                document.getElementById("productType"),
                document.getElementById("filterType"),
            ].filter(Boolean);

            selects.forEach(sel => {
                const withAll = sel.id === "filterType";
                sel.innerHTML = "";
                if (withAll) sel.appendChild(new Option("全部類型", ""));
                Object.entries(TYPE_OPTIONS).forEach(([v, t]) => sel.appendChild(new Option(t, v)));
            });
        }

        /* ----------  讀取產品 ---------- */
        async function loadProducts() {
            try {
                const { products = [] } = await jsonFetch(`${GET_URL}?action=getProducts`);
                renderTable(products);
            } catch (err) { showError("載入商品失敗", err); }
        }

        /* ----------  事件繫結 ---------- */
        function bindUIEvents() {
            /* 委派點擊 */
            body.addEventListener("click", e => {
                const btn = e.target.closest("button");
                if (!btn) return;

                if (btn.classList.contains("delete-product")) return handleDelete(btn);
                if (btn.classList.contains("edit-product")) return handleEdit(btn);
                if (btn.id === "addSizeRow") return addSizeRow();
                if (btn.classList.contains("remove-size-btn")) return removeSizeRow(btn);
                if (btn.classList.contains("add-color-btn")) return addColorRow(btn);
                if (btn.classList.contains("remove-color-btn")) return removeColorRow(btn);
                if (btn.id === "uploadMainImage") return modalEl.querySelector("#mainImageInput").click();
                if (btn.id === "addGalleryImage") return modalEl.querySelector("#galleryImageInput").click();
            });

            /* 其他表單事件 */
            body.addEventListener("change", e => {
                const t = e.target;
                if (t.id === "productActive") return updateStatusText(t);
                if (t.id === "mainImageInput") return previewMainImage(t);
                if (t.id === "galleryImageInput") return previewGalleryImages(t);
                if (t.id === "filterType" || t.id === "filterStatus") return filterProducts();
            });

            document.getElementById("searchProduct")?.addEventListener("input", filterProducts);
            document.getElementById("confirmAddProduct").addEventListener("click", handleAddOrUpdate);

            /* 開啟 modal 時若非編輯模式則重置表單 */
            document.querySelectorAll('[data-bs-target="#addProductModal"]').forEach(btn =>
                btn.addEventListener("click", () => { if (!btn.dataset.editMode) resetForm(); })
            );
        }

        /* ----------  表格呈現 ---------- */
        function renderTable(products) {
            tableBody.innerHTML = products.length
                ? products.map(createRow).join("")
                : '<tr><td colspan="6" class="text-center">暫無商品資料</td></tr>';
        }
        const getCName = t => TYPE_OPTIONS[t?.trim().toLowerCase()] || t || "";
        const createRow = p => `
        <tr>
            <td>${p.ProductID}</td>
            <td title="${p.ProductName}">${p.ProductName}</td>
            <td>${getCName(p.Type)}</td>
            <td>${p.TotalStock}</td>
            <td><span class="badge bg-${p.isActive == 1 ? "success" : "secondary"}">${p.isActive == 1 ? "上架" : "下架"}</span></td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary edit-product" data-id="${p.ProductID}"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger delete-product" data-id="${p.ProductID}"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>`;

        /* ----------  刪除 / 編輯 ---------- */
        async function handleDelete(btn) {
            const id = btn.dataset.id;
            if (!id || !confirm("確定要刪除此商品嗎？此操作無法撤銷。")) return;
            try {
                const fd = new FormData();
                fd.append("action", "deleteProduct");
                fd.append("productId", id);
                const res = await jsonFetch(API_BASE, { method: "POST", body: fd });
                if (!res.success) throw new Error(res.message);
                showToast("商品已成功刪除！", "success");
                loadProducts();
            } catch (err) { showError("刪除失敗", err); }
        }

        async function handleEdit(btn) {
            try {
                const { product } = await jsonFetch(`${GET_URL}?action=getProduct&id=${btn.dataset.id}`);
                fillForm(product);
                modal.show();
            } catch (err) { showError("獲取商品詳情失敗", err); }
        }

        /* ----------  新增 / 更新 ---------- */
        async function handleAddOrUpdate() {
            const isEdit = !!this.dataset.editMode;
            const fd = collectFormData(isEdit);

            try {
                const res = await jsonFetch(API_BASE, { method: "POST", body: fd });
                if (!res.success) throw new Error(res.message);
                showToast(isEdit ? "商品已成功更新！" : "商品已成功新增！", "success");
                modal.hide();
                loadProducts();
            } catch (err) { showError("操作失敗", err); }
        }

        /* ----------  篩選 ---------- */
        async function filterProducts() {
            const params = new URLSearchParams();
            const type = document.getElementById("filterType").value;
            const status = document.getElementById("filterStatus").value;
            const search = document.getElementById("searchProduct").value;
            if (type) params.append("type", type);
            if (status) params.append("status", status);
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
        function createColorRow({ color = "", stock = 0 } = {}) {
            const div = document.createElement("div");
            div.className = "mb-2 color-row";
            div.innerHTML = `
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" class="form-control color-input" placeholder="顏色描述" value="${color}">
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control stock-input" placeholder="庫存" value="${stock}">
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
        function createSizeBlock({ size = "", desc = "", price = "", colors = [] } = {}) {
            const wrap = document.createElement("div");
            wrap.className = "card mb-3 size-price-block";
            wrap.innerHTML = `
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">尺寸</label>
                        <input type="number" class="form-control size-input" value="${size}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">尺寸描述</label>
                        <input type="text" class="form-control size-desc-input" value="${desc}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">價格</label>
                        <input type="number" class="form-control price-input" value="${price}">
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
            /* 填入顏色列 */
            const colorCt = wrap.querySelector(".color-stock-container");
            if (colors.length) {
                colors.forEach(c => colorCt.appendChild(createColorRow(c)));
            } else {
                colorCt.appendChild(createColorRow()); // 至少一列
            }
            return wrap;
        }

        /* --- 尺寸 / 顏色增刪 --- */
        function addSizeRow(data = {}) {
            modalEl.querySelector("#sizePriceContainer").appendChild(createSizeBlock(data));
        }
        function removeSizeRow(btn) {
            btn.closest(".size-price-block")?.remove();
        }

        function addColorRow(btn) {
            const colorCt = btn.closest(".size-price-block")?.querySelector(".color-stock-container");
            if (colorCt) colorCt.appendChild(createColorRow());
        }
        function removeColorRow(btn) {
            const row = btn.closest(".color-row");
            const colorCt = row?.parentElement;
            row?.remove();
            /* 若刪到 0 列，補一列空的 */
            if (colorCt && !colorCt.querySelector(".color-row")) colorCt.appendChild(createColorRow());
        }

        /* --- 其他 UI --- */
        const updateStatusText = chk => modalEl.querySelector("#statusText").textContent = chk.checked ? "上架" : "下架";

        /* 圖片預覽 */
        function previewMainImage(input) {
            const file = input.files[0];
            if (!file) return;
            const img = modalEl.querySelector("#mainImagePreview");
            img.src = URL.createObjectURL(file);
            img.style.display = "block";
            modalEl.querySelector("#uploadMainImage").style.display = "none";
        }
        function previewGalleryImages(input) {
            const ct = modalEl.querySelector("#galleryImagesPreview");
            ct.innerHTML = "";
            [...input.files].forEach(f => {
                const img = document.createElement("img");
                img.src = URL.createObjectURL(f);
                img.className = "img-thumbnail";
                img.style.cssText = "width:100px;height:100px;margin:4px";
                ct.appendChild(img);
            });
        }

        /* --- 填入/重置表單 --- */
        function fillForm(p) {
            resetForm();

            modalEl.querySelector("#addProductModalLabel").textContent = "編輯商品";
            const confirmBtn = modalEl.querySelector("#confirmAddProduct");
            confirmBtn.textContent = "更新商品";
            confirmBtn.dataset.editMode = "true";
            confirmBtn.dataset.productId = p.ProductID;

            modalEl.querySelector("#productName").value = p.ProductName;
            modalEl.querySelector("#productType").value = p.Type.toString().toLowerCase();
            modalEl.querySelector("#productIntro").value = p.Introdution;

            const activeChk = modalEl.querySelector("#productActive");
            activeChk.checked = p.isActive == 1;
            updateStatusText(activeChk);

            /* 主圖 */
            if (p.mainImage) {
                const img = modalEl.querySelector("#mainImagePreview");
                img.src = p.mainImage;
                img.style.display = "block";
                modalEl.querySelector("#uploadMainImage").style.display = "none";
            }

            /* 詳細圖片 */
            const gp = modalEl.querySelector("#galleryImagesPreview");
            gp.innerHTML = "";
            if (p.galleryImage?.length) {
                p.galleryImage.forEach(src => {
                    const im = document.createElement("img");
                    im.src = src;
                    im.className = "img-thumbnail";
                    im.style.cssText = "width:100px;height:100px;margin:4px";
                    gp.appendChild(im);
                });
            } else {
                gp.innerHTML = '<div class="text-muted">暫無詳細圖片</div>';
            }

            /* 尺寸/顏色 */
            const sizeCt = modalEl.querySelector("#sizePriceContainer");
            sizeCt.innerHTML = "";
            if (p.sizeOptions?.length) {
                /* 先依 size/desc/price 分組 */
                const grouped = p.sizeOptions.reduce((acc, o) => {
                    const key = `${o.Size}-${o.SizeDescription}-${o.Price}`;
                    (acc[key] ||= { Size: o.Size, SizeDescription: o.SizeDescription, Price: o.Price, colors: [] })
                        .colors.push({ color: o.Color, stock: o.Stock });
                    return acc;
                }, {});
                Object.values(grouped).forEach(g => addSizeRow({
                    size: g.Size,
                    desc: g.SizeDescription,
                    price: g.Price,
                    colors: g.colors.map(c => ({ color: c.color, stock: c.stock }))
                }));
            } else {
                addSizeRow();
            }
        }

        function resetForm() {
            modalEl.querySelector("#addProductModalLabel").textContent = "新增商品";
            const confirmBtn = modalEl.querySelector("#confirmAddProduct");
            confirmBtn.textContent = "新增商品";
            delete confirmBtn.dataset.editMode;
            delete confirmBtn.dataset.productId;

            modalEl.querySelector("form").reset();
            modalEl.querySelector("#statusText").textContent = "下架";

            modalEl.querySelector("#mainImagePreview").style.display = "none";
            modalEl.querySelector("#uploadMainImage").style.display = "";
            modalEl.querySelector("#galleryImagesPreview").innerHTML = '<div class="text-muted">暫無詳細圖片</div>';

            modalEl.querySelector("#sizePriceContainer").innerHTML = "";
            addSizeRow();
        }

        /* --- 收集表單資料 --- */
        function collectFormData(isEdit) {
            const fd = new FormData();
            fd.append("action", isEdit ? "updateProduct" : "addProduct");
            if (isEdit) fd.append("productId", modalEl.querySelector("#confirmAddProduct").dataset.productId);

            fd.append("productName", modalEl.querySelector("#productName").value);
            fd.append("productType", modalEl.querySelector("#productType").value);
            fd.append("productIntro", modalEl.querySelector("#productIntro").value);
            fd.append("productActive", modalEl.querySelector("#productActive").checked ? "1" : "0");

            const mainFile = modalEl.querySelector("#mainImageInput").files[0];
            if (mainFile) fd.append("mainImage", mainFile);

            [...modalEl.querySelector("#galleryImageInput").files].forEach((f, i) => {
                fd.append("galleryImages[]", f);
                fd.append("galleryOrders[]", i + 2);
            });

            /* 尺寸/顏色 */
            const options = [];
            modalEl.querySelectorAll(".size-price-block").forEach(block => {
                const size = block.querySelector(".size-input").value;
                const desc = block.querySelector(".size-desc-input").value;
                const price = block.querySelector(".price-input").value;
                block.querySelectorAll(".color-row").forEach(row => {
                    const color = row.querySelector(".color-input").value.trim();
                    const stock = row.querySelector(".stock-input").value || 0;
                    if (size && color) options.push({ Size: size, SizeDescription: desc, Price: price, Color: color, Stock: stock });
                });
            });
            fd.append("sizes", JSON.stringify(options));
            return fd;
        }

        /* ----------  Toast ---------- */
        function showToast(msg, type = "success") {
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
        }
        const showError = (title, err) => { console.error(title, err); showToast(`${title}：${err.message || err}`, "danger"); };

        /* ----------  對外 (for inline) ---------- */
        window.addSizeRow = () => addSizeRow();
        window.addColorRow = btn => addColorRow(btn);
    })();
</script>