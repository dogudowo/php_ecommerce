# public_html 專案說明

此資料夾為歐印精品官方網站的根目錄，部署於遠端伺服器上。以下是此專案的基本結構與用途說明：

## 📁 資料夾結構

public_html/
├── index.php          # 網站首頁
├── nav.php            # 導覽列 (Navbar)
├── footer.php         # 頁腳 (Footer)
├── information.php    # 購物說明頁面
├── introduce.php      # 品牌介紹頁面
├── repair.php         # 保固維修服務頁面
├── QandA.php          # 常見問題頁面
├── order.php          # 訂購表單頁面
├── process-order.php  # 訂單處理後端程式
├── imgs/              # 存放所有圖片資源
│ ├── title.png        # 網站標題圖片
│ ├── webimg.ico       # 網站favicon
│ └── ...              # 其他圖片資源
└── README.md          # 專案說明文件

## 🛠 技術架構

- **前端技術**:
  - Bootstrap 5.3.3 (CDN引入)
  - 響應式網頁設計 (RWD)
  - PHP 模板化設計 (使用 include 拆分元件)

- **後端技術**:
  - PHP 表單處理
  - 基本訂單處理功能

## 🚀 本地部署方式

1. 確認本專案位於 XAMPP 的 htdocs 目錄下
2. 啟動 XAMPP 控制面板
3. 開啟 Apache 服務
4. 在瀏覽器輸入 `http://localhost/public_html/` 即可預覽

## 🚀 遠端部署方式
1. Git Clone到個人資料夾下方
2. 打開瀏覽器，輸入104.134.53.57/~學號

## 📌 注意事項

1. 所有頁面均使用統一的導覽列(nav.php)和頁腳(footer.php)
2. 圖片資源請統一放置在 imgs 資料夾中
3. 表單提交後會導向 process-order.php 處理
4. 網站使用 Bootstrap 5 框架，請確保網路連線以載入 CDN 資源
