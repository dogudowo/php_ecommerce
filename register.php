<?php
session_start();

// 如果已登录则跳转到首页
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error_message = '';
$success_message = '';

// 处理注册表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // 验证密码是否匹配
    if ($password !== $confirm_password) {
        $error_message = '兩次輸入的密碼不一致';
    } else {
        require_once __DIR__ . '/php/APIs.php';
        $result = registerUser($name, $phone, $email, $password, $city, $address);

        if ($result['success']) {
            header("Location: login.php?registered=1&phone=" . urlencode($phone));
            exit;
        } else {
            $error_message = $result['message'] ?? '註冊失敗，請稍後再試';
        }
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
    <title>歐印精品-會員註冊</title>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
        <h1 class="h1">會員註冊</h1>
    </div>

    <main class="container-sm col-12 col-md-6 col-lg-3 mx-auto my-5">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php">
            <h1 class="h3 mb-3 fw-normal text-center">請填寫註冊資料</h1>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name" placeholder="姓名" required>
                <label for="name">姓名</label>
            </div>

            <div class="form-floating mb-3">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="手機號碼" required
                    pattern="[0-9]{10}">
                <label for="phone">手機號碼</label>
            </div>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="電子郵件" required>
                <label for="email">電子郵件</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="密碼" required>
                <label for="password">密碼</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                    placeholder="確認密碼" required>
                <label for="confirm_password">確認密碼</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="city" name="city" placeholder="城市">
                <label for="city">城市 (選填)</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="address" name="address" placeholder="地址">
                <label for="address">地址 (選填)</label>
            </div>

            <button class="w-100 btn btn-lg btn-success" type="submit">註冊</button>

            <p class="mt-3 text-center">
                <a href="login.php" class="text-decoration-none">已有帳號？立即登入</a>
            </p>
        </form>
    </main>

    <?php include "footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ"
        crossorigin="anonymous"></script>
</body>

</html>