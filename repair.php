<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="css/navPillsStyles.css">
    <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
    <title>歐印精品-保固維修</title>
</head>

<body>
    <?php include "header.php"; ?>
    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" img-fluid>
        <h1 class="h1">保固維修</h1>
    </div>

    <div class="container text-center">
        <a class="btn btn-success" href="imgs/repair/AllenRepairPrice0609.jpg">維修價格表</a>
        <a class="btn btn-success" href="src/KeyReport.doc">鑰匙價格表</a>
    </div>

    <div class="container">
        <ul class="nav nav-pills justify-content-end" id="brandTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active " id="TW-tab" data-bs-toggle="pill" data-bs-target="#TWContent"
                    type="button" role="tab" aria-controls="TWContent" aria-selected="true">中文</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="Eng-tab" data-bs-toggle="pill" data-bs-target="#EngContent" type="button"
                    role="tab" aria-controls="EngContent" aria-selected="false">English</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="TWContent" role="tabpanel" aria-labelledby="TW-tab">
                <!-- 中文內容 -->
                <div class="container w-100">
                    <div class="container my-3 d-flex justify-content-center">
                        <img src="imgs/repair/repairContext.jpg" alt="" class="img-fluid">
                    </div>

                    <h3 class="text-decoration-underline">維修流程</h3>
                    <div class="container my-3 d-flex justify-content-center">
                        <img src="imgs/repair/repairRundown.jpg" alt="" class="img-fluid">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="EngContent" role="tabpanel" aria-labelledby="Eng-tab">
                <!-- 英文內容 -->
                <div class="container">
                    <div class="container my-3 d-flex justify-content-center">
                        <img src="imgs/repair/repairContextUS.jpg" alt="" class="img-fluid">
                    </div>

                    <h3 class="text-decoration-underline">Repair Rundown</h3>
                    <div class="container my-3 d-flex justify-content-center">
                        <img src="imgs/repair/repairRundownEN.jpg" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ"
        crossorigin="anonymous"></script>
</body>

</html>