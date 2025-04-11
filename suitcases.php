<?php
// Load product data from JSON file
$jsonData = file_get_contents('product.json');
$productData = json_decode($jsonData, true);
?>

<div class="container p-0 m-0">
    <div class="row g-2">
        <?php
        foreach ($productData as $item) {
            $product = $item['product'];
            echo '
            <div class="col-6 col-md-3">
                <form action="product.php" method="GET">
                    <input type="hidden" name="id" value="' . $item['id'] . '">
                    <div class="card" style="cursor: pointer;" onclick="this.parentNode.submit()">
                        <img src="' . $product['image'] . '" class="card-img-top" alt="' . $product['name'] . '">
                        <div class="card-body">
                            <h5>' . $product['name'] . '</h5>
                            <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' . $product['variants'][0]['name'] . '</p>
                            <p class="text-nowrap">
                                <span class="text-decoration-line-through text-danger">價格:' . number_format($product['variants'][0]['price']) . '</span>
                                <strong class="h5">$' . number_format($product['variants'][0]['price'] * 0.8) . '</strong>
                            </p>
                        </div>
                        <div class="card-footer text-muted btn w-100 text-decoration-none border-0 bg-transparent shadow-none">
                            了解更多
                        </div>
                    </div>
                </form>
            </div>';
        }
        ?>
    </div>
</div>