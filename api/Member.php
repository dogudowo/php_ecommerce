<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// 只在直接訪問時設置 JSON 頭，而不是在被引入時
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
}

function getMemberByID(int $memberId, ?PDO $pdo = null): array
{
    $pdo = $pdo ?? db_connect();
    $stmt = $pdo->prepare("SELECT Name as membername, Phone as phonenum, Email as memberemail, City, Address, IsAdmin as role FROM Member WHERE MemberID = :memberId");
    $stmt->execute(['memberId' => $memberId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

function getMemberOrders(int $memberId, ?PDO $pdo = null): array
{
    $pdo = $pdo ?? db_connect();
    $sql = <<<SQL
        SELECT  o.OrderID,
                o.OrderDate,
                o.Status,
                SUM(oi.Quantity * opt.Price) AS TotalAmount
        FROM    Orders o
        JOIN    OrderItem   oi  ON o.OrderID  = oi.OrderID
        JOIN    Options     opt ON oi.OptionID = opt.OptionID
        WHERE   o.MembersID = :memberId
        GROUP BY o.OrderID, o.OrderDate, o.Status
        ORDER BY o.OrderDate DESC
    SQL;
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['memberId' => $memberId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>