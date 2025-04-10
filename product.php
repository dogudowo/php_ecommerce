<?php
// Load product data from JSON file
$jsonData = file_get_contents('product.json'); // 之後要改為從資料庫讀取
$productData = json_decode($jsonData, true);

// Get the product ID from the URL
$productId = isset($_GET['id']) ? $_GET['id'] : null;

// Find the product by ID
$product = null;
foreach ($productData as $item) {
    if ($item['id'] === $productId) {
        $product = $item['product'];
        $title = $item['title'];
        $titleImage = $item['titleImage'];
        $titleAlt = $item['titleAlt'];
        break;
    }
}

// If no product is found, show an error message
if (!$product) {
    die('產品未找到');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="icon" href="./imgs/webimg.ico" />
    <title><?php echo $title; ?></title>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container text-center border-bottom border-black my-3">
        <img src="<?php echo $titleImage; ?>" alt="<?php echo $titleAlt; ?>" class="img-fluid">
        <h1 class="h1"><?php echo $product['id']; ?> <span class="h3"><?php echo $product['series']; ?></span></h1>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-7 col-12">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['id']; ?>" class="img-fluid">
                <p class="text-left text-danger w-75 mx-auto">
                    <?php echo $product['description']; ?>
                </p>
            </div>
            <div class="col-md-5 col-12 mt-4">
                <form>
                    <div class="row mb-3">
                        <label for="variantSelect" class="col-sm-2 col-form-label text-nowrap">型號：</label>
                        <div class="col-sm-10">
                            <select id="variantSelect" class="form-select col-sm-10" required
                                aria-label="Default select example">
                                <?php foreach ($product['variants'] as $variant): ?>
                                    <option value="<?php echo $variant['id']; ?>"
                                        data-oldprice="<?php echo $variant['oldPrice']; ?>"
                                        data-price="<?php echo $variant['price']; ?>">
                                        <?php echo $variant['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="colFormLabel" class="col-sm-2 col-form-label text-nowrap">顏色：</label>
                        <div class="col-sm-10">
                            <select class="form-select col-sm-10" required aria-label="Default select example">
                                <?php foreach ($product['colors'] as $color): ?>
                                    <option value="<?php echo $color['id']; ?>"><?php echo $color['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="colFormLabel" class="col-sm-2 col-form-label text-nowrap">數量：</label>
                        <div class="col-sm-10">
                            <select class="form-select col-sm-10" required aria-label="Default select example">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center">
                        <p>
                            <span id="oldPrice" class="text-decoration-line-through text-danger"> 價格:
                                <?php echo number_format($product['variants'][0]['oldPrice']); ?> </span>
                            <strong id="price"
                                class="h4">$<?php echo number_format($product['variants'][0]['price']); ?></strong>
                        </p>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">加入購物車</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="container mt-4">
            <div class="row justify-content-center">
                <div class="col-md-9 col-12 text-center">
                    <?php foreach ($product['galleryImages'] as $galleryImage): ?>
                        <img src="<?php echo $galleryImage; ?>" alt="" class="img-fluid">
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Update price dynamically based on selected variant
        const variantSelect = document.getElementById('variantSelect');
        const oldPriceElement = document.getElementById('oldPrice');
        const priceElement = document.getElementById('price');

        variantSelect.addEventListener('change', function () {
            const selectedOption = variantSelect.options[variantSelect.selectedIndex];
            const oldPrice = selectedOption.getAttribute('data-oldprice');
            const price = selectedOption.getAttribute('data-price');

            oldPriceElement.textContent = `價格: ${Number(oldPrice).toLocaleString()}`;
            priceElement.textContent = `$${Number(price).toLocaleString()}`;
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>