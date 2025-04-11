<?php
// 檢查是否有設定 $current_category 變數
if (!isset($current_category)) {
    $current_category = 'luggage'; // 預設值
}

// 載入產品資料
$jsonData = file_get_contents('product.json');
$productData = json_decode($jsonData, true);
?>

<div class="container p-0 m-0">
    <div class="row g-2">
        <?php
        foreach ($productData as $item) {
            if ($item['category'] === $current_category) {
                $product = $item['product'];
                echo '
                <div class="col-6 col-md-3">
                    <form action="product.php" method="GET">
                        <input type="hidden" name="id" value="' . $item['id'] . '" >
                        <div class="card" data-type="' . (isset($product['type']) ? $product['type'] : '') . '" style="cursor: pointer;" onclick="this.parentNode.submit()">
                            <img src="' . $product['image'] . '" class="card-img-top" alt="' . $product['name'] . '">
                            <div class="card-body pb-0">
                                <h5 style="min-height: calc(2 * 1.2em);">' . $product['name'] . '</h5>
                                <p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' . $product['variants'][0]['name'] . '</p>
                                <p>
                                    <span class="text-decoration-line-through text-danger" style="font-size: 0.8rem;">價格:$' . number_format($product['variants'][0]['price'] * 1.2) . '</span><br>
                                    <strong class="h4">$' . number_format($product['variants'][0]['price']) . '</strong>
                                </p>
                            </div>
                            <div class="pt-0 card-footer text-muted btn w-100 text-decoration-none border-0 bg-transparent shadow-none">
                                了解更多
                            </div>
                        </div>
                    </form>
                </div>';
            }
        }
        ?>
    </div>
</div>