<?php
require_once __DIR__ . '/fixedFile/header.php';
?>

<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png" alt="歐印精品" img-fluid>
    <h1 class="h1">購物說明</h1>
</div>

<div class="container mt-3">
    <ul class="nav nav-pills justify-content-center gap-3" id="brandTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="shopping-tab" data-bs-toggle="pill" data-bs-target="#shoppingContent"
                type="button" role="tab" aria-controls="shoppingContent" aria-selected="true"
                style="border-radius: 20px;">
                購物說明
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="qa-tab" data-bs-toggle="pill" data-bs-target="#qaContent" type="button"
                role="tab" aria-controls="qaContent" aria-selected="false" style="border-radius: 20px;">購物Q&A</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="process-tab" data-bs-toggle="pill" data-bs-target="#processContent"
                type="button" role="tab" aria-controls="processContent" aria-selected="false"
                style="border-radius: 20px;">
                購物流程
            </button>
        </li>
    </ul>
    <div class="tab-content my-3">
        <div class="tab-pane fade show active text-center" id="shoppingContent" role="tabpanel"
            aria-labelledby="shopping-tab">
            <!-- 購物說明內容 -->
            <img src="imgs/shopping/buyContext.jpg" class="img-fluid" />
        </div>
        <div class="tab-pane fade" id="qaContent" role="tabpanel" aria-labelledby="qa-tab">
            <!-- 購物Q&A內容 -->
            <?php
            require_once __DIR__ . '/models/frontend/QAModel.php';
            $qaItems = QAModel::getQAItems();
            ?>
            <div class="accordion" id="accordionExample">
                <?php foreach ($qaItems as $index => $item): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $index ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse<?= $index ?>" aria-expanded="false"
                                aria-controls="collapse<?= $index ?>">
                                <?= $item['question'] ?>
                            </button>
                        </h2>
                        <div id="collapse<?= $index ?>" class="accordion-collapse collapse collapsed"
                            aria-labelledby="heading<?= $index ?>" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <?= $item['answer'] ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="tab-pane fade text-center" id="processContent" role="tabpanel" aria-labelledby="process-tab">
            <img src="imgs/shopping/order.jpg" alt="" class="img-fulid" />
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/fixedFile/footer.php';
?>