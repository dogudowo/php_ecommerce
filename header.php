<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 定義導航選單項目陣列
$navItems = [
    ['url' => 'index.php', 'text' => '歐印精品'],
    ['url' => 'profile.php', 'text' => '品牌介紹'],
    ['url' => 'shopping.php', 'text' => '購物說明'],
    ['url' => 'repair.php', 'text' => '維修保固'],
    ['url' => 'about.php', 'text' => '關於我們'],
];

// 根據用戶角色決定最後一個選單項
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['user_role'] === 'admin') {
    $navItems[] = ['url' => 'backend.php', 'text' => '後台管理'];
} else {
    $navItems[] = ['url' => 'myOrder.php', 'text' => '我的訂單'];
}
?>

<div class="sticky-top bg-white m-0">
    <nav class="container position-relative">
        <div
            class="d-flex flex-wrap align-items-center justify-content-between justify-content-lg-between py-2 mb-3 m-0 border-bottom border-2">
            <button class="btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavHeader"
                aria-controls="offcanvasNavHeader" aria-expanded="false">
                <span class="navbar-toggler-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                        class="bi bi-list" viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" />
                    </svg>
                </span>
            </button>

            <a href="index.php" class="d-flex align-items-center mb-0 text-dark text-decoration-none">
                <img src="imgs/title.png" alt="" height="49">
            </a>

            <button class="btn d-lg-none" aria-label="Cart">
                <svg xmlns="http://www.w3.org/2000/svg" width="1.5rem" height="1.5rem" fill="currentColor"
                    class="bi bi-cart3" viewBox="0 0 16 16">
                    <path
                        d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l.84 4.479 9.144-.459L13.89 4zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                </svg>
            </button>

            <!-- 正常顯示部分開始：桌面版主選單與按鈕 -->
            <ul class="nav justify-content-center mb-lg-0 d-none d-lg-flex gap-4">
                <?php foreach ($navItems as $item): ?>
                    <li>
                        <a href="<?php echo $item['url']; ?>" class="nav-link text-black px-2 link-dark"
                            style="font-size: large"><?php echo $item['text']; ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class=" text-center d-none d-lg-flex gap-2 flex-end">
                <a href="shoppingCart.php" class="btn text-dark p-0 me-2 d-inline-flex align-items-center"
                    aria-label="Cart">
                    <!-- SVG cart icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.5rem" height="1.5rem" fill="currentColor"
                        class="bi bi-cart3" viewBox="0 0 16 16">
                        <path
                            d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l.84 4.479 9.144-.459L13.89 4zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                    </svg>
                </a>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <a href="logout.php" class="btn btn-danger me-2">登出</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary me-2">登入</a>
                <?php endif; ?>
            </div>
            <!-- 正常顯示部分結束 -->
        </div>
    </nav>

    <!-- offcanvas 顯示部分開始：手機版側邊選單 -->
    <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="offcanvasNavHeader"
        aria-labelledby="offcanvasNavHeaderLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvasNavHeaderLabel"></h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body px-0 pt-0">
            <ul class="navbar-nav nav-pills flex-column mb-auto my-2">
                <?php foreach ($navItems as $item): ?>
                    <li class="nav-item">
                        <a href="<?php echo $item['url']; ?>" class="nav-link text-black px-4" style="font-size: large;">
                            <?php echo $item['text']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="dropdown border-top">
                <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser">
                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="backend.php" style="font-size: large;">後台管理</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="myOrder.php" style="font-size: large;">我的訂單</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="d-grid gap-2 px-3 mb-3 mt-3">
                <a href="shoppingCart.php"
                    class="btn btn-primary d-flex align-items-center justify-content-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1.3rem" height="1.3rem" fill="currentColor"
                        class="bi bi-cart3" viewBox="0 0 16 16">
                        <path
                            d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l.84 4.479 9.144-.459L13.89 4zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                    </svg>
                    購物車
                </a>
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                    <a href="logout.php" class="btn btn-danger me-2 w-100">登出</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary me-2 w-100">登入</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="offcanvas-footer p-3 border-top text-center">
            <small>&copy; 2023 歐印精品</small>
        </div>
    </div>
    <!-- offcanvas 顯示部分結束 -->
</div>