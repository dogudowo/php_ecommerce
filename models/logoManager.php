<?php
require_once __DIR__ . '/../api/error_handler.php';
// 系統設置管理文件

// 只有後台登入者可存取
if (!defined('INCLUDED_FROM_ADMIN') && !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-image me-2"></i></i>品牌logo管理</h3>
</div>
<div class="container-fluid p-0">

</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

<script>

</script>