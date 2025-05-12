<?php
require_once __DIR__ . '/../../../../api/db.php';

class StoreFunctions
{
    public static function getAllStores()
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM store");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getStoreById($tax_id)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM store WHERE tax_id = ?");
        $stmt->execute([$tax_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateStoreInfo($data)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE store SET name = ?, phone = ?, location = ?, opening_time = ?, url = ? WHERE tax_id = ?");
        return $stmt->execute([$data['name'], $data['phone'], $data['location'], $data['opening_time'], $data['url'], $data['tax_id']]);
    }

    public static function addStore($data)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO store (tax_id, name, phone, location, opening_time, url) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['tax_id'], $data['name'], $data['phone'], $data['location'], $data['opening_time'], $data['url']]);
    }

    public static function deleteStore($tax_id)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM store WHERE tax_id = ?");
        return $stmt->execute([$tax_id]);
    }
}
?>