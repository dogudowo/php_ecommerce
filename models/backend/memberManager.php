<?php
// 會員管理文件

// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../../login.php');
    exit;
}

// 引入數據庫連接
require_once __DIR__ . '/../../api/db.php';

// 獲取所有會員
function getAllMembers()
{
    $pdo = getDBConnection();
    try {
        $stmt = $pdo->prepare("SELECT m.*, 
                              (SELECT COUNT(*) FROM Orders WHERE MembersID = m.MemberID) as OrderCount 
                              FROM Member m 
                              ORDER BY m.MemberID");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching members: " . $e->getMessage());
        return [];
    }
}

// 獲取會員詳情
function getMemberDetails($memberId)
{
    $pdo = getDBConnection();
    try {
        // 獲取會員基本信息
        $stmt = $pdo->prepare("SELECT * FROM Member WHERE MemberID = ?");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$member) {
            return null;
        }

        // 獲取會員訂單
        $stmt = $pdo->prepare("SELECT o.OrderID, o.OrderDate, o.Status, 
                              COUNT(oi.OptionID) as ItemCount, 
                              SUM(op.Price * oi.Quantity) as TotalAmount 
                              FROM Orders o 
                              JOIN OrderItem oi ON o.OrderID = oi.OrderID 
                              JOIN Options op ON oi.OptionID = op.OptionID 
                              WHERE o.MembersID = ? 
                              GROUP BY o.OrderID 
                              ORDER BY o.OrderDate DESC");
        $stmt->execute([$memberId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'member' => $member,
            'orders' => $orders
        ];
    } catch (PDOException $e) {
        error_log("Error fetching member details: " . $e->getMessage());
        return null;
    }
}

// 嘗試獲取會員列表
try {
    $members = getAllMembers();
} catch (Exception $e) {
    error_log("Error in member manager: " . $e->getMessage());
    $members = [];
}
?>

<div class="container-fluid p-0">
    <!-- 會員管理頁面標題 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-people me-2"></i>會員管理</h3>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" id="exportMembers">
                <i class="bi bi-file-earmark-excel me-2"></i>匯出會員資料
            </button>
            <button type="button" class="btn btn-primary" id="refreshMembers">
                <i class="bi bi-arrow-clockwise me-2"></i>刷新列表
            </button>
        </div>
    </div>

    <!-- 會員篩選區 -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="filterCity" class="form-label">城市</label>
                    <select class="form-select" id="filterCity">
                        <option value="">全部城市</option>
                        <option value="台北市">台北市</option>
                        <option value="新北市">新北市</option>
                        <option value="桃園市">桃園市</option>
                        <option value="台中市">台中市</option>
                        <option value="台南市">台南市</option>
                        <option value="高雄市">高雄市</option>
                        <!-- 其他城市選項 -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filterAdmin" class="form-label">會員類型</label>
                    <select class="form-select" id="filterAdmin">
                        <option value="">全部類型</option>
                        <option value="0">一般會員</option>
                        <option value="1">管理員</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchMember" class="form-label">搜尋會員</label>
                    <input type="text" class="form-control" id="searchMember" placeholder="會員名稱、Email或電話">
                </div>
            </div>
        </div>
    </div>

    <!-- 會員列表 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>姓名</th>
                            <th>Email</th>
                            <th>電話</th>
                            <th>城市</th>
                            <th>訂單數</th>
                            <th>類型</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="8" class="text-center">暫無會員數據</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['MemberID']); ?></td>
                                    <td><?php echo htmlspecialchars($member['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($member['Phone']); ?></td>
                                    <td><?php echo htmlspecialchars($member['City']); ?></td>
                                    <td><?php echo htmlspecialchars($member['OrderCount']); ?></td>
                                    <td>
                                        <?php if ($member['IsAdmin'] == 1): ?>
                                            <span class="badge bg-primary">管理員</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">一般會員</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary view-member"
                                                data-id="<?php echo htmlspecialchars($member['MemberID']); ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning edit-member"
                                                data-id="<?php echo htmlspecialchars($member['MemberID']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($member['IsAdmin'] == 0): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-member"
                                                    data-id="<?php echo htmlspecialchars($member['MemberID']); ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php require_once __DIR__ . '/functions/pagination.php';

            // 使用分頁功能
            $pagination = generatePagination($currentPage, $totalPages, $filterParams);
            ?>
            <?php echo $pagination; ?>
        </div>
    </div>

    <!-- 會員詳情模態框 -->
    <div class="modal fade" id="memberDetailModal" tabindex="-1" aria-labelledby="memberDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="memberDetailModalLabel">會員詳情</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>會員詳情功能正在開發中，敬請期待。
                    </div>
                    <!-- 會員詳情內容將在這裡動態加載 -->
                    <div id="memberDetailContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 編輯會員模態框 -->
    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editMemberModalLabel">編輯會員資料</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editMemberForm">
                        <input type="hidden" id="editMemberId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">姓名</label>
                            <input type="text" class="form-control" id="editName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPhone" class="form-label">電話</label>
                            <input type="tel" class="form-control" id="editPhone" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCity" class="form-label">城市</label>
                            <select class="form-select" id="editCity" required>
                                <option value="台北市">台北市</option>
                                <option value="新北市">新北市</option>
                                <option value="桃園市">桃園市</option>
                                <option value="台中市">台中市</option>
                                <option value="台南市">台南市</option>
                                <option value="高雄市">高雄市</option>
                                <!-- 其他城市選項 -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editAddress" class="form-label">地址</label>
                            <input type="text" class="form-control" id="editAddress" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="editIsAdmin">
                                <label class="form-check-label" for="editIsAdmin">設為管理員</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" id="saveMember">保存更改</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 會員篩選功能
    document.getElementById('filterCity').addEventListener('change', filterMembers);
    document.getElementById('filterAdmin').addEventListener('change', filterMembers);
    document.getElementById('searchMember').addEventListener('input', filterMembers);

    function filterMembers() {
        console.log('篩選會員功能尚未實現');
        // 實際篩選功能將在後續開發中實現
    }

    // 查看會員詳情
    document.querySelectorAll('.view-member').forEach(button => {
        button.addEventListener('click', function () {
            const memberId = this.getAttribute('data-id');
            console.log('查看會員詳情：', memberId);
            // 實際查看功能將在後續開發中實現

            // 顯示模態框
            const modal = new bootstrap.Modal(document.getElementById('memberDetailModal'));
            modal.show();
        });
    });

    // 編輯會員資料
    document.querySelectorAll('.edit-member').forEach(button => {
        button.addEventListener('click', function () {
            const memberId = this.getAttribute('data-id');
            console.log('編輯會員資料：', memberId);

            // 設置會員ID
            document.getElementById('editMemberId').value = memberId;

            // 這裡應該從服務器獲取會員資料並填充表單
            // 暫時使用模擬數據
            document.getElementById('editName').value = '測試會員';
            document.getElementById('editEmail').value = 'test@example.com';
            document.getElementById('editPhone').value = '0912345678';
            document.getElementById('editCity').value = '台北市';
            document.getElementById('editAddress').value = '測試地址123號';
            document.getElementById('editIsAdmin').checked = false;

            // 顯示模態框
            const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
            modal.show();
        });
    });

    // 保存會員資料
    document.getElementById('saveMember').addEventListener('click', function () {
        const memberId = document.getElementById('editMemberId').value;
        const name = document.getElementById('editName').value;
        const email = document.getElementById('editEmail').value;
        const phone = document.getElementById('editPhone').value;
        const city = document.getElementById('editCity').value;
        const address = document.getElementById('editAddress').value;
        const isAdmin = document.getElementById('editIsAdmin').checked ? 1 : 0;

        console.log('保存會員資料：', memberId, name, email, phone, city, address, isAdmin);
        // 實際保存功能將在後續開發中實現

        // 關閉模態框
        const modal = bootstrap.Modal.getInstance(document.getElementById('editMemberModal'));
        modal.hide();

        // 顯示成功提示
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '11';

        toastContainer.innerHTML = `
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i>會員資料已成功更新！
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        document.body.appendChild(toastContainer);
        const toastElement = toastContainer.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();

        // 3秒後移除 Toast 元素
        setTimeout(() => {
            document.body.removeChild(toastContainer);
        }, 3500);
    });

    // 刪除會員
    document.querySelectorAll('.delete-member').forEach(button => {
        button.addEventListener('click', function () {
            const memberId = this.getAttribute('data-id');
            console.log('刪除會員：', memberId);

            // 顯示確認對話框
            if (confirm('確定要刪除此會員嗎？此操作無法撤銷。')) {
                // 實際刪除功能將在後續開發中實現
                alert('刪除功能正在開發中');
            }
        });
    });

    // 匯出會員資料
    document.getElementById('exportMembers').addEventListener('click', function () {
        console.log('匯出會員資料功能尚未實現');
        // 實際匯出功能將在後續開發中實現
        alert('匯出功能正在開發中');
    });

    // 刷新會員列表
    document.getElementById('refreshMembers').addEventListener('click', function () {
        console.log('刷新會員列表');
        // 實際刷新功能將在後續開發中實現
        location.reload();
    });
</script>