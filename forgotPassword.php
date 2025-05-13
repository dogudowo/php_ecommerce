<?php
require_once __DIR__ . '/api/db.php';
require_once __DIR__ . '/api/error_handler.php';

session_start();

// 已登入就回首頁
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$stage = 'verify';  // verify | reset | done
$error_message = '';
$success_message = '';

// ---------- 表單處理 ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* 階段 1：驗證手機 + Email */
    if (isset($_POST['phone'], $_POST['email']) && !isset($_POST['new_password'])) {
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);

        try {
            $db = getDBConnection();
            $stmt = $db->prepare("SELECT MemberID FROM Member WHERE Phone = :phone AND Email = :email");
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['reset_member_id'] = (int) $user['MemberID'];
                $stage = 'reset';
            } else {
                $error_message = '手機號碼與電子郵件不匹配，請重新輸入。';
            }
        } catch (PDOException $e) {
            $error_message = '系統錯誤，請稍後再試。';
            error_log($e->getMessage());
        }
    }

    /* 階段 2：設定新密碼（已移除長度限制） */ elseif (isset($_POST['new_password'], $_POST['confirm_password']) && isset($_SESSION['reset_member_id'])) {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        $memberId = (int) $_SESSION['reset_member_id'];

        if ($newPassword !== $confirmPassword) {
            $stage = 'reset';
            $error_message = '兩次輸入的密碼不一致，請重新確認。';
        } else {
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("UPDATE Member SET Password = :pwd WHERE MemberID = :id");
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt->bindParam(':pwd', $hashedPassword);
                $stmt->bindParam(':id', $memberId);
                $stmt->execute();

                unset($_SESSION['reset_member_id']);
                $stage = 'done';
                $success_message = '密碼已成功重設，請使用新密碼登入。';
            } catch (PDOException $e) {
                $stage = 'reset';
                $error_message = '系統錯誤，請稍後再試。';
                error_log($e->getMessage());
            }
        }
    }

    // 任意錯誤 → 回驗證階段
    else {
        $stage = 'verify';
    }
}
?>

<?php include __DIR__ . "/fixedFile/header.php"; ?>

<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
    <h1 class="h1">忘記密碼</h1>
</div>

<main class="container-sm col-12 col-md-6 col-lg-3 mx-auto my-5">
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if ($stage === 'verify'): ?>
        <!-- 階段 1：驗證手機 / Email -->
        <form method="post" action="forgotPassword.php">
            <h1 class="h3 mb-3 fw-normal text-center">驗證身分</h1>
            <div class="form-floating mb-3">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="輸入手機號碼" required
                    pattern="[0-9]{10}">
                <label for="phone">手機號碼</label>
            </div>
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="輸入電子郵件" required>
                <label for="email">電子郵件</label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">下一步</button>
            <p class="mt-3 text-center"><a href="login.php" class="text-decoration-none">返回登入</a></p>
        </form>

    <?php elseif ($stage === 'reset'): ?>
        <!-- 階段 2：自訂新密碼 -->
        <form method="post" action="forgotPassword.php">
            <h1 class="h3 mb-3 fw-normal text-center">重設密碼</h1>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="輸入新密碼(至少8位)"
                    required minlength="8">
                <label for="new_password">新密碼(至少8位)</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                    placeholder="再次輸入新密碼" required>
                <label for="confirm_password">再次確認新密碼</label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">確認重設</button>
        </form>

    <?php elseif ($stage === 'done'): ?>
        <!-- 完成 -->
        <div class="text-center">
            <a href="login.php" class="btn btn-primary">前往登入</a>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . "/fixedFile/footer.php"; ?>