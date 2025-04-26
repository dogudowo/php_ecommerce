<?php
function db_connect()
{
    $config = require __DIR__ . '/config.php';
    $db = $config['db'];

    // 建立連線
    $conn = new mysqli($db['servername'], $db['username'], $db['password'], $db['dbname']);

    // 檢查連線
    if ($conn->connect_error) {
        die("連線失敗: " . $conn->connect_error);
    }

    return $conn;
}
?>