<nav class="navbar navbar-expand-md bg-white sticky-top border-bottom" data-bs-theme="light">
    <div class="container">
        <?php
        $navItems = [
            ['name' => '歐印精品', 'link' => 'index.php'],
            ['name' => '品牌介紹', 'link' => 'introduce.php'],
            ['name' => '購物說明', 'link' => 'information.php'],
            ['name' => '保固維修', 'link' => 'repair.php'],
            ['name' => '聯絡我們', 'link' => 'contentUs.php']
        ];
        ?>

        <a class="navbar-toggler border-0" tabindex="2" data-bs-toggle="offcanvas" data-bs-target="#offcanvas"
            aria-controls="offcanvas" aria-label="Toggle navigation" role="button"
            style="outline: none !important; box-shadow: none !important;">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-list"
                viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
            </svg>
        </a>


        <!-- 品牌圖片 -->
        <a class="navbar-brand mx-auto p-0 m-0" tabindex="1" href="index.php">
            <img src="./imgs/title.png" alt="Title" height="50" class="p-0 m-0">
        </a>

        <!-- Offcanvas 導航菜單 -->
        <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="offcanvas" aria-labelledby="offcanvasLabel">
            <div class="offcanvas-body pt-md-2 pt-4">
                <ul class="navbar-nav flex-grow-1 justify-content-evenly">
                    <?php foreach ($navItems as $item): ?>
                        <li class="nav-item h5">
                            <a class="nav-link" href="<?= $item['link'] ?>"><?= $item['name'] ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- 購物車按鈕 -->
        <a href="Shopping-cart.php" class="btn me-2 m-0" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.5rem" height="1.5rem" fill="currentColor"
                class="bi bi-cart" viewBox="0 0 16 16">
                <path
                    d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
            </svg>
        </a>
    </div>
</nav>