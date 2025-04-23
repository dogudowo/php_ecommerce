<?php
function db_connect()
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "D1256977"; // 資料庫名稱

    // 建立連線
    $conn = new mysqli($servername, $username, $password, $dbname);

    // 檢查連線
    if ($conn->connect_error) {
        die("連線失敗: " . $conn->connect_error);
    }

    return $conn;
}
?>