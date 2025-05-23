<?php
require_once __DIR__ . '/../api/error_handler.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentRole = isset($_SESSION['user_role']) ? (int) $_SESSION['user_role'] : 0;
// 會員管理文件

// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit;
}

?>

<div class="container-fluid p-0">
    <!-- 會員管理頁面標題 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-people me-2"></i>會員管理</h3>
    </div>

    <!-- 會員篩選區 -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="filterCity" class="form-label">城市</label>
                    <select class="form-select" id="filterCity">
                        <option value="">全部城市</option>
                        <option value="基隆市">基隆市</option>
                        <option value="新北市">新北市</option>
                        <option value="台北市">台北市</option>
                        <option value="桃園市">桃園市</option>
                        <option value="新竹縣">新竹縣</option>
                        <option value="新竹市">新竹市</option>
                        <option value="苗栗縣">苗栗縣</option>
                        <option value="台中市">台中市</option>
                        <option value="彰化縣">彰化縣</option>
                        <option value="南投縣">南投縣</option>
                        <option value="雲林縣">雲林縣</option>
                        <option value="嘉義縣">嘉義縣</option>
                        <option value="嘉義市">嘉義市</option>
                        <option value="台南市">台南市</option>
                        <option value="高雄市">高雄市</option>
                        <option value="屏東縣">屏東縣</option>
                        <option value="宜蘭縣">宜蘭縣</option>
                        <option value="花蓮縣">花蓮縣</option>
                        <option value="台東縣">台東縣</option>
                        <option value="金門縣">金門縣</option>
                        <option value="連江縣">連江縣</option>
                        <option value="澎湖縣">澎湖縣</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filterAdmin" class="form-label">會員類型</label>
                    <select class="form-select" id="filterAdmin">
                        <option value="">全部類型</option>
                        <option value="0">一般會員</option>
                        <option value="1">員工</option>
                        <option value="2">管理員</option>
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
                <table id="memberTable" class="table table-hover align-middle">
                    <!-- 固定欄寬 -->
                    <colgroup>
                        <col style="width: 6%;"> <!-- ID -->
                        <col style="width: 14%;"> <!-- 姓名 -->
                        <col style="width: 22%;"> <!-- Email -->
                        <col style="width: 14%;"> <!-- 電話 -->
                        <col style="width: 10%;"> <!-- 城市 -->
                        <col style="width: 10%;"> <!-- 訂單數 -->
                        <col style="width: 12%;"> <!-- 類型 -->
                        <col style="width: 12%;"> <!-- 操作 -->
                    </colgroup>

                    <thead>
                        <tr>
                            <th>會員ID</th>
                            <th>姓名</th>
                            <th>Email</th>
                            <th>電話</th>
                            <th>城市</th>
                            <th>訂單數</th>
                            <th>類型</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="memberTableBody">
                        <!-- 由 JS 動態產生 -->
                    </tbody>
                </table>
            </div>
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
                        <div class="mb-3 text-center d-flex flex-column">
                            <label class="form-label">會員類型</label>
                            <div class="btn-group" role="group" aria-label="會員類型">
                                <input type="radio" class="btn-check" name="editMemberType" id="editMemberType0"
                                    value="0" autocomplete="off">
                                <label class="btn btn-outline-primary" for="editMemberType0">一般會員</label>
                                <input type="radio" class="btn-check" name="editMemberType" id="editMemberType1"
                                    value="1" autocomplete="off">
                                <label class="btn btn-outline-primary" for="editMemberType1">員工</label>
                                <input type="radio" class="btn-check" name="editMemberType" id="editMemberType2"
                                    value="2" autocomplete="off">
                                <label class="btn btn-outline-primary" for="editMemberType2">管理員</label>
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

<!-- 刪除會員確認模態框 -->
<div class="modal fade" id="deleteMemberModal" tabindex="-1" aria-labelledby="deleteMemberModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteMemberModalLabel">確認刪除會員</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>您確定要刪除此會員嗎？此操作將同時刪除該會員的所有相關資料，包括訂單、購物車項目等，且無法恢復。</p>
                <input type="hidden" id="deleteMemberId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteMember">確認刪除</button>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<script src="api/toast.js"></script>

<script>
    /* ====== 共用設定 ====== */
    const MEMBER_API = 'api/MemberBackend.php';
    const currentUserRole = <?php echo json_encode($currentRole, JSON_NUMERIC_CHECK); ?>;

    /* ====== 篩選（保留 stub） ====== */
    document.getElementById('filterCity').addEventListener('change', filterMembers);
    document.getElementById('filterAdmin').addEventListener('change', filterMembers);
    document.getElementById('searchMember').addEventListener('input', filterMembers);

    async function filterMembers() {
        const city = document.getElementById('filterCity').value;
        const memberType = document.getElementById('filterAdmin').value;
        const searchTerm = document.getElementById('searchMember').value;

        const tbody = document.getElementById('memberTableBody');
        tbody.innerHTML = spinnerRow();

        try {
            const params = new URLSearchParams();
            if (city) params.append('city', city);
            if (memberType) params.append('type', memberType);
            if (searchTerm) params.append('search', searchTerm);

            const res = await fetch(`${MEMBER_API}?action=list&${params.toString()}`);
            if (!res.ok) throw new Error(res.status);
            const json = await res.json();

            if (json.success && json.members?.length) {
                tbody.innerHTML = '';
                json.members.forEach(m => tbody.appendChild(buildRow(m)));
                rebindEventListeners();
            } else {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center">沒有符合條件的會員資料</td></tr>`;
            }
        } catch (err) {
            console.error('篩選會員失敗：', err);
            tbody.innerHTML = `<tr><td colspan="8" class="text-danger text-center">篩選會員失敗</td></tr>`;
        }
    }

    /* ====================
       會員列表載入／刷新
       ==================== */
    async function loadMembers() {
        const tbody = document.getElementById('memberTableBody');
        tbody.innerHTML = spinnerRow();

        try {
            const res = await fetch(`${MEMBER_API}?action=list`);
            if (!res.ok) throw new Error(res.status);
            const json = await res.json();

            if (json.success && json.members?.length) {
                tbody.innerHTML = '';
                json.members.forEach(m => tbody.appendChild(buildRow(m)));
                rebindEventListeners();   // 為新列重新掛事件
            } else {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center">目前沒有會員資料</td></tr>`;
            }
        } catch (err) {
            console.error('載入會員列表失敗：', err);
            tbody.innerHTML = `<tr><td colspan="8" class="text-danger text-center">無法載入會員資料</td></tr>`;
        }
    }

    function spinnerRow() {
        return `
            <tr>
                <td colspan="8" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">載入中...</span>
                    </div>
                </td>
            </tr>`;
    }

    function badgeByRole(isAdmin) {
        return isAdmin == 2 ? '<span class="badge bg-primary">管理員</span>' :
            isAdmin == 1 ? '<span class="badge bg-success">員工</span>' :
                '<span class="badge bg-secondary">一般會員</span>';
    }

    function buildRow(m) {
        const tr = document.createElement('tr');
        tr.dataset.memberId = m.MemberID;
        tr.innerHTML = `
            <td>${m.MemberID}</td>
            <td>${m.Name}</td>
            <td>${m.Email}</td>
            <td>${m.Phone}</td>
            <td>${m.City}</td>
            <td>${m.OrderCount ?? 'N/A'}</td>
            <td>${badgeByRole(m.IsAdmin)}</td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-success view-member"   data-id="${m.MemberID}" title="查看詳情"><i class="bi bi-eye"></i></button>
                    ${currentUserRole === 2 ? `
                        <button class="btn btn-sm btn-outline-primary edit-member"   data-id="${m.MemberID}" title="編輯"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-outline-danger  delete-member" data-id="${m.MemberID}" title="刪除"><i class="bi bi-trash"></i></button>
                    ` : ''}
                </div>
            </td>`;
        return tr;
    }

    /* ====================
       詳情、編輯、刪除：事件處理
       ==================== */
    async function viewMemberHandler() {
        const id = this.dataset.id;
        try {
            const res = await fetch(`${MEMBER_API}?action=get&id=${id}`);
            if (!res.ok) throw new Error(res.status);
            const { success, member, message } = await res.json();
            if (!success) throw new Error(message);

            document.getElementById('memberDetailContent').innerHTML = `
                <div class="card mb-3">
                  <div class="card-body">
                    <h5 class="card-title">基本資料</h5>
                    ${['MemberID', 'Name', 'Email', 'Phone'].map(f => row(f, member[f])).join('')}
                    ${row('地址', `${member.City}${member.Address}`)}
                    ${row('會員類型', badgeByRole(member.IsAdmin))}
                  </div>
                </div>`;
            new bootstrap.Modal('#memberDetailModal').show();
        } catch (err) {
            console.error(err);
            showToast('獲取會員資料失敗！', 'danger');
        }

        function row(label, val) {
            return `<div class="row mb-2"><div class="col-md-4"><strong>${label}：</strong></div><div class="col-md-8">${val}</div></div>`;
        }
    }

    async function editMemberHandler() {
        const id = this.dataset.id;
        try {
            const res = await fetch(`${MEMBER_API}?action=get&id=${id}`);
            if (!res.ok) throw new Error(res.status);
            const { member } = await res.json();

            document.getElementById('editMemberId').value = id;
            // 設定按鈕群組的選取狀態
            document.querySelectorAll('input[name="editMemberType"]').forEach(radio => {
                if (radio.value == member.IsAdmin) {
                    radio.checked = true;
                }
            });
            new bootstrap.Modal('#editMemberModal').show();
        } catch (err) {
            console.error(err);
            showToast('獲取會員資料失敗！', 'danger');
        }
    }

    async function deleteMemberHandler() {
        const id = this.dataset.id;
        document.getElementById('deleteMemberId').value = id;
        new bootstrap.Modal('#deleteMemberModal').show();
    }

    /* 存檔 */
    document.getElementById('saveMember').onclick = async () => {
        const memberId = document.getElementById('editMemberId').value;
        const isAdmin = document.querySelector('input[name="editMemberType"]:checked').value;

        try {
            const res = await fetch(`${MEMBER_API}?action=update`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ memberId, isAdmin })
            });
            const json = await res.json();
            bootstrap.Modal.getInstance('#editMemberModal').hide();

            if (json.success) {
                // 更新表格
                const row = document.querySelector(`tr[data-member-id="${memberId}"] td:nth-child(7)`);
                if (row) row.innerHTML = badgeByRole(+isAdmin);
                showToast('會員資料已成功更新！');
            } else {
                showToast(json.message || '更新失敗', 'danger');
            }
        } catch (err) {
            console.error(err);
            showToast('系統錯誤，請稍後再試', 'danger');
        }
    };

    /* 刪除確認 */
    document.getElementById('confirmDeleteMember').onclick = async () => {
        const memberId = document.getElementById('deleteMemberId').value;
        try {
            const res = await fetch(`${MEMBER_API}?action=delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ memberId })
            });
            const json = await res.json();
            bootstrap.Modal.getInstance('#deleteMemberModal').hide();

            if (json.success) {
                document.querySelector(`tr[data-member-id="${memberId}"]`)?.remove();
                showToast('會員已成功刪除！');
            } else {
                showToast(json.message || '刪除失敗', 'danger');
            }
        } catch (err) {
            console.error(err);
            showToast('系統錯誤，請稍後再試', 'danger');
        }
    };

    /* 重新綁定（每次重繪 table 後呼叫） */
    function rebindEventListeners() {
        document.querySelectorAll('.view-member').forEach(btn => btn.onclick = viewMemberHandler);
        document.querySelectorAll('.edit-member').forEach(btn => btn.onclick = editMemberHandler);
        document.querySelectorAll('.delete-member').forEach(btn => btn.onclick = deleteMemberHandler);
    }
    /* 頁面 init */
    document.addEventListener('DOMContentLoaded', loadMembers);
</script>