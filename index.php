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

<body width="98vw" class="align-content-center">
    <?php include 'nav.php'; ?>


    <!-- 小於 md 時顯示 offcanvas 版 sidebar -->
    <div class="container">
        <a class="btn d-md-none" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button"
            aria-controls="offcanvasExample">
            <svg xmlns="http://www.w3.org/20absolute00/svg" width="20" height="20" fill="currentColor"
                class="bi bi-funnel" viewBox="0 0 16 16">
                <path
                    d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z" />
            </svg>
            篩選
        </a>
    </div>


    <div class="offcanvas offcanvas-start d-md-none w-50" tabindex="-1" id="offcanvasExample"
        aria-labelledby="offcanvasExampleLabel">
        <div class="container">
            <!-- <div class="offcanvas-title mt-3">
                <img src="./imgs/title.png" alt="歐印精品" height="70px">
            </div> -->
            <?php include 'sidebar.php'; ?>
        </div>
    </div>

    <div class="container p-0">
        <div class="container w-100 p-1">
            <div class="row">
                <!-- Sidebar for md and above -->
                <div class="d-none d-md-block col-md-2 mt-4">
                    <?php include 'sidebar.php'; ?>
                </div>
                <!-- Suitcases always visible -->
                <div class="col-12 col-md-10 my-4">
                    <?php include 'suitcases.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
        </script>
</body>

</html>