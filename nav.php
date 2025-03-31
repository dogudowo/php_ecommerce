<nav class="navbar navbar-expand-md bg-white sticky-top border-bottom" data-bs-theme="light">
    <div class="container">

        <button class="navbar-toggler" tabindex="2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas"
            aria-controls="offcanvas" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>


        <!-- 中間的品牌圖片 -->
        <a class="navbar-brand mx-auto p-0 m-0" tabindex="1" href="index.php">
            <img src="./imgs/title.png" alt="Title" height="50">
        </a>

        <!-- 將 offcanvas 移到左邊 -->
        <div class="offcanvas offcanvas-start w-50" tabindex="-1" id="offcanvas" aria-labelledby="offcanvasLabel">
            <div class="offcanvas-header align-content-center">
                <!-- Offcanvas 裡的標題圖片 -->
                <!-- <img src="./imgs/title.png" alt="歐印精品" class="offcanvas-title img-fluid" id="offcanvasLabel"
                    height="40"> -->
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav flex-grow-1 justify-content-evenly">
                    <li class="nav-item h5">
                        <a class="nav-link" href="index.php">歐印精品</a>
                    </li>
                    <li class="nav-item h5">
                        <a class="nav-link" href="introduce.php">品牌介紹</a>
                    </li>
                    <li class="nav-item h5">
                        <a class="nav-link" href="information.php">購物說明</a>
                    </li>
                    <li class="nav-item h5">
                        <a class="nav-link" href="repair.php">保固維修</a>
                    </li>
                    <li class="nav-item h5">
                        <a class="nav-link" href="contentUs.php">聯絡我們</a>
                    </li>
                </ul>
            </div>
        </div>


        <!-- 新增的購物車按鈕 -->
        <a href="Shopping-cart.php" class="btn me-2 btn-outline-secondary m-0" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.5rem" height="1.5rem" fill="currentColor"
                class="bi bi-cart" viewBox="0 0 16 16">
                <path
                    d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
            </svg>
        </a>
    </div>
</nav>