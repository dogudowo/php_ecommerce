<div class="container p-0 m-0">
    <div class="row g-2">
        <?php
        $products = [
            [
                'id' => 1,  //後端資料庫id
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'id' => 2,   //後端資料庫id
                'name' => '6021 series',
                'size' => '6021-64/26吋升級快閃',
                'original_price' => '11,500',
                'discount_price' => '$4,388',
                'image' => 'imgs/6021/6021S03140602.jpg'
            ]
        ];

        foreach ($products as $product) {
            echo '
            <div class="col-6 col-md-3">
                <form action="product.php" method="GET">
                    <input type="hidden" name="id" value="' . $product['id'] . '">
                    <div class="card" style="cursor: pointer;" onclick="this.parentNode.submit()">
                        <img src="' . $product['image'] . '" class="card-img-top" alt="' . $product['name'] . '">
                        <div class="card-body">
                            <h5>' . $product['name'] . '</h5>
                            <p>' . $product['size'] . '</p>
                            <p>
                                <span class="text-decoration-line-through text-danger">價格:' . $product['original_price'] . '</span>
                                <strong class="h4">' . $product['discount_price'] . '</strong>
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