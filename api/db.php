<?php
function db_connect()
{
    $config = require __DIR__ . '/../config.php';
    $db = $config['db'];

    try {
        $conn = new PDO(
            "mysql:host={$db['servername']};dbname={$db['dbname']}",
            $db['username'],
            $db['password']
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("連線失敗: " . $e->getMessage());
    }
}

function getDBConnection()
{
    static $conn = null;
    if ($conn === null) {
        $conn = db_connect();
    }
    return $conn;
}
?>