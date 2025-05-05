<?php
session_start();

// 清除所有 session 變數
$_SESSION = array();

// 如果需要刪除 session cookie，則加上以下代碼
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 銷毀 session
session_destroy();

// 加入header
include __DIR__ . "/fixedFile/header.php";
?>

<script>
    window.location.href = "index.php";
</script>

<?php
// 加入footer
include __DIR__ . "/fixedFile/footer.php";
?>