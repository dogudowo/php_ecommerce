<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="icon" href="./imgs/webimg.ico" />
    <title>歐印精品-保固維修</title>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container w-100">
        <div class="container text-center border-bottom border-black my-3">
            <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
            <h1 class="h1">維修保固</h1>
        </div>

        <div class="container my-3 d-flex justify-content-center gap-4 flex-wrap">
            <a href="#" class="text-decoration-none btn btn-success text-nowrap small">維修單查詢</a>
            <a href="imgs/AllenRepairPrice0609.jpg" target="_blank"
                class="text-decoration-none btn btn-success text-nowrap small">維修價格表</a>
            <a href="src/KeyReport.doc" class=" small text-nowrap text-decoration-none btn btn-success">鑰匙申請表</a>

        </div>


        <div class="container">
            <ul class="nav nav-pills justify-content-end" id="brandTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active small" id="TW-tab" data-bs-toggle="pill" data-bs-target="#TWContent"
                        type="button" role="tab" aria-controls="TWContent" aria-selected="true">中文</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link small" id="Eng-tab" data-bs-toggle="pill" data-bs-target="#EngContent"
                        type="button" role="tab" aria-controls="EngContent" aria-selected="false">English</button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="TWContent" role="tabpanel" aria-labelledby="TW-tab">
                    <!-- 中文內容 -->
                    <div class="container w-100">
                        <div class="container my-3 d-flex justify-content-center">
                            <img src="imgs/repairContext.jpg" alt="" class="img-fluid">
                        </div>

                        <h3 class="text-decoration-underline">維修流程</h3>
                        <div class="container my-3 d-flex justify-content-center">
                            <img src="imgs/repairRundown.jpg" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="EngContent" role="tabpanel" aria-labelledby="Eng-tab">
                    <!-- 英文內容 -->
                    <div class="container">
                        <div class="container my-3 d-flex justify-content-center">
                            <img src="imgs/repairContextUS.jpg" alt="" class="img-fluid">
                        </div>

                        <h3 class="text-decoration-underline">Repair Rundown</h3>
                        <div class="container my-3 d-flex justify-content-center">
                            <img src="imgs/repairRundownEN.jpg" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>