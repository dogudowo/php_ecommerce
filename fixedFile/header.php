<?php require_once __DIR__ . '/../api/header.php'; ?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"
        integrity="sha256-J8ay84czFazJ9wcTuSDLpPmwpMXOm573OUtZHPQqpEU=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="icon" href="imgs/webimg.ico" type="image/x-icon">
    <title><?= getPageTitle() ?></title>
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .container-fulid {
            flex: 1 0 auto;
        }

        footer {
            flex-shrink: 0;
        }

        .nav-pills .nav-link {
            color: rgb(25, 135, 84);
            background-color: white;
            transition: background-color 0.3s, color 0.3s;
        }

        /* 活動（active）狀態 */
        .nav-pills .nav-link.active {
            color: #fff;
            background-color: rgb(25, 135, 84);
            /* 自訂的 active 背景色 */
        }

        .nav-pills .nav-link img {
            vertical-align: middle;
        }

        /* 全域隱藏滾動條，但仍可滑動 */
        html,
        body {
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE 10+ */
        }

        /* Chrome、Edge、Safari */
        html::-webkit-scrollbar,
        body::-webkit-scrollbar {
            display: none;
        }
    </style>
    <?php outputProductTitleScript(); ?>
</head>

<body>
    <div class="fixed-top bg-white m-0 border-bottom border-2">
        <nav class="container position-relative">
            <!-- 電腦版導航 (xl以上) -->
            <div class="d-none d-xl-flex flex-wrap align-items-center justify-content-between py-2 m-0">
                <a href="index.php" class="d-flex align-items-center mb-0 text-dark text-decoration-none">
                    <img src="imgs/title.png" alt="" height="49">
                </a>

                <ul class="nav justify-content-center mb-xl-0 gap-2">
                    <?php renderNavItems(getNavItems(), ''); ?>
                </ul>

                <div class="text-center gap-2 d-flex flex-row flex-nowrap">
                    <?php renderUserControls(false, false); ?>
                </div>
            </div>

            <!-- 移動版導航 (xl以下) -->
            <div class="d-flex d-xl-none flex-wrap align-items-center justify-content-between py-2 m-0">
                <button class="btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNavHeader"
                    aria-controls="collapseNavHeader" aria-expanded="false">
                    <span class="navbar-toggler-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                            class="bi bi-list" viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" />
                        </svg>
                    </span>
                </button>

                <a href="index.php" class="d-flex align-items-center mb-0 text-dark text-decoration-none">
                    <img src="imgs/title.png" alt="" height="49">
                </a>

                <div class="text-center gap-2">
                    <?php renderUserControls(true, false); ?>
                </div>
            </div>
        </nav>

        <div class="collapse bg-white border-top" id="collapseNavHeader">
            <div class="container p-2 m-0">
                <ul class="navbar-nav nav-pills flex-column mb-auto">
                    <?php renderNavItems(getNavItems(), 'mb-2 mx-4'); ?>
                </ul>
                <div class="d-grid gap-2 mb-3">
                    <?php renderUserControls(false, true); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fulid pt-5 mt-3 flex-grow-1">