<?php
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

try {
    $result = getAllProducts($page, 10, $type, $status, $search);
    $products = $result['products'];
    $totalPages = $result['totalPages'];
    $currentPage = $result['currentPage'];
    $productTypes = getProductTypes();
} catch (Exception $e) {
    error_log("Error in product manager: " . $e->getMessage());
    $products = [];
    $totalPages = 1;
    $currentPage = 1;
    $productTypes = [];
}
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
                        <?php foreach ($productTypes as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>" <?= $t === $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t) ?>
                            </option>
                        <?php endforeach; ?>
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
                <table class="table table-hover align-middle">
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
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center">暫無商品數據</td>
                            </tr>
                        <?php else:
                            foreach ($products as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['ProductID']) ?></td>
                                    <td><?= htmlspecialchars($p['ProductName']) ?></td>
                                    <td><?= htmlspecialchars($p['Type']) ?></td>
                                    <td><?= htmlspecialchars($p['TotalStock']) ?></td>
                                    <td>
                                        <?php if ($p['isActive']): ?>
                                            <span class="badge bg-success">上架</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">下架</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary edit-product"
                                                data-id="<?= $p['ProductID'] ?>"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger delete-product"
                                                data-id="<?= $p['ProductID'] ?>" title="刪除商品">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <?php require_once __DIR__ . '/functions/pagination.php';
            $filterParams = [
                'type' => $type,
                'status' => $status,
                'search' => $search
            ];
            $pagination = generatePagination($currentPage, $totalPages, $filterParams);
            echo $pagination; ?>

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
                                    <textarea class="form-control" id="productIntro" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" id="productActive"
                                            style="transform: scale(1.5); margin-right: 1rem; margin-left: 1rem;">
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
                                        <input type="file" id="mainImageInput" accept="image/*" style="display: none;">
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
                                        <input type="file" id="galleryImageInput" accept="image/*" multiple
                                            style="display: none;">
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
                                                                <div class="col-md-2">
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
                    <button type="button" class="btn btn-primary" id="addProduct">新增商品</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="models/backend/functions/product/product.js"></script>