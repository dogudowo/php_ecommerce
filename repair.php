<?php require_once 'header.php'; ?>

<div class="container mx-auto m-0 p-0">
    <div class="container text-center border-bottom border-black">
        <img src="imgs/title.png?t=<?php echo time(); ?>" style="max-height: 100px;" alt="歐印精品" class="img-fluid">
        <h1 class="h1">保固維修</h1>
    </div>
    <div class="container text-center mt-3">
        <a class="btn btn-outline-success" style="border-radius: 20px;" target="_blank""
            href=" imgs/repair/price/AllenRepairPrice0609.jpg?t=<?php echo time(); ?>">維修價格表</a>
        <?php
        $keyReportPath = '';
        $srcDir = __DIR__ . '/src/';
        if (is_dir($srcDir)) {
            $files = scandir($srcDir);
            foreach ($files as $file) {
                if (strpos($file, 'KeyReport') === 0) {
                    $keyReportPath = 'src/' . $file;
                    break;
                }
            }
        }
        ?>
        <a class="btn btn-outline-success" style="border-radius: 20px;" href="<?php echo $keyReportPath; ?>">鑰匙價格表</a>
    </div>

    <div class="container m-0 p-0">
        <ul class="nav nav-pills justify-content-end mt-2" id="brandTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active " id="TW-tab" data-bs-toggle="pill" data-bs-target="#TWContent"
                    type="button" role="tab" aria-controls="TWContent" aria-selected="true">中文</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="Eng-tab" data-bs-toggle="pill" data-bs-target="#EngContent" type="button"
                    role="tab" aria-controls="EngContent" aria-selected="false">English</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="TWContent" role="tabpanel" aria-labelledby="TW-tab">
                <!-- 中文內容 -->
                <div class="container w-100">
                    <div class="container my-3 d-flex justify-content-center">
                        <img src="imgs/repair/context-ch/repairContext.jpg?t=<?php echo time(); ?>" alt=""
                            class="img-fluid">
                    </div>

                    <h3>維修流程</h3>
                    <div class="container my-3 d-flex justify-content-center">
                        <img src="imgs/repair/context-ch/repairRundown.jpg?t=<?php echo time(); ?>" alt=""
                            class="img-fluid">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="EngContent" role="tabpanel" aria-labelledby="Eng-tab">
                <!-- 英文內容 -->
                <div class="container">
                    <div class="container my-3 d-flex justify-content-center">
                        <img src="imgs/repair/context-en/repairContextUS.jpg?t=<?php echo time(); ?>" alt=""
                            class="img-fluid">
                    </div>

                    <h3 class="text-decoration-underline">Repair Rundown</h3>
                    <div class="container my-3 d-flex justify-content-center">
                        <img src="imgs/repair/context-en/repairRundownEN.jpg?t=<?php echo time(); ?>" alt=""
                            class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>