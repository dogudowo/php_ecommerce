<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/error_handler.php';
class StoreLocationModel
{
    public static function getCompanyInfo()
    {
        $db = getDBConnection();
        $query = "SELECT tax_id, name, email, phone, fax FROM company";
        $stmt = $db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getStoreLocations()
    {
        $db = getDBConnection();
        $query = "SELECT * FROM store";
        $stmt = $db->prepare($query);
        $stmt->execute();

        $locations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $locations[] = [
                'name' => $row['name'],
                'service_time' => [$row['opening_time']],
                'phone' => $row['phone'],
                'address' => $row['location'],
                'map_url' => $row['url']
            ];
        }

        return $locations;
    }
}
?>