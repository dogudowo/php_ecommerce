<?php
// 會員管理文件

// 防止直接訪問此文件
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../../login.php');
    exit;
}

// 引入會員功能
require_once __DIR__ . '/functions/member/memberFunction.php';
?>

<div class="container-fluid p-0">
    <!-- 會員管理頁面標題 -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-people me-2"></i>會員管理</h3>
        <div>
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
                    <tbody id="memberTableBody">
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">載入中...</span>
                                </div>
                            </td>
                        </tr>
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
                        <div class="mb-3">
                            <label for="editMemberType" class="form-label">會員類型</label>
                            <select class="form-select" id="editMemberType" required>
                                <option value="0">一般會員</option>
                                <option value="1">員工</option>
                                <option value="2">管理員</option>
                            </select>
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

<script>
    // 會員篩選功能
    document.getElementById('filterCity').addEventListener('change', filterMembers);
    document.getElementById('filterAdmin').addEventListener('change', filterMembers);
    document.getElementById('searchMember').addEventListener('input', filterMembers);

    function filterMembers() {
        console.log('篩選會員功能尚未實現');
    }

    // 查看會員詳情
    document.querySelectorAll('.view-member').forEach(button => {
        button.addEventListener('click', async function () {
            const memberId = this.getAttribute('data-id');
            console.log('查看會員詳情：', memberId);

            try {
                // 使用AJAX獲取會員詳細資料
                const response = await fetch(`models/backend/functions/member/getMemberDetails.php?id=${memberId}`);
                if (!response.ok) throw new Error('獲取會員資料失敗');

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || '獲取會員資料失敗');
                }

                const member = data.member;

                // 填充會員詳情內容
                const detailContent = document.getElementById('memberDetailContent');
                detailContent.innerHTML = `
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">基本資料</h5>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>會員ID：</strong></div>
                                <div class="col-md-8">${member.MemberID}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>姓名：</strong></div>
                                <div class="col-md-8">${member.Name}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Email：</strong></div>
                                <div class="col-md-8">${member.Email}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>電話：</strong></div>
                                <div class="col-md-8">${member.Phone}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>地址：</strong></div>
                                <div class="col-md-8">${member.City}${member.Address}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>會員類型：</strong></div>
                                <div class="col-md-8">
                                    ${member.IsAdmin == 0 ? '<span class="badge bg-secondary">一般會員</span>' :
                        member.IsAdmin == 1 ? '<span class="badge bg-success">員工</span>' :
                            '<span class="badge bg-primary">管理員</span>'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // 移除提示信息
                const alertInfo = document.querySelector('#memberDetailModal .alert-info');
                if (alertInfo) {
                    alertInfo.remove();
                }

                // 顯示模態框
                const modal = new bootstrap.Modal(document.getElementById('memberDetailModal'));
                modal.show();
            } catch (error) {
                console.error('Error:', error);
                // 使用Bootstrap的Toast顯示錯誤
                const toastContainer = document.createElement('div');
                toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '11';

                toastContainer.innerHTML = `
                    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-exclamation-circle me-2"></i>獲取會員資料失敗！
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
            }
        });
    });

    // 編輯會員資料 (使用事件委派)
    document.getElementById('memberTableBody').addEventListener('click', async function (event) {
        const target = event.target.closest('.edit-member');
        if (!target) return;

        const memberId = target.getAttribute('data-id');
        console.log('編輯會員資料：', memberId);

        try {
            // 使用AJAX獲取會員詳細資料
            const response = await fetch(`models/backend/functions/member/getMemberDetails.php?id=${memberId}`);
            if (!response.ok) throw new Error('獲取會員資料失敗');

            const data = await response.json();
            const member = data.member;

            // 填充模態框
            document.getElementById('editMemberId').value = memberId;
            document.getElementById('editMemberType').value = member.IsAdmin;

            // 顯示模態框
            const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
            modal.show();
        } catch (error) {
            console.error('Error:', error);
            // 使用Bootstrap的Toast顯示錯誤
            const toastContainer = document.createElement('div');
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '11';

            toastContainer.innerHTML = `
                <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-exclamation-circle me-2"></i>獲取會員資料失敗！
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
        }
    });

    // 保存會員資料
    document.getElementById('saveMember').addEventListener('click', async function () {
        const memberId = document.getElementById('editMemberId').value;
        const isAdmin = document.getElementById('editMemberType').value;

        console.log('保存會員資料：', memberId, isAdmin);

        try {
            // 使用AJAX保存會員資料
            const response = await fetch('models/backend/functions/member/updateMember.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ memberId, isAdmin })
            });

            const data = await response.json();

            // 關閉模態框
            const modal = bootstrap.Modal.getInstance(document.getElementById('editMemberModal'));
            modal.hide();

            // 顯示成功或失敗提示
            const toastContainer = document.createElement('div');
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '11';

            if (data.success) {
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

                // 更新表格中的會員資料
                const memberRow = document.querySelector(`tr[data-member-id="${memberId}"]`);
                if (memberRow) {
                    // 更新會員類型標籤
                    const typeCell = memberRow.querySelector('td:nth-child(7)');
                    if (isAdmin == 0) {
                        typeCell.innerHTML = '<span class="badge bg-secondary">一般會員</span>';
                    } else if (isAdmin == 1) {
                        typeCell.innerHTML = '<span class="badge bg-success">員工</span>';
                    } else {
                        typeCell.innerHTML = '<span class="badge bg-primary">管理員</span>';
                    }
                }
            } else {
                toastContainer.innerHTML = `
                    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-exclamation-circle me-2"></i>${data.message || '更新會員資料失敗！'}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
            }

            document.body.appendChild(toastContainer);
            const toastElement = toastContainer.querySelector('.toast');
            const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
            toast.show();

            // 3秒後移除 Toast 元素
            setTimeout(() => {
                document.body.removeChild(toastContainer);
            }, 3500);
        } catch (error) {
            console.error('Error:', error);
            // 顯示錯誤提示
            const toastContainer = document.createElement('div');
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '11';

            toastContainer.innerHTML = `
                <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-exclamation-circle me-2"></i>系統錯誤，請稍後再試！
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
        }
    });

    // 會員資料更新成功後，更新表格中的會員資料
    function updateMemberRowInTable(memberId, name, email, phone, city, isAdmin) {
        // 找到對應的表格行
        const row = document.querySelector(`tr[data-member-id="${memberId}"]`);
        if (row) {
            // 更新表格中的會員資料
            row.querySelector('td:nth-child(2)').textContent = name;
            row.querySelector('td:nth-child(3)').textContent = email;
            row.querySelector('td:nth-child(4)').textContent = phone;
            row.querySelector('td:nth-child(5)').textContent = city;

            // 更新會員類型標籤
            const typeCell = row.querySelector('td:nth-child(7)');
            let typeLabel = '';
            if (isAdmin === '2') {
                typeLabel = '<span class="badge bg-primary">管理員</span>';
            } else if (isAdmin === '1') {
                typeLabel = '<span class="badge bg-success">員工</span>';
            } else {
                typeLabel = '<span class="badge bg-secondary">一般會員</span>';
            }
            typeCell.innerHTML = typeLabel;
        }
    }

    // 刪除會員
    document.querySelectorAll('.delete-member').forEach(button => {
        button.addEventListener('click', function () {
            const memberId = this.getAttribute('data-id');
            document.getElementById('deleteMemberId').value = memberId;

            // 顯示確認刪除模態框
            const modal = new bootstrap.Modal(document.getElementById('deleteMemberModal'));
            modal.show();
        });
    });

    // 確認刪除會員
    document.getElementById('confirmDeleteMember').addEventListener('click', async function () {
        const memberId = document.getElementById('deleteMemberId').value;
        console.log('刪除會員：', memberId);

        try {
            // 使用AJAX刪除會員資料
            const response = await fetch('models/backend/functions/member/deleteMember.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    memberId: memberId
                })
            });

            const data = await response.json();

            // 關閉模態框
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteMemberModal'));
            modal.hide();

            // 顯示成功或失敗提示
            const toastContainer = document.createElement('div');
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '11';

            if (data.success) {
                toastContainer.innerHTML = `
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle me-2"></i>會員已成功刪除！
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;

                // 從表格中移除該會員行
                const memberRow = document.querySelector(`tr[data-member-id="${memberId}"]`);
                if (memberRow) {
                    memberRow.remove();
                }
            } else {
                toastContainer.innerHTML = `
                    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-exclamation-circle me-2"></i>${data.message || '刪除會員失敗！'}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
            }

            document.body.appendChild(toastContainer);
            const toastElement = toastContainer.querySelector('.toast');
            const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
            toast.show();

            // 3秒後移除 Toast 元素
            setTimeout(() => {
                document.body.removeChild(toastContainer);
            }, 3500);

        } catch (error) {
            console.error('Error:', error);
        }
    });


    // 刷新會員列表
    document.getElementById('refreshMembers').addEventListener('click', function () {
        console.log('刷新會員列表');
        loadMembers();
    });

    // 載入會員列表函數
    async function loadMembers() {
        const tableBody = document.getElementById('memberTableBody');
        tableBody.innerHTML = '<tr><td colspan="8" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">載入中...</span></div></td></tr>'; // 顯示載入中提示

        try {
            const response = await fetch('models/backend/functions/member/getMembers.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            if (data.success && data.members) {
                tableBody.innerHTML = ''; // 清空現有內容
                if (data.members.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center">目前沒有會員資料</td></tr>';
                    return;
                }
                data.members.forEach(member => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-member-id', member.MemberID);

                    let memberType = '';
                    if (member.IsAdmin == 0) {
                        memberType = '<span class="badge bg-secondary">一般會員</span>';
                    } else if (member.IsAdmin == 1) {
                        memberType = '<span class="badge bg-success">員工</span>';
                    } else {
                        memberType = '<span class="badge bg-primary">管理員</span>';
                    }

                    row.innerHTML = `
                        <td>${member.MemberID}</td>
                        <td>${member.Name}</td>
                        <td>${member.Email}</td>
                        <td>${member.Phone}</td>
                        <td>${member.City}</td>
                        <td>${member.OrderCount !== undefined ? member.OrderCount : 'N/A'}</td>
                        <td>${memberType}</td>
                        <td class="btn-group">
                            <button class="btn btn-sm btn-outline-info view-member " data-id="${member.MemberID}" title="查看詳情"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-sm btn-outline-primary edit-member " data-id="${member.MemberID}" title="編輯"><i class="bi bi-pencil-square"></i></button>
                            <button class="btn btn-sm btn-outline-danger delete-member" data-id="${member.MemberID}" title="刪除"><i class="bi bi-trash"></i></button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });

                // 重新綁定事件到新的按鈕
                rebindEventListeners();

            } else {
                tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">載入會員資料失敗: ${data.message || '未知錯誤'}</td></tr>`;
            }
        } catch (error) {
            console.error('載入會員列表時發生錯誤:', error);
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">載入會員列表時發生錯誤，請檢查主控台。</td></tr>`;
        }
    }

    // 重新綁定事件監聽器函數
    function rebindEventListeners() {
        // 查看會員詳情
        document.querySelectorAll('.view-member').forEach(button => {
            button.removeEventListener('click', viewMemberHandler); // 防止重複綁定
            button.addEventListener('click', viewMemberHandler);
        });

        // 編輯會員資料
        document.querySelectorAll('.edit-member').forEach(button => {
            button.removeEventListener('click', editMemberHandler);
            button.addEventListener('click', editMemberHandler);
        });

        // 刪除會員
        document.querySelectorAll('.delete-member').forEach(button => {
            button.removeEventListener('click', deleteMemberHandler);
            button.addEventListener('click', deleteMemberHandler);
        });
    }

    // 將事件處理函數提取出來，以便重新綁定
    async function viewMemberHandler() {
        const memberId = this.getAttribute('data-id');
        console.log('查看會員詳情：', memberId);
        try {
            const response = await fetch(`models/backend/functions/member/getMemberDetails.php?id=${memberId}`);
            if (!response.ok) throw new Error('獲取會員資料失敗');
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || '獲取會員資料失敗');
            }
            const member = data.member;
            const detailContent = document.getElementById('memberDetailContent');
            detailContent.innerHTML = `
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">基本資料</h5>
                        <div class="row mb-2"><div class="col-md-4"><strong>會員ID：</strong></div><div class="col-md-8">${member.MemberID}</div></div>
                        <div class="row mb-2"><div class="col-md-4"><strong>姓名：</strong></div><div class="col-md-8">${member.Name}</div></div>
                        <div class="row mb-2"><div class="col-md-4"><strong>Email：</strong></div><div class="col-md-8">${member.Email}</div></div>
                        <div class="row mb-2"><div class="col-md-4"><strong>電話：</strong></div><div class="col-md-8">${member.Phone}</div></div>
                        <div class="row mb-2"><div class="col-md-4"><strong>地址：</strong></div><div class="col-md-8">${member.City}${member.Address}</div></div>
                        <div class="row mb-2"><div class="col-md-4"><strong>會員類型：</strong></div><div class="col-md-8">${member.IsAdmin == 0 ? '<span class="badge bg-secondary">一般會員</span>' : member.IsAdmin == 1 ? '<span class="badge bg-success">員工</span>' : '<span class="badge bg-primary">管理員</span>'}</div></div>
                    </div>
                </div>
            `;
            const modal = new bootstrap.Modal(document.getElementById('memberDetailModal'));
            modal.show();
        } catch (error) {
            console.error('Error:', error);
            showToast('獲取會員資料失敗！', 'danger');
        }
    }

    async function editMemberHandler() {
        const memberId = this.getAttribute('data-id');
        console.log('編輯會員資料：', memberId);
        try {
            const response = await fetch(`models/backend/functions/member/getMemberDetails.php?id=${memberId}`);
            if (!response.ok) throw new Error('獲取會員資料失敗');
            const data = await response.json();
            const member = data.member;
            document.getElementById('editMemberId').value = memberId;
            document.getElementById('editName').value = member.Name;
            document.getElementById('editEmail').value = member.Email;
            document.getElementById('editPhone').value = member.Phone;
            document.getElementById('editCity').value = member.City;
            document.getElementById('editAddress').value = member.Address;
            document.getElementById('editMemberType').value = member.IsAdmin;
            const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
            modal.show();
        } catch (error) {
            console.error('Error:', error);
            showToast('獲取會員資料失敗！', 'danger');
        }
    }

    function deleteMemberHandler() {
        const memberId = this.getAttribute('data-id');
        document.getElementById('deleteMemberId').value = memberId;
        const modal = new bootstrap.Modal(document.getElementById('deleteMemberModal'));
        modal.show();
    }

    // 通用 Toast 顯示函數
    function showToast(message, type = 'success') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '1056'; // 確保在 modal 之上

        const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
        const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';

        toastContainer.innerHTML = `
            <div class="toast align-items-center text-white ${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${iconClass} me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        document.body.appendChild(toastContainer);
        const toastElement = toastContainer.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();
        toastElement.addEventListener('hidden.bs.toast', function () {
            document.body.removeChild(toastContainer);
        });
    }

    // 頁面載入完成後執行
    document.addEventListener('DOMContentLoaded', function () {
        loadMembers();
    });

</script>