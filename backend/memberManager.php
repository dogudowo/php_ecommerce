<div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="v-pills-members-tab">
    <h3 class="mb-3">會員管理</h3>
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">會員列表</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>姓名</th>
                            <th>電話</th>
                            <th>電子郵件</th>
                            <th>訂單數</th>
                            <th>身份</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 使用小寫路徑
                        include_once '/Applications/XAMPP/xamppfiles/htdocs/public_html/php/APIs.php';

                        // 添加錯誤處理
                        try {
                            $members = getMembers();
                            if (!empty($members)) {
                                foreach ($members as $member) {
                                    echo "<tr>
                                            <td>" . htmlspecialchars($member['MemberID']) . "</td>
                                            <td>" . htmlspecialchars($member['membername']) . "</td>
                                            <td>" . htmlspecialchars($member['phonenum']) . "</td>
                                            <td>" . htmlspecialchars($member['memberemail']) . "</td>
                                            <td>" . htmlspecialchars($member['order_count']) . "</td>
                                            <td>" . htmlspecialchars($member['role']) . "</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>無會員資料可顯示</td></tr>";
                            }
                        } catch (Exception $e) {
                            echo "<tr><td colspan='6' class='text-center text-danger'>錯誤: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>