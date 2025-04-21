<?php
session_start();

// 檢查是否已經登入，如果已登入則直接導向首頁
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// 初始化錯誤訊息變數
$error_message = '';

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // 模擬使用者驗證 - 管理員和會員
    $users = [
        '0912345678' => [
            'password' => '1234',
            'role' => 'admin',
            'name' => '管理員'
        ],
        '0912345679' => [
            'password' => '1234',
            'role' => 'member',
            'name' => '會員'
        ]
    ];

    if (array_key_exists($phone, $users) && $users[$phone]['password'] === $password) {
        // 登入成功，設定 session
        $_SESSION['logged_in'] = true;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_role'] = $users[$phone]['role'];
        $_SESSION['user_name'] = $users[$phone]['name'];

        // 重定向到之前嘗試訪問的頁面或首頁
        $redirect_to = $_SESSION['redirect_to'] ?? 'index.php';
        unset($_SESSION['redirect_to']); // 清除重定向信息

        header("Location: $redirect_to");
        exit;
    } else {
        $error_message = '手機號碼或密碼錯誤，請重新輸入。';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
    <title>歐印精品-會員登入</title>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
        <h1 class="h1">會員登入</h1>
    </div>

    <main class="container-sm col-12 col-md-6 col-lg-2 mx-auto my-5">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <h1 class="h3 mb-3 fw-normal text-center">請登入</h1>

            <div class="form-floating mb-3 ">
                <input type="tel" class="form-control rounded-bottom-0" id="phone" name="phone" placeholder="輸入電話號碼"
                    required pattern="[0-9]{10}">
                <label for="phone">手機號碼</label>
            </div>

            <div class="form-floating mb-3 ">
                <input type="password" class="form-control rounded-top-0" id="password" name="password"
                    placeholder="輸入密碼" required>
                <label for="password">密碼</label>
            </div>

            <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" value="remember-me"> 記住我
                </label>
            </div>

            <button class="w-100 btn btn-lg btn-success" type="submit">登入</button>

            <p class="mt-3 text-center">
                <a href="#" class="text-decoration-none">忘記密碼？</a> |
                <a href="#" class="text-decoration-none">註冊會員</a>
            </p>

            <p class="mt-5 mb-3 text-muted text-center">&copy; 2025 歐印精品</p>
        </form>
    </main>

    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ"
        crossorigin="anonymous"></script>
</body>

</html>