<?php
require_once 'fixedFile/header.php';
require_once __DIR__ . '/models/frontend/BrandModel.php';

$brandInfo = BrandModel::getBrandInfo();
?>
<style>
    p {
        text-indent: 1.5rem;
    }
</style>
<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png" alt="歐印精品" img-fluid>
    <h1 class="h1">品牌介紹</h1>
</div>

<div class="container text-center my-3 border-bottom border-black d-flex-column justify-content-center">
    <?php foreach ($brandInfo['company_intro'] as $paragraph): ?>
        <p class="px-2 text-start"><?= $paragraph ?></p>
    <?php endforeach; ?>
</div>
<div class="container text-center my-3 w-100">
    <h5>以下為歐印代理之品牌</h5>
    <div class="container">
        <ul class="nav nav-pills justify-content-center gap-3" id="brandTab" role="tablist">
            <?php foreach ($brandInfo['brands'] as $brandName => $brandData): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $brandName === 'LegendWalker' ? 'active' : '' ?>"
                        id="<?= strtolower($brandName) ?>-tab" data-bs-toggle="pill"
                        data-bs-target="#<?= strtolower($brandName) ?>Content" type="button" role="tab"
                        aria-controls="<?= strtolower($brandName) ?>Content"
                        aria-selected="<?= $brandName === 'LegendWalker' ? 'true' : 'false' ?>"
                        style="border-radius: 20px;"><?= $brandName ?></button>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content my-3">
            <?php foreach ($brandInfo['brands'] as $brandName => $brandData): ?>
                <div class="tab-pane fade <?= $brandName === 'LegendWalker' ? 'show active' : '' ?>"
                    id="<?= strtolower($brandName) ?>Content" role="tabpanel"
                    aria-labelledby="<?= strtolower($brandName) ?>-tab">

                    <div class="container d-flix align-items-center">
                        <?php if (isset($brandData['logos'])): ?>
                            <?php foreach ($brandData['logos'] as $logo): ?>
                                <img class="img" src="imgs/profile/<?= $logo ?>" alt="<?= $brandName ?>" height="50vh">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <img src="imgs/profile/<?= $brandData['logo'] ?>" alt="<?= $brandName ?>" height="40px">
                        <?php endif; ?>
                    </div>

                    <div class="container text-start d-flix my-3 w-100">
                        <?php foreach ($brandData['description'] as $paragraph): ?>
                            <p><?= $paragraph ?></p>
                        <?php endforeach; ?>
                    </div>

                    <?php if (isset($brandData['images'])): ?>
                        <div class="container d-flex flex-column align-items-center">
                            <?php foreach ($brandData['images'] as $image): ?>
                                <img class="img" src="imgs/profile/<?= $image ?>" alt="">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once 'fixedFile/footer.php'; ?>