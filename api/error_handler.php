<?php
// 設定自訂錯誤日誌路徑
// 由於此檔案位於 api 資料夾下，.logs 資料夾在其上一層
ini_set('error_log', __DIR__ . '/../.logs/error_log');
// 確保錯誤會被記錄到檔案
ini_set('log_errors', 'On');
// 不在瀏覽器顯示錯誤，避免洩漏路徑等資訊
ini_set('display_errors', 'Off');
error_reporting(E_ALL);

// 測試錯誤日誌功能
// trigger_error('這是一條測試錯誤訊息 - 錯誤日誌功能測試', E_USER_NOTICE);
?>