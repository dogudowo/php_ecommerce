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

        <div class="container my-3 d-flex justify-content-center gap-4">
            <button type="button" class="btn btn-info">Info</button>
            <button type="button" class="btn btn-info">Info</button>
            <button type="button" class="btn btn-info">Info</button>
        </div>

        <div class="container">
            <div class="container d-flex mx-3 ">
                <h4>Language: </h4>
                <button type="button" class="btn btn-primary">English</button>
            </div>
            <div class="container">
                <div class="container my-3 d-flex justify-content-center">
                    <img src="imgs/repairContext.jpg" alt="" class="img-fluid">
                </div>

                <h3 class="text-decoration-underline">維修流程</h3>
                <div class="container my-3 d-flex justify-content-center">
                    <img src="imgs/repairRundown.jpg" alt="" class="img-fluid">
                </div>
            </div>
            <div class="container visually-hidden">
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

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>