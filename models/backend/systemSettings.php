<?php
// 系統設置管理文件

// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../../login.php');
    exit;
}

// 系統設置相關功能將在這裡實現
// 例如：網站標題、聯繫方式、郵件設置等

?>
<div class="container-fluid p-0">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>系統設置</h5>
                </div>
                <div class="card-body">
                    <!-- 系統設置表單 -->
                    <form id="systemSettingsForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>基本設置</h5>
                                <div class="mb-3">
                                    <label for="siteName" class="form-label">網站名稱</label>
                                    <input type="text" class="form-control" id="siteName" placeholder="請輸入網站名稱">
                                </div>
                                <div class="mb-3">
                                    <label for="siteDescription" class="form-label">網站描述</label>
                                    <textarea class="form-control" id="siteDescription" rows="3"
                                        placeholder="請輸入網站描述"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="contactEmail" class="form-label">聯繫郵箱</label>
                                    <input type="email" class="form-control" id="contactEmail" placeholder="請輸入聯繫郵箱">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>顯示設置</h5>
                                <div class="mb-3">
                                    <label for="itemsPerPage" class="form-label">每頁顯示商品數</label>
                                    <select class="form-select" id="itemsPerPage">
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="30">30</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">首頁顯示選項</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showFeaturedProducts"
                                            checked>
                                        <label class="form-check-label" for="showFeaturedProducts">
                                            顯示精選商品
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showNewArrivals" checked>
                                        <label class="form-check-label" for="showNewArrivals">
                                            顯示新品上架
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>系統設置功能正在開發中，目前表單提交功能尚未啟用。
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-secondary me-md-2" disabled>
                                <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                            </button>
                            <button type="button" class="btn btn-primary" disabled>
                                <i class="bi bi-save me-1"></i>保存設置
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>