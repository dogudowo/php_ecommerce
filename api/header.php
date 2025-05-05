<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getNavItems()
{
    $items = [
        ['url' => 'index.php', 'text' => '歐印精品'],
        ['url' => 'brand.php', 'text' => '品牌介紹'],
        ['url' => 'instructions.php', 'text' => '購物說明'],
        ['url' => 'repair.php', 'text' => '維修保固'],
        ['url' => 'about.php', 'text' => '關於我們'],
        ['url' => 'myOrder.php', 'text' => '我的訂單'],
    ];

    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff')) {
        $items[] = ['url' => 'backend.php', 'text' => '後台管理'];
    }

    return $items;
}

function renderNavItems($navItems, $class = '')
{
    foreach ($navItems as $item) {
        echo '<li class="nav-item">';
        echo '<a href="' . $item['url'] . '" class="nav-link text-black ' . $class . '" style="font-size: large;">';
        echo $item['text'];
        echo '</a></li>';
    }
}

function renderUserControls($isMobile = false, $showCartText = false)
{
    $btnClass = $isMobile ? 'w-100' : 'me-2';
    $cartClass = $showCartText ? 'btn btn-primary w-100' : 'text-black px-3';
    echo '<a href="cart.php" class="' . $cartClass . ' d-flex align-items-center justify-content-center gap-2">';
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="1.3rem" height="1.3rem" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16">';
    echo '<path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l.84 4.479 9.144-.459L13.89 4zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>';
    echo '</svg>';

    // 只在指定時顯示"購物車"文字
    if ($showCartText) {
        echo ' 購物車';
    }

    echo '</a>';

    if (!$isMobile) {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            echo '<a href="logout.php" class="btn btn-danger w-100' . $btnClass . '">登出</a>';
        } else {
            echo '<a href="login.php" class="btn btn-primary w-100' . $btnClass . '">登入</a>';
        }
    }
}

function getPageTitle()
{
    // 獲取當前頁面文件名
    $current_page = basename($_SERVER['PHP_SELF']);
    $page_title = '歐印精品';

    // 根據頁面設置標題
    if ($current_page === 'productDetail.php') {
        $page_title = '歐印精品-商品詳情';
    } else if ($current_page === 'brand.php') {
        $page_title = '歐印精品-品牌介紹';
    } else if ($current_page === 'instructions.php') {
        $page_title = '歐印精品-購物說明';
    } else if ($current_page === 'repair.php') {
        $page_title = '歐印精品-維修保固';
    } else if ($current_page === 'about.php') {
        $page_title = '歐印精品-關於我們';
    } else if ($current_page === 'myOrder.php') {
        $page_title = '歐印精品-我的訂單';
    } else if ($current_page === 'backend.php') {
        $page_title = '歐印精品-後台管理';
    } else if ($current_page === 'cart.php') {
        $page_title = '歐印精品-購物車';
    } else if ($current_page === 'login.php') {
        $page_title = '歐印精品-會員登入';
    }

    return $page_title;
}

function outputProductTitleScript()
{
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page === 'productDetail.php') {
        echo '<script>document.addEventListener("DOMContentLoaded", function() {
            const productNameElement = document.getElementById("product-name");
            if (productNameElement) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === "childList" && productNameElement.textContent) {
                            document.title = "歐印精品-" + productNameElement.textContent;
                        }
                    });
                });
                observer.observe(productNameElement, { childList: true });
            }
        });</script>';
    }
}
?>