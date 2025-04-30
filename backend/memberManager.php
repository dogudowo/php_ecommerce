<div class="tab-pane" id="members" role="tabpanel" aria-labelledby="v-pills-members-tab">
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
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tbody id="membersTableBody">
                        <tr>
                            <td colspan="7" class="text-center">載入中...</td>
                        </tr>
                    </tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 編輯會員 Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">編輯會員資料</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="memberEditForm">
                    <input type="hidden" id="editMemberId" name="memberId">
                    <div class="mb-3">
                        <label class="form-label">姓名</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">電話</label>
                        <input type="text" class="form-control" id="editPhone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">電子郵件</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">城市</label>
                        <input type="text" class="form-control" id="editCity" name="city">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">地址</label>
                        <input type="text" class="form-control" id="editAddress" name="address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">身份</label>
                        <select class="form-select" id="editIsAdmin" name="isAdmin">
                            <option value="0">會員</option>
                            <option value="1">管理員</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="saveMemberChanges">儲存變更</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const editMemberModal = new bootstrap.Modal(document.getElementById('editMemberModal'));

    // 監聽全域點擊
    document.addEventListener('click', async (e) => {
        // 確保點到的是「編輯」按鈕
        if (e.target.classList.contains('edit-member-btn')) {
            const memberId = e.target.dataset.memberId;
            try {
                const resp = await fetch(`php/APIs.php?action=getMember&memberId=${memberId}`);
                if (!resp.ok) throw new Error(resp.statusText);
                const data = await resp.json();
                if (data.error) throw new Error(data.message || '讀取失敗');

                // 填表單
                document.getElementById('editMemberId').value = data.MemberID || '';
                document.getElementById('editName').value = data.membername || '';
                document.getElementById('editPhone').value = data.phonenum || '';
                document.getElementById('editEmail').value = data.memberemail || '';
                document.getElementById('editCity').value = data.City || '';
                document.getElementById('editAddress').value = data.Address || '';
                document.getElementById('editIsAdmin').value = data.IsAdmin ? '1' : '0';

                editMemberModal.show();
            } catch (err) {
                alert('無法讀取會員資料：' + err.message);
                console.error(err);
            }
        }
    });

    // 保存更改
    document.getElementById('saveMemberChanges').addEventListener('click', async () => {
        const form = document.getElementById('memberEditForm');
        const formData = new FormData(form);

        // 添加表單驗證
        if (!formData.get('name') || !formData.get('phone') || !formData.get('email')) {
            alert('請填寫所有必填字段');
            return;
        }

        try {
            const resp = await fetch('php/APIs.php?action=updateMember', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.fromEntries(formData))
            });

            const result = await resp.json();
            if (!resp.ok || !result.success) {
                throw new Error(result.message || '更新失敗');
            }

            editMemberModal.hide();
            // 直接更新畫面而不重整
            const row = document.querySelector(`tr[data-member-id="${formData.get('memberId')}"]`);
            if (row) {
                row.cells[1].textContent = formData.get('name');
                row.cells[2].textContent = formData.get('phone');
                row.cells[3].textContent = formData.get('email');
                row.cells[5].textContent = formData.get('isAdmin') === '1' ? '管理員' : '會員';
            }
        } catch (err) {
            alert('更新錯誤: ' + err.message);
            console.error('更新錯誤:', err);
        }
    });

    async function loadMembers() {
        const resp = await fetch('php/APIs.php?action=getMembers');
        const data = await resp.json();
        const tbody = document.getElementById('membersTableBody');
        tbody.innerHTML = '';
        if (data.length) {
            data.forEach(member => {
                const tr = document.createElement('tr');
                tr.dataset.memberId = member.MemberID;  // ← 這行很重要
                tr.innerHTML = `
                    <td>${member.MemberID}</td>
                    <td>${member.membername}</td>
                    <td>${member.phonenum}</td>
                    <td>${member.memberemail}</td>
                    <td>${member.order_count}</td>
                    <td>${member.role}</td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-primary edit-member-btn"
                                data-member-id="${member.MemberID}">
                        編輯
                        </button>
                    </td>
                    `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">無會員資料可顯示</td></tr>';
        }
    }

    // 一開始載入
    loadMembers();
</script>