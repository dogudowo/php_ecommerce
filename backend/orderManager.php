<div class="tab-pane" id="orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
    <h3 class="mb-3">購買訂單管理</h3>
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">購買訂單</h5>
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

<script>
    function loadOrderDetails(orderId) {
        fetch(`php/APIs.php?action=getOrderDetails&order_id=${orderId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(data => {
                if (!data) {
                    throw new Error('Empty response from server');
                }

                if (data.error) {
                    throw new Error(data.error);
                }

                const items = data.items || [];
                const status = data.status || 'Unknown';
                const totalAmount = data.total_amount || 0;

                let html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>訂單編號: ORD-${orderId}</h6>
                    </div>
                    <div class="col-md-6 text-end">
                        <select name="status" class="form-select">
                            <option value="Pending" ${status === 'Pending' ? 'selected' : ''}>處理中</option>
                            <option value="Shipping" ${status === 'Shipping' ? 'selected' : ''}>運送中</option>
                            <option value="Completed" ${status === 'Completed' ? 'selected' : ''}>已完成</option>
                            <option value="Cancel" ${status === 'Cancel' ? 'selected' : ''}>已取消</option>
                            <option value="abnormal" ${status === 'abnormal' ? 'selected' : ''}>異常</option>
                        </select>
                    </div>
                </div>`;

                if (items.length === 0) {
                    html += `<div class="alert alert-info">此訂單沒有商品</div>`;
                } else {
                    html += `
                    <table class="table">
                        <thead>
                            <tr>
                                <th>商品</th>
                                <th>規格</th>
                                <th>單價</th>
                                <th>數量</th>
                                <th>小計</th>
                            </tr>
                        </thead>
                        <tbody>`;

                    items.forEach(item => {
                        html += `
                        <tr>
                            <td>${item.ProductName || '未知商品'}</td>
                            <td>${(item.Color || '') + (item.SizeDescription ? ', ' + item.SizeDescription : '')}</td>
                            <td>NT$${item.Price || 0}</td>
                            <td>${item.Quantity || 0}</td>
                            <td>NT$${item.Subtotal || 0}</td>
                        </tr>`;
                    });

                    html += `</tbody></table>`;
                }

                html += `
                <div class="text-end">
                    <h5>總金額: NT$${totalAmount}</h5>
                </div>`;

                document.getElementById('editOrderContent').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('editOrderContent').innerHTML =
                    `<div class="alert alert-danger">載入訂單詳情失敗: ${error.message}</div>`;
            });
    }

    document.getElementById('saveOrderChanges').addEventListener('click', function () {
        const orderId = document.querySelector('#editOrderContent .row.mb-3 h6').textContent.replace('訂單編號: ORD-', '');
        const statusSelect = document.querySelector('#editOrderContent select[name="status"]');
        const newStatus = statusSelect.value;

        fetch('php/APIs.php?action=updateOrderStatus', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                new_status: newStatus
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 關閉modal
                    bootstrap.Modal.getInstance(document.getElementById('editOrderModal')).hide();

                    // 更新狀態標籤 - 使用更可靠的選擇器
                    const rows = document.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        if (row.textContent.includes(`ORD-${orderId}`)) {
                            const statusBadge = row.querySelector('.badge');
                            const statusClass = getStatusClass(newStatus);
                            const statusText = getStatusText(newStatus);

                            statusBadge.className = `badge ${statusClass}`;
                            statusBadge.textContent = statusText;
                        }
                    });
                } else {
                    alert('更新訂單狀態失敗: ' + (data.message || '未知錯誤'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('發生錯誤，請稍後再試');
            });
    });

    // 新增輔助函數
    function getStatusClass(status) {
        switch (status) {
            case 'Completed': return 'bg-success';
            case 'Pending': return 'bg-info text-dark';
            case 'Cancel': return 'bg-warning';
            case 'abnormal': return 'bg-danger';
            case 'Shipping': return 'bg-primary'; // 新增運送中狀態
            default: return 'bg-secondary';
        }
    }

    function getStatusText(status) {
        switch (status) {
            case 'Completed': return '已完成';
            case 'Pending': return '處理中';
            case 'Cancel': return '已取消';
            case 'abnormal': return '異常';
            case 'Shipping': return '運送中'; // 新增運送中狀態
            default: return status;
        }
    }
</script>