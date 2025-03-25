<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="icon" href="./imgs/webimg.ico" />
    <title>歐印精品-首頁</title>

</head>

<body>
    <nav class="navbar navbar-expand-md bg-white sticky-top border-bottom" data-bs-theme="light">
        <div class="container">
            <!-- 只在 md 以上顯示的品牌圖片 -->
            <a class="navbar-brand d-none d-md-block" href="index.php">
                <img src="./imgs/title.png" alt="Title" height="40px">
            </a>

            <!-- 只在 md 以下顯示的品牌圖片（選擇性，如果不需要可移除） -->
            <a class="navbar-brand d-md-none" href="index.php">
                <img src="./imgs/title.png" alt="Title" height="40px">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas"
                aria-controls="offcanvas" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="offcanvas offcanvas-end w-75" tabindex="-1" id="offcanvas" aria-labelledby="offcanvasLabel">
                <div class="offcanvas-header">
                    <!-- 這裡的圖片是 Offcanvas 裡的標題圖片 -->
                    <img src="./imgs/title.png" alt="歐印精品" class="offcanvas-title" id="offcanvasLabel" height="70px">
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
        </div>
    </nav>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>