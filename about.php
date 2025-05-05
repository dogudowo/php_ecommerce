<?php
require_once 'fixedFile/header.php';
require_once __DIR__ . '/models/frontend/StoreLocationModel.php';

$storeLocations = StoreLocationModel::getStoreLocations();
?>

<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png" alt="歐印精品" class="img-fluid">
    <h1 class="h1">關於我們</h1>
</div>
<div class="row justify-content-center my-3">
    <div class="col-auto d-flex align-items-center">
        <a href="https://line.me/R/ti/p/_idjyEv115" class="mx-3" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="3rem" height="3rem" fill="currentColor" class="bi"
                viewBox="0 0 512 512">
                <path
                    d="M311 196.8v81.3c0 2.1-1.6 3.7-3.7 3.7h-13c-1.3 0-2.4-.7-3-1.5l-37.3-50.3v48.2c0 2.1-1.6 3.7-3.7 3.7h-13c-2.1 0-3.7-1.6-3.7-3.7V196.9c0-2.1 1.6-3.7 3.7-3.7h12.9c1.1 0 2.4 .6 3 1.6l37.3 50.3V196.9c0-2.1 1.6-3.7 3.7-3.7h13c2.1-.1 3.8 1.6 3.8 3.5zm-93.7-3.7h-13c-2.1 0-3.7 1.6-3.7 3.7v81.3c0 2.1 1.6 3.7 3.7 3.7h13c2.1 0 3.7-1.6 3.7-3.7V196.8c0-1.9-1.6-3.7-3.7-3.7zm-31.4 68.1H150.3V196.8c0-2.1-1.6-3.7-3.7-3.7h-13c-2.1 0-3.7 1.6-3.7 3.7v81.3c0 1 .3 1.8 1 2.5c.7 .6 1.5 1 2.5 1h52.2c2.1 0 3.7-1.6 3.7-3.7v-13c0-1.9-1.6-3.7-3.5-3.7zm193.7-68.1H327.3c-1.9 0-3.7 1.6-3.7 3.7v81.3c0 1.9 1.6 3.7 3.7 3.7h52.2c2.1 0 3.7-1.6 3.7-3.7V265c0-2.1-1.6-3.7-3.7-3.7H344V247.7h35.5c2.1 0 3.7-1.6 3.7-3.7V230.9c0-2.1-1.6-3.7-3.7-3.7H344V213.5h35.5c2.1 0 3.7-1.6 3.7-3.7v-13c-.1-1.9-1.7-3.7-3.7-3.7zM512 93.4V419.4c-.1 51.2-42.1 92.7-93.4 92.6H92.6C41.4 511.9-.1 469.8 0 418.6V92.6C.1 41.4 42.2-.1 93.4 0H419.4c51.2 .1 92.7 42.1 92.6 93.4zM441.6 233.5c0-83.4-83.7-151.3-186.4-151.3s-186.4 67.9-186.4 151.3c0 74.7 66.3 137.4 155.9 149.3c21.8 4.7 19.3 12.7 14.4 42.1c-.8 4.7-3.8 18.4 16.1 10.1s107.3-63.2 146.5-108.2c27-29.7 39.9-59.8 39.9-93.1z" />
            </svg>
        </a>
        <a href="https://www.facebook.com/legendwalkertaiwan/" class="mx-3" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="3rem" height="3rem" fill="currentColor"
                class="bi bi-facebook" viewBox="0 0 16 16">
                <path
                    d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
            </svg>
        </a>
        <a href="https://www.instagram.com/all_en.boutique/" class="mx-3" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="3rem" height="3rem" fill="currentColor"
                class="bi bi-instagram" viewBox="0 0 16 16">
                <path
                    d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334" />
            </svg>
        </a>
        <a href="https://www.youtube.com/user/legendwalker6000" class="mx-3" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" width="3rem" height="3rem" fill="currentColor" class="bi bi-youtube"
                viewBox="0 0 16 16">
                <path
                    d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z" />
            </svg>
        </a>
    </div>
</div>

<div class="container my-4">
    <h3 class="text-decoration-underline text-center">官方FaceBook</h3>
    <a class="text-left" href="https://www.facebook.com/legendwalkertaiwan/">
        Legend Walker 台灣代理 歐印精品
    </a>
</div>

<div class="container my-4">
    <h3 class="text-decoration-underline text-center">客服電話 & 聯絡信箱</h3>
    <div class="container text-left">
        <p>客服電話：(04) 2291-4216(代表號)；(04) 2291-4226</p>
        <p>真：(04) 2292-2221</p>
        <p>詢問資訊/訂單相關：info@all-en.com.tw</p>
        <p>維修/換貨/退貨：lwfans@all-en.com.tw</p>
        <p>行銷企劃相關：lwworking@all-en.com.tw</p>
        <p class="text-primary">統一編號：53513578</p>
        <p class="text-primary">公司名稱：歐印興業有限公司</p>
        <hr>
        <p class="text-primary">統一編號：60392352</p>
        <p class="text-primary">公司名稱：歐奧特國際有限公司</p>
    </div>
</div>

<div class="container my-4">
    <ul class="nav nav-pills justify-content-center" id="brandTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="storeLoc-tab" data-bs-toggle="pill" data-bs-target="#storeLocContent"
                type="button" role="tab" aria-controls="storeLocContent" aria-selected="true">
                店面位置
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link"
                href="https://docs.google.com/forms/u/0/d/e/1FAIpQLSf5T8Jt46qCVAIgzvzLTv-wfvfqMOUX0Dl8VeS1Fnu_vxtysA/formResponse"
                target="_blank">
                廣告合作
            </a>
        </li>
    </ul>
    <div class="tab-content my-3">
        <div class="tab-pane fade show active" id="storeLocContent" role="tabpanel" aria-labelledby="storeLoc-tab">
            <div class="container">
                <?php foreach ($storeLocations as $store): ?>
                    <h3 class="text-decoration-underline text-center"><?php echo $store['name']; ?></h3>

                    <p class="text-left h6">服務時間</p>
                    <?php foreach ($store['service_time'] as $time): ?>
                        <p><?php echo $time; ?></p>
                    <?php endforeach; ?>

                    <?php foreach ($store['service_notice'] as $notice): ?>
                        <p class="text-danger"><?php echo $notice; ?></p>
                    <?php endforeach; ?>

                    <?php if (!empty($store['phone'])): ?>
                        <p class="h6">聯絡電話</p>
                        <p><?php echo $store['phone']; ?></p>
                    <?php endif; ?>

                    <?php if (!empty($store['address'])): ?>
                        <p class="h6">地址</p>
                        <p><?php echo $store['address']; ?></p>
                    <?php endif; ?>

                    <!-- Google Map 顯示 -->
                    <div class="ratio ratio-16x9">
                        <iframe src="<?php echo $store['map_url']; ?>" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>

                    <?php if (next($storeLocations)): ?>
                        <hr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'fixedFile/footer.php'; ?>