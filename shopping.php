<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <link rel="stylesheet" href="css/navPillsStyles.css">
    <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
    <style>
        p {
            text-indent: 2rem;
        }
    </style>
    <title>歐印精品-購物說明</title>
</head>

<body>
    <?php include "header.php"; ?>
    <div class="container text-center border-bottom border-black my-3">
        <img src="imgs/title.png" alt="歐印精品" img-fluid>
        <h1 class="h1">購物說明</h1>
    </div>

    <div class="container">
        <ul class="nav nav-pills justify-content-center gap-3" id="brandTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="shopping-tab" data-bs-toggle="pill"
                    data-bs-target="#shoppingContent" type="button" role="tab" aria-controls="shoppingContent"
                    aria-selected="true">
                    購物說明
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="qa-tab" data-bs-toggle="pill" data-bs-target="#qaContent" type="button"
                    role="tab" aria-controls="qaContent" aria-selected="false">購物Q&A</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="process-tab" data-bs-toggle="pill" data-bs-target="#processContent"
                    type="button" role="tab" aria-controls="processContent" aria-selected="false">
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
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                您可以透過哪些管道購買到歐印的商品？
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse collapsed" aria-labelledby="headingOne"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>我們建構通暢的購買通路，您可以透過以下方式訂購商品：</p>
                                <p>1. 台中敦化旗艦店、南投半山夢工廠、新竹西大路大遠百、烏日倉儲</p>
                                <p>2. 歐印官網(<a href="index.php">www.all-en.com.tw</a>)</p>
                                <p>3.購物平台，如：7-11、pchome、蝦皮、Google購物、臉書購物等平台</p>
                                <p>4. 特約商店服務展示</p>
                                <p>5.百貨公司不定期快閃活動</p>
                                <p>6.不定期展覽</p>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                如何取得團購特約商的活動？
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                您可以在歐印官網(https://www.all-en.com.tw/company.php)查詢所屬或家人的公司、學校是否為特約商或是聯盟商店，直接在官網下單須先向所屬公司福委取得折扣碼
                                於結帳時輸入折扣碼即可享有團購優惠最低價；如果還沒簽訂特約，趕緊來電(04-22914216)洽詢簽約事宜哦！
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                完成訂購後多久可收到商品呢?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                一般不缺貨的狀況約2~4天即可收到商品。若無現貨商品，網頁會顯示＊號，依到櫃時排單順序出貨(*號下方也會顯示預計供貨期)。 官網上沒有的顏色表示不供貨囉！
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                什麼時候有貨？若排單須等待多久呢?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingThree"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                訂購順序即為排單的依據，如果您急於使用，可以備註說明，我們會協調供貨順序(例如有些客戶現在訂，但一個月後才使用)，我們即可協調供貨順序喔！</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                收到貨後發現有瑕疵該如何反應給歐印呢？
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingThree"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">請您將瑕疵處拍照後加入line官方:https://lin.ee/upPxrJS
                                上班時間將有專人與您聯繫，若判定為瑕疵，我們會協助您換貨，不需費用，敬請放心！</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                商品售後服務如何處理
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingThree"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">您可以(DIY更換配件) 請上官網訂購
                                https://www.all-en.com.tw/otherrow_n.php?tp=1 (其他維修事項)請加入line官方: https://lin.ee/upPxrJS
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                如何加入聯盟商店或是策略行銷廣告服務
                            </button>
                        </h2>
                        <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingThree"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">請寄信至公司 info@all-en.com.tw 將有專人服務!!</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                客訴問題反映
                            </button>
                        </h2>
                        <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingThree"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">請來電0939653911 客服經理Janet</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
                                若收到產品發現有刮傷？
                            </button>
                        </h2>
                        <div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingThree"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                請傳照片到line官方帳號:@legendwalker，以利我們判斷。唯箱殼製程，非無塵室作業，且因非精密儀器設備，恕難達到完全無刮傷。敬請見諒</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade btn" id="processContent" role="tabpanel" aria-labelledby="process-tab">
                <!-- 購物流程內容 -->
                <img src="imgs/shopping/order.jpg" alt="" class="img-fulid" width="100%" />
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ"
        crossorigin="anonymous"></script>
</body>

</html>