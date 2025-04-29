<div class="tab-pane" id="repairs" role="tabpanel" aria-labelledby="v-pills-repairs-tab">
    <h3 class="mb-3">維修訂單管理</h3>
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">維修購買訂單</h5>
            <!-- <form method="get" class="input-group w-50">
                <input type="text" class="form-control" name="search" placeholder="搜尋訂單...">
                <button class="btn btn-outline-secondary" type="submit">搜尋</button>
            </form> -->
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>訂單編號</th>
                            <th>客戶</th>
                            <th>金額</th>
                            <th>狀態</th>
                            <th>日期</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once __DIR__ . '/../php/APIs.php';
                        try {
                            $orders = getOrders();
                            if ($orders === false) {
                                error_log("獲取訂單數據失敗: " . print_r(error_get_last(), true));
                                echo '<tr><td colspan="6" class="text-center text-danger">獲取訂單數據時發生錯誤</td></tr>';
                            } elseif (!empty($orders)) {
                                foreach ($orders as $order) {
                                    $statusClass = match ($order['Status']) {
                                        'Completed' => 'bg-success',
                                        'Pending' => 'bg-info text-dark',
                                        'Shipping' => 'bg-primary',
                                        'Cancel' => 'bg-warning',
                                        'abnormal' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $statusText = match ($order['Status']) {
                                        'Completed' => '已完成',
                                        'Pending' => '處理中',
                                        'Shipping' => '運送中',
                                        'Cancel' => '已取消',
                                        'abnormal' => '異常',
                                        default => $order['Status']
                                    };
                                    ?>
                                    <tr>
                                        <td>ORD-<?= htmlspecialchars($order['OrderID']) ?></td>
                                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                        <td>NT$<?= number_format($order['total_amount']) ?></td>
                                        <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                        <td><?= htmlspecialchars($order['OrderDate']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                                data-bs-target="#editOrderModal"
                                                onclick="loadOrderDetails(<?= $order['OrderID'] ?>, true)">
                                                編輯
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center">目前沒有訂單記錄</td></tr>';
                            }
                        } catch (Exception $e) {
                            error_log("訂單管理頁面錯誤: " . $e->getMessage());
                            echo '<tr><td colspan="6" class="text-center text-danger">系統錯誤: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <?php if (!empty($orders)): ?>
                    <span>顯示 1 到 <?= count($orders) ?> 筆，共 <?= count($orders) ?> 筆</span>
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">上一頁</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item disabled"><a class="page-link" href="#">下一頁</a></li>
                        </ul>
                    </nav>
                <?php else: ?>
                    <span>目前沒有資料可顯示</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrderModalLabel">編輯訂單</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editOrderContent">
                <!-- 內容將由 JavaScript 動態載入 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveOrderChanges">儲存變更</button>
            </div>
        </div>
    </div>
</div>