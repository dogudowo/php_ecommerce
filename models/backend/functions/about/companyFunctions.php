<?php
require_once __DIR__ . '/../../../../api/db.php';

class CompanyFunctions
{
    public static function addCompany($tax_id, $name, $email = null, $phone = null, $fax = null)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("INSERT INTO company (tax_id, name, email, phone, fax) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$tax_id, $name, $email, $phone, $fax]);
    }

    public static function getCompanyById($tax_id)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare('SELECT * FROM company WHERE tax_id = :tax_id LIMIT 1');
        $stmt->bindParam(':tax_id', $tax_id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function getCompanyInfo()
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM company");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateCompanyInfo($data)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE company SET name = ?, email = ?, phone = ?, fax = ? WHERE tax_id = ?");
        return $stmt->execute([$data['name'], $data['email'], $data['phone'], $data['fax'], $data['tax_id']]);
    }

    public static function deleteCompany($tax_id)
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM company WHERE tax_id = ?");
        return $stmt->execute([$tax_id]);
    }
}
?>