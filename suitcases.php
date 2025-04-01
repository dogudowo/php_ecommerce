<div class="container p-0 m-0">
    <div class="row g-2">
        <?php
        $products = [
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
            [
                'name' => 'T6927 series',
                'size' => 'T6927-69/29',
                'original_price' => '12,800',
                'discount_price' => '$7,680',
                'image' => 'imgs/T6927/T6927S06210615.jpg'
            ],
        ];

        foreach ($products as $product) {
            echo '
            <div class="col-6 col-md-3">
                <a href="product.php" class="text-decoration-none text-black">
                    <div class="card">
                        <img src="'.$product['image'].'" class="card-img-top" alt="'.$product['name'].'">
                        <div class="card-body">
                            <h5>'.$product['name'].'</h5>
                            <p>'.$product['size'].'</p>
                            <p>
                                <span class="text-decoration-line-through text-danger">價格:'.$product['original_price'].'</span>
                                <strong class="h4">'.$product['discount_price'].'</strong>
                            </p>
                        </div>
                        <a href="product.php" class="card-footer text-muted btn w-100 text-decoration-none border-0 bg-transparent shadow-none">了解更多</a>
                    </div>
                </a>
            </div>';
        }
        ?>
    </div>
</div>