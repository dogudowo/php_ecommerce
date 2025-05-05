<?php
session_start();

// 檢查是否已經登入，如果已登入則直接導向首頁
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// 初始化錯誤訊息變數
$error_message = '';
$success_message = '';

// 檢查是否從註冊頁面重定向過來
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success_message = '註冊成功！請登入';
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    require_once __DIR__ . '/api/login.php';
    $authResult = authenticateUser($phone, $password);

    if ($authResult['success']) {
        // 登入成功，設定 session
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $authResult['user']['id'];
        $_SESSION['user_phone'] = $authResult['user']['phone'];
        $_SESSION['user_name'] = $authResult['user']['name'];
        $_SESSION['user_role'] = $authResult['user']['role'];

        // 重定向到之前嘗試訪問的頁面或首頁
        $redirect_to = $_SESSION['redirect_to'] ?? 'index.php';
        unset($_SESSION['redirect_to']);

        header("Location: $redirect_to");
        exit;
    } else {
        $error_message = '手機號碼或密碼錯誤，請重新輸入。';
    }
}
?>

<?php include __DIR__ . "/fixedFile/header.php"; ?>

<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
    <h1 class="h1">會員登入</h1>
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

    <form method="post" action="login.php">
        <h1 class="h3 mb-3 fw-normal text-center">請登入</h1>

        <div class="form-floating mb-3 ">
            <input type="tel" class="form-control rounded-bottom-0" id="phone" name="phone" placeholder="輸入電話號碼"
                required pattern="[0-9]{10}"
                value="<?php echo isset($_GET['phone']) ? htmlspecialchars($_GET['phone']) : '' ?>">
            <label for="phone">手機號碼</label>
        </div>

        <div class="form-floating mb-3 ">
            <input type="password" class="form-control rounded-top-0" id="password" name="password" placeholder="輸入密碼"
                required>
            <label for="password">密碼</label>
        </div>

        <div class="checkbox mb-3">
            <label>
                <input type="checkbox" value="remember-me"> 記住我
            </label>
        </div>

        <button class="w-100 btn btn-lg btn-success" type="submit">登入</button>

        <p class="mt-3 text-center">
            <a href="register.php" class="text-decoration-none">註冊會員</a>
        </p>

        <p class="mt-5 mb-3 text-muted text-center">&copy; 2025 歐印精品</p>
    </form>
</main>

<?php include __DIR__ . "/fixedFile/footer.php"; ?>