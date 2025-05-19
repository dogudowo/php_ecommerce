<?php require_once 'header.php'; ?>

<div class="container text-center border-bottom border-black">
    <img src="imgs/title.png" style="max-height: 100px;" alt="歐印精品" class="img-fluid">
    <h1 class="h1">品牌介紹</h1>
</div>

<div class="container text-center my-3 border-bottom border-black d-flex-column justify-content-center"
    id="companyIntro">
</div>

<div class="container text-center my-3 w-100">
    <h5>以下為歐印代理之品牌</h5>
    <ul class="nav nav-pills justify-content-center gap-3" id="brandTab" role="tablist"></ul>
    <div class="tab-content my-3" id="brandContent"></div>
</div>
<?php require_once 'footer.php'; ?>

<script>
    $(document).ready(function () {
        $.ajax({
            url: 'api/brand.php?action=getBrandInfo',
            method: 'GET',
            success: function (response) {
                // 1. 顯示公司介紹
                response.company_intro.forEach(paragraph => {
                    $('#companyIntro').append(`<p class="px-2 text-start mx-0">${paragraph.replace(/\n/g, '<br>')}</p>`);
                });

                // 2. 顯示品牌列表
                Object.keys(response.brands).forEach((brandId, index) => {
                    const brandData = response.brands[brandId];
                    $('#brandTab').append(`
                        <li class="nav-item">
                            <button class="nav-link ${index === 0 ? 'active' : ''}"
                                id="${brandId}-tab" data-bs-toggle="pill"
                                data-bs-target="#${brandId}Content" type="button" role="tab"
                                aria-controls="${brandId}Content" aria-selected="${index === 0}">
                                ${brandData.name}
                            </button>
                        </li>
                    `);

                    // 3. 顯示品牌內容
                    const logosHtml = brandData.logos.map(logo =>
                        `<img class="img" src="${logo.replace('../', '')}" alt="${brandId}" height="50vh">`
                    ).join('');

                    const imagesHtml = brandData.images.map(image =>
                        `<div class="col-md-4 mb-3 d-flex flex-column">
                            <img class="img-fluid rounded" src="${image.replace('../', '')}" alt="${brandId}最新資訊">
                         </div>`
                    ).join('');

                    $('#brandContent').append(`
                        <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" id="${brandId}Content" role="tabpanel">
                            <div class="container d-flex flex-wrap align-items-center justify-content-center gap-3">
                                ${logosHtml}
                            </div>
                            <div class="container text-start my-3 col-lg-9">
                                <p>${brandData.content.replace(/\n/g, '<br>')}</p>
                            </div>
                            <div class="container">
                                <h5 class="text-center">最新資訊</h5>
                                <div class="row">
                                    ${imagesHtml}
                                </div>
                            </div>
                        </div>
                    `);
                });
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
            }
        });
    });
</script>