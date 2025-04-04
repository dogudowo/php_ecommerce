<?php
session_start();

// 模擬的使用者資料庫
$users = [
    'admin' => ['password' => '1234', 'role' => 'admin'],
    'user' => ['password' => 'user123', 'role' => 'user']
];

// 獲取表單提交的帳號和密碼
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account = $_POST['account'] ?? '';
    $password = $_POST['password'] ?? '';

    // 驗證帳號和密碼
    if (isset($users[$account]) && $users[$account]['password'] === $password) {
        // 登入成功，設定 Session
        $_SESSION['username'] = $account;
        $_SESSION['user_role'] = $users[$account]['role'];

        // 根據角色導向不同頁面
        if ($_SESSION['user_role'] === 'admin') {
            header('Location: backend.php');
        } else {
            header('Location: index.php');
        }
        exit();
    } else {
        // 登入失敗，返回登入頁面
        header('Location: login.php?error=invalid_credentials');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="icon" href="./imgs/webimg.ico" />
    <title>歐印精品-會員登入</title>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" img-fluid>
        <h1 class="h1">會員登入</h1>
    </div>

    <div class="container">
        <div class="row">
            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="text" class="form-label">帳號</label>
                    <input type="text" class="form-control" id="text" name="account" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">密碼</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials'): ?>
                    <div class="alert alert-danger" role="alert">
                        帳號或密碼錯誤，請重新嘗試！
                    </div>
                <?php endif; ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">登入</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>