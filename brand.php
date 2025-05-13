<?php
require_once 'fixedFile/header.php';
require_once __DIR__ . '/api/brand.php';

$brandInfo = BrandModel::getBrandInfo();
?>

<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png" alt="歐印精品" img-fluid>
    <h1 class="h1">品牌介紹</h1>
</div>

<div class="container text-center my-3 border-bottom border-black d-flex-column justify-content-center">
    <?php foreach ($brandInfo['company_intro'] as $paragraph): ?>
        <p class="px-2 text-start mx-0"><?= $paragraph ?></p>
    <?php endforeach; ?>
</div>
<div class="container text-center my-3 w-100">
    <h5>以下為歐印代理之品牌</h5>
    <div class="container m-0 p-0">
        <ul class="nav nav-pills justify-content-center gap-3" id="brandTab" role="tablist">
            <?php foreach ($brandInfo['brands'] as $brandId => $brandData): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $brandId === array_key_first($brandInfo['brands']) ? 'active' : '' ?>"
                        id="<?= strtolower($brandId) ?>-tab" data-bs-toggle="pill"
                        data-bs-target="#<?= strtolower($brandId) ?>Content" type="button" role="tab"
                        aria-controls="<?= strtolower($brandId) ?>Content"
                        aria-selected="<?= $brandId === array_key_first($brandInfo['brands']) ? 'true' : 'false' ?>"
                        style="border-radius: 20px;"><?= $brandData['name'] ?></button>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content my-3">
            <?php foreach ($brandInfo['brands'] as $brandId => $brandData): ?>
                <div class="tab-pane fade <?= $brandId === array_key_first($brandInfo['brands']) ? 'show active' : '' ?>"
                    id="<?= strtolower($brandId) ?>Content" role="tabpanel"
                    aria-labelledby="<?= strtolower($brandId) ?>-tab">

                    <div class="container d-flex flex-wrap align-items-center justify-content-center gap-3">
                        <?php if (!empty($brandData['logos'])): ?>
                            <?php foreach ($brandData['logos'] as $logo): ?>
                                <img class="img" src="imgs/brand/<?= $brandId ?>/<?= $logo ?>" alt="<?= $brandId ?>" height="50vh">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="container text-start my-3 col-lg-9">
                        <?php if (!empty($brandData['content'])): ?>
                            <p><?= nl2br($brandData['content']) ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($brandData['images'])): ?>
                        <div class="container">
                            <h5 class="text-center">最新資訊</h5>
                            <div class="row">
                                <?php foreach ($brandData['images'] as $image): ?>
                                    <div class="col-md-4 mb-3 d-flex flex-column">
                                        <img class="img-fluid rounded" src="imgs/<?= $image ?>" alt="<?= $brandId ?>最新資訊">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once 'fixedFile/footer.php'; ?>