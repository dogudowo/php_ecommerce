<?php
session_start();

// 檢查用戶是否已登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 根據用戶角色重定向到相應的後台頁面
if ($_SESSION['user_role'] === 'admin') {
    // 管理員訪問完整後台
    include 'admin_backend.php';
} elseif ($_SESSION['user_role'] === 'staff') {
    // 員工訪問限制後台
    include 'staff_backend.php';
} else {
    // 一般會員無權訪問後台
    header('Location: index.php');
    exit;
}
?>