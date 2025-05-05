$(document).ready(function () {
    // 刪除商品事件處理
    $(document).on("click", ".delete-product", async function () {
        const productId = $(this).data("id");
        if (!productId) return;

        // 使用 Bootstrap 的 modal 來確認刪除
        const confirmModal = new bootstrap.Modal(
            $(
                "<div class='modal fade' tabindex='-1'>" +
                    "<div class='modal-dialog modal-dialog-centered'>" +
                    "<div class='modal-content'>" +
                    "<div class='modal-header bg-danger text-white'>" +
                    "<h5 class='modal-title'>確認刪除</h5>" +
                    "<button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>" +
                    "</div>" +
                    "<div class='modal-body'>" +
                    "<p>確定要刪除此商品嗎？此操作無法撤銷。</p>" +
                    "</div>" +
                    "<div class='modal-footer'>" +
                    "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>取消</button>" +
                    "<button type='button' class='btn btn-danger' id='confirmDelete'>刪除</button>" +
                    "</div>" +
                    "</div>" +
                    "</div>" +
                    "</div>"
            ).appendTo("body")[0]
        );

        confirmModal.show();

        $("#confirmDelete").on("click", async function () {
            try {
                const result = await deleteProduct(productId);

                if (result.success) {
                    // 顯示成功訊息
                    const toastHtml = `
                        <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                          <div class="d-flex">
                            <div class="toast-body"><i class="bi bi-check-circle me-2"></i>商品已成功刪除！</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                          </div>
                        </div>`;
                    const $container = $(
                        '<div class="position-fixed bottom-0 end-0 p-3">'
                    )
                        .append(toastHtml)
                        .appendTo("body");
                    const toast = new bootstrap.Toast(
                        $container.find(".toast")[0],
                        { delay: 3000 }
                    );
                    toast.show();
                    toast._element.addEventListener("hidden.bs.toast", () =>
                        $container.remove()
                    );

                    // 刷新商品列表
                    filterProducts();
                } else {
                    throw new Error(result.message);
                }
            } catch (err) {
                console.error("刪除商品錯誤:", err);

                // 顯示錯誤訊息
                const toastHtml = `
                    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="d-flex">
                        <div class="toast-body"><i class="bi bi-exclamation-triangle me-2"></i>刪除失敗：${
                            err.message || "未知錯誤"
                        }</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                      </div>
                    </div>`;
                const $container = $(
                    '<div class="position-fixed bottom-0 end-0 p-3">'
                )
                    .append(toastHtml)
                    .appendTo("body");
                const toast = new bootstrap.Toast(
                    $container.find(".toast")[0],
                    { delay: 5000 }
                );
                toast.show();
                toast._element.addEventListener("hidden.bs.toast", () =>
                    $container.remove()
                );
            } finally {
                confirmModal.hide();
                // 移除 modal 元素
                setTimeout(() => {
                    $(".modal").last().remove();
                }, 300);
            }
        });
    });

    // 新增/更新商品
    $("#addProduct").on("click", async function () {
        try {
            const formData = new FormData();
            const isEditMode = $(this).data("edit-mode");
            const productId = $(this).data("product-id");

            // 設置操作類型
            formData.append(
                "action",
                isEditMode ? "updateProduct" : "addProduct"
            );
            if (isEditMode) {
                formData.append("productId", productId);
            }

            // 基本信息
            formData.append("productName", $("#productName").val());
            formData.append("productType", $("#productType").val());
            formData.append("productIntro", $("#productIntro").val());
            formData.append(
                "productActive",
                $("#productActive").is(":checked") ? "1" : "0"
            );

            // 主圖片
            const mainFile = $("#mainImageInput")[0].files[0];
            if (mainFile) formData.append("mainImage", mainFile);

            // 詳細圖片
            const galleryFiles = $("#galleryImageInput")[0].files;
            for (let i = 0; i < galleryFiles.length; i++) {
                formData.append("galleryImages[]", galleryFiles[i]);
                // 確保圖片順序正確傳遞
                formData.append(`galleryOrders[]`, i + 2); // 主圖片order=1，詳細圖片從2開始
            }

            // 尺寸與顏色
            const sizes = [];
            $(".size-price-block").each(function () {
                const $blk = $(this);
                const sizeData = {
                    size: $blk.find(".size-input").val(),
                    sizeDescription: $blk.find(".size-desc-input").val(),
                    price: $blk.find(".price-input").val(),
                    colors: [],
                };
                $blk.find(".color-stock-container .color-row").each(
                    function () {
                        const $row = $(this);
                        const colorVal = $row.find(".color-input").val();
                        if (colorVal && colorVal.trim()) {
                            // 分割顏色描述，確保多個顏色分開顯示
                            const colors = colorVal
                                .split(",")
                                .map((c) => c.trim())
                                .filter((c) => c); // 過濾掉空值
                            colors.forEach((c) => {
                                sizeData.colors.push({
                                    Color: c,
                                    Stock: $row.find(".stock-input").val() || 0, // 預設庫存為0
                                });
                            });
                            // 在編輯模式下，將顏色值設置回輸入框
                            if (isEditMode) {
                                $row.find(".color-input").val(
                                    colorVal
                                        .split(",")
                                        .map((c) => c.trim())
                                        .join(", ")
                                );
                            }
                        }
                    }
                );
                sizes.push(sizeData);
            });
            // 確保尺寸和顏色選項正確傳遞
            formData.append("sizes", JSON.stringify(sizes));

            let result;
            if (isEditMode) {
                // 使用更新商品函數
                result = await updateProduct(formData);
            } else {
                // 使用添加商品函數
                const res = await fetch(
                    "/models/backend/functions/product/addProductHandler.php",
                    {
                        method: "POST",
                        body: formData,
                    }
                );
                result = await res.json();
            }

            if (result.success) {
                // 顯示成功訊息
                const toastHtml = `
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="d-flex">
                        <div class="toast-body"><i class="bi bi-check-circle me-2"></i>${
                            isEditMode ? "商品已成功更新！" : "商品已成功新增！"
                        }</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                      </div>
                    </div>`;
                const $container = $(
                    '<div class="position-fixed bottom-0 end-0 p-3">'
                )
                    .append(toastHtml)
                    .appendTo("body");
                const toast = new bootstrap.Toast(
                    $container.find(".toast")[0],
                    { delay: 3000 }
                );
                toast.show();
                toast._element.addEventListener("hidden.bs.toast", () =>
                    $container.remove()
                );

                // 關閉 Modal 並重置
                $("#addProductModal").modal("hide");
                resetProductForm();

                // 刷新商品列表
                filterProducts();
            } else {
                throw new Error(
                    result.message ||
                        (isEditMode ? "更新商品失敗" : "新增商品失敗")
                );
            }
        } catch (err) {
            const isEditMode = $(this).data("edit-mode");
            console.error(isEditMode ? "更新商品錯誤:" : "新增商品錯誤:", err);

            // 顯示錯誤訊息
            const toastHtml = `
                <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                  <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-exclamation-triangle me-2"></i>${
                        err.message || "操作失敗"
                    }</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                  </div>
                </div>`;
            const $container = $(
                '<div class="position-fixed bottom-0 end-0 p-3">'
            )
                .append(toastHtml)
                .appendTo("body");
            const toast = new bootstrap.Toast($container.find(".toast")[0], {
                delay: 5000,
            });
            toast.show();
            toast._element.addEventListener("hidden.bs.toast", () =>
                $container.remove()
            );
        }
    });

    // 重置商品表單
    function resetProductForm() {
        // 重置 Modal 標題和按鈕
        $("#addProductModalLabel").text("新增商品");
        $("#addProduct")
            .text("新增商品")
            .removeData("edit-mode")
            .removeData("product-id");

        // 清空表單數據
        $("#productName").val("");
        $("#productType").val("");
        $("#productIntro").val("");
        $("#productActive").prop("checked", false);
        $("#statusText").text("下架");

        // 重置圖片
        $("#mainImagePreview").attr("src", "").hide();
        $("#uploadMainImage").show();
        $("#mainImageInput").val("");
        $("#galleryImagesPreview").empty();
        $("#galleryImageInput").val("");

        // 重置尺寸和顏色選項
        $("#sizePriceContainer").empty();
        $("#addSizeRow").trigger("click");
    }

    // Modal 關閉時重置表單
    $("#addProductModal").on("hidden.bs.modal", function () {
        resetProductForm();
    });

    // 主圖片預覽
    $("#uploadMainImage").on("click", () => $("#mainImageInput").click());
    $("#mainImageInput").on("change", function (e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (ev) => {
            $("#mainImagePreview").attr("src", ev.target.result).show();
            $("#uploadMainImage").hide();
        };
        reader.readAsDataURL(file);
    });

    // 詳細圖片選擇
    $("#addGalleryImage").on("click", () => $("#galleryImageInput").click());
    $("#galleryImageInput").on("change", function (e) {
        const files = e.target.files;
        const $preview = $("#galleryImagesPreview").empty();
        for (let i = 0; i < files.length; i++) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                const $img = $("<img>")
                    .attr("src", ev.target.result)
                    .addClass("img-thumbnail")
                    .css({ width: "100px", height: "100px", margin: "4px" })
                    .attr("draggable", true)
                    .data("index", i);
                $preview.append($img);
            };
            reader.readAsDataURL(files[i]);
        }
        // 初始化拖曳排序
        $preview.sortable({
            items: "img",
            stop: function (event, ui) {
                // 更新圖片順序
                const newOrder = [];
                $preview.find("img").each(function (index) {
                    newOrder.push($(this).data("index"));
                });
                // 重新排序files數組
                const sortedFiles = [];
                newOrder.forEach((index) => {
                    sortedFiles.push(files[index]);
                });
                // 更新files數組
                const dataTransfer = new DataTransfer();
                sortedFiles.forEach((file) => dataTransfer.items.add(file));
                e.target.files = dataTransfer.files;
            },
        });
    });

    // 動態新增／刪除尺寸區塊
    $("#addSizeRow").on("click", function () {
        const $tpl = $(`
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
                    <button type="button" class="btn btn-outline-danger remove-size-btn">
                      <i class="bi bi-trash"></i> 刪除
                    </button>
                  </div>
                </div>
                <div class="mt-3">
                  <button type="button" class="btn btn-outline-primary add-color-btn">
                    <i class="bi bi-plus"></i> 添加顏色
                  </button>
                  <div class="color-stock-container mt-2">
                    <div class="mb-2 color-row">
                      <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" class="form-control color-input" placeholder="顏色描述">
                        </div>
                        <div class="col-md-4">
                            <input type="number" class="form-control stock-input" placeholder="庫存">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm w-100 btn-outline-danger remove-color-btn" disabled>
                            <i class="bi bi-trash"></i> 刪除
                            </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        `);
        $("#sizePriceContainer").append($tpl);
    });

    // 事件代理：刪除尺寸
    $(document).on("click", ".remove-size-btn", function () {
        if ($(".size-price-block").length > 1) {
            $(this).closest(".size-price-block").remove();
        }
    });

    // 新增顏色
    $(document).on("click", ".add-color-btn", function () {
        const $container = $(this).siblings(".color-stock-container");
        const $row = $(`
      <div class="mb-2 color-row">
        <div class="row g-2">
          <div class="col-md-6">
            <input type="text" class="form-control color-input" placeholder="顏色描述">
          </div>
          <div class="col-md-4">
            <input type="number" class="form-control stock-input" placeholder="庫存">
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-sm w-100 btn-outline-danger remove-color-btn">
              <i class="bi bi-trash"></i> 刪除
            </button>
          </div>
        </div>
      </div>
    `);
        $container.append($row);

        // 加完以後，看看到底有幾行，若超過 1 行就啟用所有刪除按鈕
        const count = $container.children(".color-row").length;
        $container.find(".remove-color-btn").prop("disabled", count <= 1);
    });

    // 刪除顏色
    $(document).on("click", ".remove-color-btn", function () {
        const $container = $(this).closest(".color-stock-container");
        const $thisRow = $(this).closest(".color-row");

        // 只有超過 1 行時才允許刪除
        if ($container.children(".color-row").length > 1) {
            $thisRow.remove();
        }

        // 刪除後重新計算剩餘行數，若只剩 1 行就 disable 所有刪除按鈕
        const remaining = $container.children(".color-row").length;
        $container.find(".remove-color-btn").prop("disabled", remaining <= 1);
    });

    // 上架狀態顯示文字
    $("#productActive").on("change", function () {
        $("#statusText").text($(this).is(":checked") ? "上架" : "下架");
    });

    // 編輯商品事件處理
    $(document).on("click", ".edit-product", async function () {
        const productId = $(this).data("id");
        if (!productId) return;

        try {
            // 獲取商品詳情
            const res = await fetch(
                `/new_test/api/product.php?action=getProduct&id=${productId}`
            );
            const result = await res.json();

            if (!result.success) {
                throw new Error(result.message || "獲取商品詳情失敗");
            }

            const product = result.product;

            // 修改 Modal 標題和按鈕
            $("#addProductModalLabel").text("編輯商品");
            $("#addProduct")
                .text("更新商品")
                .data("edit-mode", true)
                .data("product-id", productId);

            // 填充表單數據
            $("#productName").val(product.ProductName);
            $("#productType").val(product.Type);
            $("#productIntro").val(product.Introdution);
            $("#productActive").prop("checked", product.isActive == 1);
            $("#statusText").text(product.isActive == 1 ? "上架" : "下架");

            // 顯示主圖片預覽
            if (product.mainImage) {
                $("#mainImagePreview")
                    .attr("src", `${product.mainImage}`)
                    .show();
                $("#uploadMainImage").hide();
            } else {
                $("#mainImagePreview").hide();
                $("#uploadMainImage").show();
            }

            // 清空並填充畫廊圖片
            $("#galleryImagesPreview").empty();
            if (product.galleryImages && product.galleryImages.length > 0) {
                product.galleryImages.forEach((img) => {
                    const $img = $("<img>")
                        .attr("src", `/new_test/${img.url}`)
                        .addClass("img-thumbnail")
                        .css({ width: "100px", height: "100px", margin: "4px" })
                        .attr("draggable", true);
                    $("#galleryImagesPreview").append($img);
                });
            }

            // 清空並填充尺寸和顏色選項
            $("#sizePriceContainer").empty();
            if (product.sizeOptions && product.sizeOptions.length > 0) {
                product.sizeOptions.forEach((sizeOption) => {
                    // —— 把下面這段加在這裡 ——

                    // 解析顏色陣列：把後端回傳的 "Colors" 字串拆成陣列
                    const colorLabels = sizeOption.Colors
                        ? sizeOption.Colors.split(",")
                        : [];

                    // 建立尺寸區塊（保留你原本的 HTML 結構）
                    const $sizeBlock = $(`
                        <div class="card mb-3 size-price-block">
                          <div class="card-body">
                            <div class="row g-3">
                              <div class="col-md-3">
                                <label class="form-label">尺寸</label>
                                <input type="number" class="form-control size-input" placeholder="尺寸" value="${
                                    sizeOption.Size
                                }">
                              </div>
                              <div class="col-md-3">
                                <label class="form-label">尺寸描述</label>
                                <input type="text" class="form-control size-desc-input" placeholder="尺寸描述" value="${
                                    sizeOption.SizeDescription || ""
                                }">
                              </div>
                              <div class="col-md-3">
                                <label class="form-label">價格</label>
                                <input type="number" class="form-control price-input" placeholder="價格" value="${
                                    sizeOption.Price
                                }">
                              </div>
                              <div class="col-md-3 text-end">
                                <button type="button" class="btn btn-outline-danger remove-size-btn">
                                  <i class="bi bi-trash"></i> 刪除
                                </button>
                              </div>
                            </div>
                            <div class="mt-3">
                              <button type="button" class="btn btn-outline-primary add-color-btn">
                                <i class="bi bi-plus"></i> 添加顏色
                              </button>
                              <div class="color-stock-container mt-2"></div>
                            </div>
                          </div>
                        </div>
                    `);

                    // (3) 填充顏色與庫存
                    const $colorContainer = $sizeBlock.find(
                        ".color-stock-container"
                    );
                    if (
                        Array.isArray(sizeOption.colors) &&
                        sizeOption.colors.length
                    ) {
                        sizeOption.colors.forEach((colorOption, idx) => {
                            const $colorRow = $(`
                                <div class="mb-2 color-row">
                                  <div class="row g-2">
                                    <div class="col-md-6">
                                      <input type="text" class="form-control color-input" placeholder="顏色描述" value="${
                                          colorOption.Color ||
                                          sizeOption.Colors ||
                                          ""
                                      }">
                                    </div>
                                    <div class="col-md-4">
                                      <input type="number" class="form-control stock-input" placeholder="庫存" value="${
                                          colorOption.Stock || ""
                                      }">
                                    </div>
                                    <div class="col-md-2">
                                      <button type="button" class="btn btn-sm w-100 btn-outline-danger remove-color-btn">
                                        <i class="bi bi-trash"></i> 刪除
                                      </button>
                                    </div>
                                  </div>
                                </div>
                            `);
                            $colorContainer.append($colorRow);
                        });
                    } else {
                        // 最少保留一行空白
                        $colorContainer.append(`
                            <div class="mb-2 color-row">
                              <div class="row g-2">
                                <div class="col-md-6">
                                  <input type="text" class="form-control color-input" placeholder="顏色描述">
                                </div>
                                <div class="col-md-4">
                                  <input type="number" class="form-control stock-input" placeholder="庫存">
                                </div>
                                <div class="col-md-2">
                                  <button type="button" class="btn btn-sm w-100 btn-outline-danger remove-color-btn" disabled>
                                    <i class="bi bi-trash"></i> 刪除
                                  </button>
                                </div>
                              </div>
                            </div>
                        `);
                    }
                    // 更新刪除按鈕狀態
                    const remaining =
                        $colorContainer.children(".color-row").length;
                    $colorContainer
                        .find(".remove-color-btn")
                        .prop("disabled", remaining <= 1);

                    // (4) 把尺寸區塊加回容器
                    $("#sizePriceContainer").append($sizeBlock);

                    // —— 這裡結束 ——
                });
            } else {
                // 如果沒有任何尺寸，先觸發「新增尺寸」按鈕
                $("#addSizeRow").trigger("click");
            }

            // 打開 Modal
            const modal = new bootstrap.Modal($("#addProductModal"));
            modal.show();
        } catch (err) {
            console.error("獲取商品詳情錯誤:", err);

            // 顯示錯誤訊息
            const toastHtml = `
                <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                  <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-exclamation-triangle me-2"></i>獲取商品詳情失敗：${
                        err.message || "未知錯誤"
                    }</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                  </div>
                </div>`;
            const $container = $(
                '<div class="position-fixed bottom-0 end-0 p-3">'
            )
                .append(toastHtml)
                .appendTo("body");
            const toast = new bootstrap.Toast($container.find(".toast")[0], {
                delay: 5000,
            });
            toast.show();
            toast._element.addEventListener("hidden.bs.toast", () =>
                $container.remove()
            );
        }
    });

    // 篩選商品
    $("#filterType, #filterStatus").on("change", filterProducts);
    $("#searchProduct").on("input", filterProducts);

    async function filterProducts() {
        const type = $("#filterType").val();
        const status = $("#filterStatus").val();
        const search = $("#searchProduct").val();
        try {
            const res = await fetch(
                `/api/product.php?action=filter&type=${encodeURIComponent(
                    type
                )}&status=${encodeURIComponent(
                    status
                )}&search=${encodeURIComponent(search)}`
            );
            const data = await res.json();
            if (!data.success) throw new Error(data.message);
            const $tbody = $("table tbody").empty();
            if (data.products.length === 0) {
                return $tbody.append(
                    '<tr><td colspan="6" class="text-center">暫無符合條件的商品</td></tr>'
                );
            }
            data.products.forEach((p) => {
                $tbody.append(`
                    <tr>
                      <td>${p.ProductID}</td>
                      <td>${p.ProductName}</td>
                      <td>${p.Type}</td>
                      <td>${p.TotalStock}</td>
                      <td>${
                          p.isActive == 1
                              ? '<span class="badge bg-success">上架</span>'
                              : '<span class="badge bg-secondary">下架</span>'
                      }</td>
                      <td>
                        <div class="btn-group">
                          <button class="btn btn-sm btn-outline-primary edit-product" data-id="${
                              p.ProductID
                          }"><i class="bi bi-pencil"></i></button>
                          <button class="btn btn-sm btn-outline-danger delete-product" data-id="${
                              p.ProductID
                          }" title="刪除商品"><i class="bi bi-trash"></i> 刪除</button>
                        </div>
                      </td>
                    </tr>
                `);
            });
            renderPagination(
                data.currentPage,
                data.totalPages,
                type,
                status,
                search
            );
        } catch (err) {
            console.error("篩選失敗:", err);
        }
    }

    function renderPagination(current, total, type, status, search) {
        const $pg = $(".pagination").empty();
        const makeLink = (p, text, disabled) =>
            `<li class="page-item ${
                disabled ? "disabled" : ""
            }"><a class="page-link" href="?page=${p}&type=${encodeURIComponent(
                type
            )}&status=${encodeURIComponent(status)}&search=${encodeURIComponent(
                search
            )}">${text}</a></li>`;
        $pg.append(makeLink(current - 1, "上一頁", current <= 1));
        for (let i = 1; i <= total; i++) {
            $pg.append(
                `<li class="page-item ${
                    i === current ? "active" : ""
                }"><a class="page-link" href="?page=${i}&type=${encodeURIComponent(
                    type
                )}&status=${encodeURIComponent(
                    status
                )}&search=${encodeURIComponent(search)}">${i}</a></li>`
            );
        }
        $pg.append(makeLink(current + 1, "下一頁", current >= total));
    }

    // 儲存商品（示範 Toast）
    $("#saveProduct").on("click", function () {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="d-flex">
                <div class="toast-body"><i class="bi bi-check-circle me-2"></i>商品已成功保存！</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
              </div>
            </div>`;
        const $container = $('<div class="position-fixed bottom-0 end-0 p-3">')
            .append(toastHtml)
            .appendTo("body");
        const toast = new bootstrap.Toast($container.find(".toast")[0], {
            delay: 3000,
        });
        toast.show();
        toast._element.addEventListener("hidden.bs.toast", () =>
            $container.remove()
        );
        $("#addProductModal").modal("hide");
    });
});

// 刪除商品函數
async function deleteProduct(productId) {
    try {
        // 使用 FormData 來傳遞數據
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("productId", productId);

        // 發送請求到後端處理文件
        const res = await fetch(
            "models/backend/functions/product/deleteProductHandler.php",
            {
                method: "POST",
                body: formData,
            }
        );
        const result = await res.json();
        return result;
    } catch (error) {
        console.error("刪除商品錯誤:", error);
        return {
            success: false,
            message: error.message || "刪除商品時發生錯誤",
        };
    }
}

// 更新商品函數
async function updateProduct(formData) {
    try {
        // 確保 formData 包含 action 參數
        if (!formData.has("action")) {
            formData.append("action", "updateProduct");
        }

        // 發送請求到後端處理文件
        const res = await fetch(
            "models/backend/functions/product/updateProductHandler.php",
            {
                method: "POST",
                body: formData,
            }
        );
        const result = await res.json();
        return result;
    } catch (error) {
        console.error("更新商品錯誤:", error);
        return {
            success: false,
            message: error.message || "更新商品時發生錯誤",
        };
    }
}
