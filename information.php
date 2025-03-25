<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="icon" href="./imgs/webimg.ico" />
    <title>歐印精品-購物說明</title>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
        <h1 class=" h1">購物說明</h1>
    </div>

    <div class="container my-3 d-flex justify-content-center gap-4 flex-wrap">
        <a href="QandA.php" class="text-decoration-none text-white btn btn-success text-nowrap small">購物Q &
            A</a>
        <a href="imgs/order.jpg" class="text-decoration-none text-white btn btn-success text-nowrap small">購物流程</a>
        <a href="orderInformation.php"
            class="text-decoration-none text-white btn btn-success text-nowrap small">購物訂單查詢</a>
    </div>

    <div class="container my-3 d-flex justify-content-center">
        <img src="imgs/buyContext.jpg" alt="buy context" class="img-fluid">
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>