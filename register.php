<?php
// 檢查是否已登入，若已登入則跳轉
if (isset($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

// 引入註冊功能
require_once __DIR__ . '/api/register.php';

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);

    $result = registerUser($name, $phone, $email, $password, $confirm_password, $city, $address);

    if ($result['success']) {
        // 註冊成功，跳轉到登入頁面
        header('Location: login.php');
        exit;
    } else {
        // 顯示錯誤訊息
        $error_message = $result['message'];
    }
}
?>

<?php include "fixedFile/header.php"; ?>

<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
    <h1 class="h1">會員註冊</h1>
</div>

<main class="container-sm col-12 col-md-6 col-lg-4 mx-auto my-5">
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
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="確認密碼"
                required>
            <label for="confirm_password">確認密碼</label>
        </div>

        <div class="form-floating mb-3">
            <select class="form-select" id="city" name="city" required>
                <option value="" selected disabled>請選擇城市</option>
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
            <label for="city">城市</label>
        </div>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="address" name="address" placeholder="地址" required>
            <label for="address">地址</label>
        </div>

        <button class="w-100 btn btn-lg btn-success" type="submit">註冊</button>

        <p class="mt-3 text-center">
            <a href="login.php" class="text-decoration-none">已有帳號？立即登入</a>
        </p>
    </form>
</main>

<?php include "fixedFile/footer.php"; ?>