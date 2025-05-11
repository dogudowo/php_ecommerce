# 歐印精品購物網站

## 安裝說明
### 遠端伺服器部署
1. 將專案複製到個人資料夾下
2. 使用make設定專案環境
    - 複製 config.example.php 並命名為 config.php
    - 設定資料夾權限
3. 確定資料庫設置正確
4. 開啟瀏覽器輸入遠端伺服器網址

### 本地部署
1. 安裝 XAMPP
2. 將專案複製到 htdocs 資料夾
3. 使用make設定專案環境
    - 複製 config.example.php 並命名為 config.php
    - 設定資料夾權限
4. 確定資料庫設置正確
5. 開啟 XAMPP 並啟動 Apache 和 MySQL
6. 開啟瀏覽器輸入 localhost

## MakeFile 指令
- make help: 顯示所有指令
- make config: 複製 config.example.php 並命名為 config.php
- make set_permission: 設定資料夾權限
- make update: 更新專案
    