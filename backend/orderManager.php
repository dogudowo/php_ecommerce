<div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="v-pills-orders-tab">
    <h3 class="mb-3">訂單管理</h3>
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">所有訂單</h5>
            <div class="input-group w-50">
                <input type="text" class="form-control" placeholder="搜尋訂單...">
                <button class="btn btn-outline-secondary" type="button">搜尋</button>
            </div>
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
                        <tr>
                            <td>ORD-20230001</td>
                            <td>李小明</td>
                            <td>NT$5,800</td>
                            <td><span class="badge bg-success">已完成</span></td>
                            <td>2023/12/15</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info">查看</button>
                                <button class="btn btn-sm btn-outline-warning">編輯</button>
                            </td>
                        </tr>
                        <tr>
                            <td>ORD-20230002</td>
                            <td>張美美</td>
                            <td>NT$3,200</td>
                            <td><span class="badge bg-warning text-dark">處理中</span></td>
                            <td>2023/12/20</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info">查看</button>
                                <button class="btn btn-sm btn-outline-warning">編輯</button>
                            </td>
                        </tr>
                        <tr>
                            <td>ORD-20230003</td>
                            <td>王大明</td>
                            <td>NT$7,600</td>
                            <td><span class="badge bg-danger">已取消</span></td>
                            <td>2023/12/22</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info">查看</button>
                                <button class="btn btn-sm btn-outline-warning">編輯</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span>顯示 1 到 3 筆，共 3 筆</span>
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">上一頁</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item disabled"><a class="page-link" href="#">下一頁</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>