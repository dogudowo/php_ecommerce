(function ($) {
    const API_BASE = "api/product.php";
    const GET_URL = "models/backend/functions/product/getProducts.php";
    const $body = $("body");
    const $tableBody = $("#productTableBody");
    const $addBtn = $("#addProduct");
    const $modal = $("#addProductModal");
    const $toastContainer = $(
        '<div class="position-fixed bottom-0 end-0 p-3" />'
    ).appendTo("body");

    // 初始化
    $(document).ready(() => {
        loadProducts();
        bindUIEvents();
    });

    // 載入商品列表
    async function loadProducts() {
        try {
            const res = await fetch(`${GET_URL}?action=getProducts`);
            if (!res.ok) throw new Error(res.status);
            const data = await res.json();
            renderTable(data.products || []);
        } catch (e) {
            showError("載入商品失敗", e);
        }
    }

    // 綁定 UI 事件
    function bindUIEvents() {
        $body
            .on("click", ".delete-product", handleDelete)
            .on("click", ".edit-product", handleEdit)
            .on("change", "#productActive", updateStatusText)
            // .on("click", "#addProduct", handleAddOrUpdate) // 改由 modal 的按鈕觸發
            .on("click", "#addSizeRow", addSizeRow)
            .on("click", ".remove-size-btn", removeSizeRow)
            .on("click", ".add-color-btn", addColorRow)
            .on("click", ".remove-color-btn", removeColorRow)
            .on("click", "#uploadMainImage", () => $("#mainImageInput").click())
            .on("change", "#mainImageInput", previewMainImage)
            .on("click", "#addGalleryImage", () =>
                $("#galleryImageInput").click()
            )
            .on("change", "#galleryImageInput", previewGalleryImages);

        $("#filterType, #filterStatus").on("change", filterProducts);
        $("#searchProduct").on("input", filterProducts);

        // 處理模態框的確認按鈕點擊事件
        $("#confirmAddProduct").on("click", handleAddOrUpdate);

        // 當點擊觸發新增商品模態框的按鈕時，重置表單
        $('button[data-bs-target="#addProductModal"]').on("click", function () {
            // 檢查是否是明確的「新增」操作，而不是編輯後的「更新」
            // 可以通過檢查按鈕文字或一個特定的 data-attribute
            // 這裡我們假設觸發新增的按鈕上沒有 edit-mode data
            if (!$(this).data("edit-mode")) {
                resetForm();
            }
        });
    }

    // 渲染商品表格
    function renderTable(products) {
        $tableBody.empty();
        if (!products.length) {
            return $tableBody.append(
                '<tr><td colspan="6" class="text-center">暫無商品資料</td></tr>'
            );
        }
        products.forEach((p) => $tableBody.append(createRow(p)));
    }

    // 建立表格每列
    function createRow(p) {
        return `
        <tr>
          <td>${p.ProductID}</td>
          <td>${p.ProductName}</td>
          <td>${p.Type}</td>
          <td>${p.TotalStock}</td>
          <td><span class="badge bg-${
              p.isActive == 1 ? "success" : "secondary"
          }">${p.isActive == 1 ? "上架" : "下架"}</span></td>
          <td>
            <div class="btn-group">
              <button class="btn btn-sm btn-outline-primary edit-product" data-id="${
                  p.ProductID
              }"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger delete-product" data-id="${
                  p.ProductID
              }"><i class="bi bi-trash"></i></button>
            </div>
          </td>
        </tr>`;
    }

    // 顯示/隱藏狀態文字
    function updateStatusText() {
        $("#statusText").text($(this).is(":checked") ? "上架" : "下架");
    }

    // 刪除商品處理
    async function handleDelete() {
        const id = $(this).data("id");
        if (!id || !confirm("確定要刪除此商品嗎？此操作無法撤銷。")) return;
        try {
            const fd = new FormData();
            fd.append("action", "deleteProduct");
            fd.append("productId", id);
            const res = await fetch(API_BASE, { method: "POST", body: fd });
            if (!res.ok) throw new Error(res.status);
            const result = await res.json();
            if (!result.success) throw new Error(result.message);
            showToast("商品已成功刪除！", "success");
            loadProducts();
        } catch (e) {
            showError("刪除失敗", e);
        }
    }

    // 編輯商品處理
    async function handleEdit() {
        const id = $(this).data("id");
        if (!id) return;
        try {
            const res = await fetch(`${GET_URL}?action=getProduct&id=${id}`);
            if (!res.ok) throw new Error(res.status);
            const { product } = await res.json();
            fillForm(product);
            new bootstrap.Modal($("#addProductModal")).show();
        } catch (e) {
            showError("獲取商品詳情失敗", e);
        }
    }

    // 新增或更新商品
    async function handleAddOrUpdate() {
        // isEdit 的判斷改為檢查 modal 內的按鈕狀態或 form 的狀態
        const isEdit = !!$("#confirmAddProduct").data("edit-mode");
        const fd = collectFormData(isEdit);
        try {
            const res = await fetch(API_BASE, { method: "POST", body: fd });
            if (!res.ok) throw new Error(res.status);
            const result = await res.json();
            if (!result.success) throw new Error(result.message);
            showToast(
                isEdit ? "商品已成功更新！" : "商品已成功新增！",
                "success"
            );
            bootstrap.Modal.getInstance($("#addProductModal")[0]).hide(); // 使用 Bootstrap 5 的方式隱藏 modal
            // resetForm(); // 在成功後重置表單，確保下次打開是乾淨的
            loadProducts();
        } catch (e) {
            showError("操作失敗", e);
        }
    }

    // 篩選商品
    async function filterProducts() {
        const params = new URLSearchParams();
        const type = $("#filterType").val();
        const status = $("#filterStatus").val();
        const search = $("#searchProduct").val();
        if (type) params.append("type", type);
        if (status) params.append("status", status);
        if (search) params.append("search", search);
        try {
            const res = await fetch(`${API_BASE}?action=filter&${params}`);
            if (!res.ok) throw new Error(res.status);
            const data = await res.json();
            renderTable(data.products || []);
        } catch (e) {
            showError("篩選失敗", e);
        }
    }

    // 填入表單資料
    function fillForm(p) {
        resetForm(); // 編輯前先重置表單，確保清空舊狀態
        $("#addProductModalLabel").text("編輯商品");
        $("#confirmAddProduct") // 更新 modal 內的確認按鈕
            .text("更新商品")
            .data({ "edit-mode": true, "product-id": p.ProductID });
        $("#productName").val(p.ProductName);
        $("#productType").val(p.Type);
        $("#productIntro").val(p.Introdution);
        $("#productActive").prop("checked", p.isActive == 1);
        $("#statusText").text(p.isActive == 1 ? "上架" : "下架");
        if (p.mainImage) {
            $("#mainImagePreview").attr("src", p.mainImage).show();
            $("#uploadMainImage").hide();
        } else {
            $("#mainImagePreview").hide();
            $("#uploadMainImage").show();
        }

        // 處理畫廊圖片
        $("#galleryImagesPreview").empty();
        if (p.galleryImage && p.galleryImage.length > 0) {
            p.galleryImage.forEach((img) => {
                $("<img>")
                    .attr("src", img)
                    .addClass("img-thumbnail")
                    .css({ width: "100px", height: "100px", margin: "4px" })
                    .appendTo("#galleryImagesPreview");
            });
        } else {
            $("#galleryImagesPreview").html(
                '<div class="text-muted">暫無詳細圖片</div>'
            );
        }
        // 清空並填尺寸與顏色
        $("#sizePriceContainer").empty();
        if (p.sizeOptions && p.sizeOptions.length > 0) {
            const groupedSizes = p.sizeOptions.reduce((acc, opt) => {
                const key = `${opt.Size}-${opt.SizeDescription}-${opt.Price}`;
                if (!acc[key]) {
                    acc[key] = {
                        Size: opt.Size,
                        SizeDescription: opt.SizeDescription,
                        Price: opt.Price,
                        colors: [],
                    };
                }
                acc[key].colors.push({ Color: opt.Color, Stock: opt.Stock });
                return acc;
            }, {});

            Object.values(groupedSizes).forEach((sizeGroup) => {
                let colorsHtml = "";
                sizeGroup.colors.forEach((colorOpt, index) => {
                    // 第一個顏色行顯示標籤，後續的不顯示
                    const showLabels = index === 0;
                    colorsHtml += createColorRowHtml(
                        showLabels,
                        colorOpt.Color,
                        colorOpt.Stock,
                        sizeGroup.colors.length === 1 && showLabels
                    );
                });

                const sizePriceHtml = `
                    <div class="card mb-3 size-price-block">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">尺寸</label>
                                    <input type="number" class="form-control size-input" placeholder="尺寸" value="${sizeGroup.Size}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">尺寸描述</label>
                                    <input type="text" class="form-control size-desc-input" placeholder="尺寸描述" value="${sizeGroup.SizeDescription}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">價格</label>
                                    <input type="number" class="form-control price-input" placeholder="價格" value="${sizeGroup.Price}">
                                </div>
                                <div class="col-md-3 text-end">
                                    <button type="button" class="btn btn-outline-danger remove-size-btn"><i class="bi bi-trash"></i> 刪除</button>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary add-color-btn"><i class="bi bi-plus"></i> 添加顏色</button>
                                <div class="color-stock-container mt-2">
                                    ${colorsHtml}
                                </div>
                            </div>
                        </div>
                    </div>`;
                $("#sizePriceContainer").append(sizePriceHtml);
            });
        } else {
            addSizeRow(); // 如果沒有 sizeOptions，則添加一個空的
        }
    }

    // 重置表單
    function resetForm() {
        $("#addProductModalLabel").text("新增商品");
        $("#confirmAddProduct") // 更新 modal 內的確認按鈕
            .text("新增商品")
            .removeData("edit-mode product-id");
        $("#addProductForm")[0].reset();
        $("#productActive").prop("checked", false); // 明確設定未勾選
        $("#statusText").text("下架");
        $("#mainImagePreview").attr("src", "#").hide(); // 清空預覽圖片並隱藏
        $("#mainImageInput").val(""); // 清空檔案選擇
        $("#uploadMainImage").show();
        $("#galleryImagesPreview")
            .empty()
            .html('<div class="text-muted">暫無詳細圖片</div>'); // 清空畫廊並顯示提示
        $("#galleryImageInput").val(""); // 清空檔案選擇
        $("#sizePriceContainer").empty();
        addSizeRow(); // 添加一個預設的空尺寸行
    }

    // 收集表單資料
    function collectFormData(isEdit) {
        const fd = new FormData();
        fd.append("action", isEdit ? "updateProduct" : "addProduct");
        if (isEdit)
            fd.append("productId", $("#confirmAddProduct").data("product-id")); // 從 modal 確認按鈕獲取 product-id
        fd.append("productName", $("#productName").val());
        fd.append("productType", $("#productType").val());
        fd.append("productIntro", $("#productIntro").val());
        fd.append(
            "productActive",
            $("#productActive").is(":checked") ? "1" : "0"
        );
        const mainFile = $("#mainImageInput")[0].files[0];
        if (mainFile) fd.append("mainImage", mainFile);
        Array.from($("#galleryImageInput")[0].files).forEach((file, i) => {
            fd.append("galleryImages[]", file);
            fd.append("galleryOrders[]", i + 2);
        });
        const productOptions = [];
        $(".size-price-block").each(function () {
            const $sizeBlock = $(this);
            const size = $sizeBlock.find(".size-input").val();
            const sizeDescription = $sizeBlock.find(".size-desc-input").val();
            const price = $sizeBlock.find(".price-input").val();

            $sizeBlock.find(".color-row").each(function () {
                const $colorBlock = $(this);
                const color = $colorBlock.find(".color-input").val().trim();
                const stock = $colorBlock.find(".stock-input").val() || 0;

                if (size && color) {
                    // 確保尺寸和顏色都有值
                    productOptions.push({
                        Size: size,
                        SizeDescription: sizeDescription,
                        Price: price,
                        Color: color,
                        Stock: stock,
                    });
                }
            });
        });
        fd.append("sizes", JSON.stringify(productOptions)); // 後端接收的key仍為 "sizes"
        return fd;
    }

    // 預覽主圖
    function previewMainImage(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
            $("#mainImagePreview").attr("src", ev.target.result).show();
            $("#uploadMainImage").hide();
        };
        reader.readAsDataURL(file);
    }

    // 預覽畫廊圖
    function previewGalleryImages(e) {
        const files = e.target.files;
        const $pv = $("#galleryImagesPreview").empty();
        Array.from(files).forEach((f, i) => {
            const reader = new FileReader();
            reader.onload = (ev) => {
                $("<img>")
                    .attr("src", ev.target.result)
                    .addClass("img-thumbnail")
                    .css({ width: "100px", height: "100px", margin: "4px" })
                    .attr("draggable", true)
                    .data("index", i)
                    .appendTo($pv);
            };
            reader.readAsDataURL(f);
        });
        $pv.sortable({
            items: "img",
            stop: (evt, ui) => {
                const order = $pv
                    .find("img")
                    .map((_, img) => $(img).data("index"))
                    .get();
                const dt = new DataTransfer();
                order.forEach((idx) => dt.items.add(files[idx]));
                e.target.files = dt.files;
            },
        });
    }

    // 動態新增尺寸區塊
    function addSizeRow() {
        const tpl = `
            <div class="card mb-3 size-price-block">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">尺寸</label>
                            <input type="number" class="form-control size-input" placeholder="尺寸">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">尺寸描述</label>
                            <input type="text" class="form-control size-desc-input" placeholder="尺寸描述">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">價格</label>
                            <input type="number" class="form-control price-input" placeholder="價格">
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="button" class="btn btn-outline-danger remove-size-btn"><i class="bi bi-trash"></i> 刪除</button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-primary add-color-btn"><i class="bi bi-plus"></i> 添加顏色</button>
                            <div class="color-stock-container mt-3">
                                ${createColorRowHtml(true, "", "", true)} 
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        $("#sizePriceContainer").append(tpl);
    }

    // 建立顏色行 HTML
    function createColorRowHtml(
        showLabels = true,
        color = "",
        stock = "",
        disableRemove = false
    ) {
        const colorLabelHtml = showLabels
            ? '<label class="form-label">顏色</label>'
            : "";
        const stockLabelHtml = showLabels
            ? '<label class="form-label">庫存</label>'
            : "";
        return `
            <div class="mb-2 color-row">
                <div class="row g-2">
                    <div class="col-md-6">
                        ${colorLabelHtml}
                        <input type="text" class="form-control color-input" placeholder="顏色描述" value="${color}">
                    </div>
                    <div class="col-md-4">
                        ${stockLabelHtml}
                        <input type="number" class="form-control stock-input" placeholder="庫存" value="${stock}">
                    </div>
                    <div class="col-md-2" style="align-self: end;">
                        <button type="button" class="btn btn-sm w-100 btn-outline-danger remove-color-btn" ${
                            disableRemove ? "disabled" : ""
                        }><i class="bi bi-trash"></i> 刪除</button>
                    </div>
                </div>
            </div>`;
    }

    // 移除尺寸區塊
    function removeSizeRow() {
        if ($(".size-price-block").length > 1)
            $(this).closest(".size-price-block").remove();
    }

    // 新增顏色行
    function addColorRow() {
        const $ct = $(this).siblings(".color-stock-container");
        // 新增的顏色行不顯示標籤，且可以刪除 (disableRemove = false)
        $ct.append(createColorRowHtml(false, "", "", false));
        // 更新所有該尺寸區塊內的刪除按鈕狀態
        const $colorRows = $ct.find(".color-row");
        $colorRows
            .find(".remove-color-btn")
            .prop("disabled", $colorRows.length <= 1);
    }

    // 移除顏色行
    function removeColorRow() {
        const $ct = $(this).closest(".color-stock-container");
        if ($ct.children().length > 1) $(this).closest(".color-row").remove();
        $ct.find(".remove-color-btn").prop(
            "disabled",
            $ct.children().length <= 1
        );
    }

    // 顯示吐司
    function showToast(msg, type) {
        const $toast = $(
            `<div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body"><i class="bi ${
                type === "success"
                    ? "bi-check-circle"
                    : "bi-exclamation-triangle"
            } me-2"></i>${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`
        );
        $toast.appendTo($toastContainer);
        new bootstrap.Toast($toast[0], { delay: 3000 }).show();
    }

    // 顯示錯誤
    function showError(title, err) {
        console.error(title, err);
        showToast(`${title}：${err.message || err}`, "danger");
    }
})(jQuery);
