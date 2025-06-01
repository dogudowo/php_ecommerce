<?php
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

/* ---------- 公用工具 ---------- */
function db()
{
    // 兼容 getDBConnection / getDbConnection 兩種命名
    if (function_exists('getDBConnection')) {
        return getDBConnection();
    }
    return getDbConnection();
}

function jsonExit(array $payload, int $status = 200)
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

/* ---------- 路由 ---------- */
try {
    switch ("$method:$action") {
        case 'GET:list':
            listMembers();
            break;

        case 'GET:get':
            getMemberDetails($_GET['id'] ?? null);
            break;

        case 'POST:update':
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            updateMember($data);
            break;

        case 'POST:delete':      // 透過 POST 傳 JSON 進行刪除
        case 'DELETE:':          // or 真正的 HTTP DELETE
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
            deleteMember($data);
            break;

        default:
            jsonExit(['success' => false, 'message' => '無效的 action 或 HTTP 方法'], 405);
    }
} catch (Throwable $e) {
    error_log('MemberBackend error: ' . $e->getMessage());
    jsonExit(['success' => false, 'message' => '系統錯誤，請稍後再試'], 500);
}

/* ---------- 功能實作 ---------- */

/**
 * 取得會員清單 (含訂單筆數)
 */
function listMembers(): void
{
    $pdo = db();

    $city = $_GET['city'] ?? null;
    $type = $_GET['type'] ?? null;
    $search = $_GET['search'] ?? null;

    $where = [];
    $params = [];

    if ($city) {
        $where[] = "m.City LIKE :city";
        $params['city'] = "%$city%";
    }

    if ($type !== null) {
        $where[] = "m.IsAdmin = :type";
        $params['type'] = (int) $type;
    }

    if ($search) {
        $where[] = "(m.Name LIKE :search OR m.Email LIKE :search OR m.Phone LIKE :search)";
        $params['search'] = "%$search%";
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT 
                m.MemberID,
                m.Name,
                m.Email,
                m.Phone,
                m.City,
                m.Address,
                m.IsAdmin,
                (SELECT COUNT(*) FROM Orders o WHERE o.MemberID = m.MemberID) AS OrderCount
            FROM Member m
            $whereClause
            ORDER BY m.MemberID ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonExit([
        'success' => true,
        'members' => $members,
    ]);
}


function getMemberDetails(?string $memberId): void
{
    if (!$memberId) {
        jsonExit(['success' => false, 'message' => '未提供會員ID'], 400);
    }

    $pdo = db();
    $sql = "SELECT MemberID, Name, Email, Phone, City, Address, IsAdmin
            FROM Member
            WHERE MemberID = :memberId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['memberId' => $memberId]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member) {
        jsonExit(['success' => true, 'member' => $member]);
    }
    jsonExit(['success' => false, 'message' => '找不到指定的會員'], 404);
}


function updateMember(array $data): void
{
    if (empty($data['memberId'])) {
        jsonExit(['success' => false, 'message' => '缺少會員ID'], 400);
    }
    $memberId = (int) $data['memberId'];
    $isAdmin = isset($data['isAdmin']) ? (int) $data['isAdmin'] : 0;

    $pdo = db();

    // 檢查會員是否存在
    $stmt = $pdo->prepare('SELECT 1 FROM Member WHERE MemberID = ?');
    $stmt->execute([$memberId]);
    if (!$stmt->fetch()) {
        jsonExit(['success' => false, 'message' => '找不到會員資料'], 404);
    }

    $stmt = $pdo->prepare('UPDATE Member SET IsAdmin = :isAdmin WHERE MemberID = :memberId');
    if ($stmt->execute(['isAdmin' => $isAdmin, 'memberId' => $memberId])) {
        jsonExit(['success' => true, 'message' => '會員資料已成功更新']);
    }
    jsonExit(['success' => false, 'message' => '更新會員資料失敗'], 500);
}

function deleteMember(array $data): void
{
    if (empty($data['memberId'])) {
        jsonExit(['success' => false, 'message' => '未提供會員ID'], 400);
    }

    $memberId = (int) $data['memberId'];
    $pdo = db();

    try {
        $pdo->beginTransaction();

        /* 1. 刪 CartItem / cart */
        $pdo->prepare('DELETE FROM CartItem WHERE MemberID = ?')->execute([$memberId]);
        // cart 表若不存在則忽略
        try {
            $pdo->prepare('DELETE FROM cart WHERE MemberID = ?')->execute([$memberId]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), "doesn't exist") === false) {
                throw $e; // 不是表不存在就往外丟
            }
        }

        /* 2. 找出所有訂單 */
        $stmt = $pdo->prepare('SELECT OrderID FROM Orders WHERE MemberID = ?');
        $stmt->execute([$memberId]);
        $orders = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($orders) {
            $orderIn = implode(',', array_fill(0, count($orders), '?'));

            /* 3. 找收據 */
            $stmt = $pdo->prepare("SELECT ReceiptID FROM Receipt WHERE OrderID IN ($orderIn)");
            $stmt->execute($orders);
            $receipts = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($receipts) {
                $receiptIn = implode(',', array_fill(0, count($receipts), '?'));

                /* 4. 找保固 */
                $stmt = $pdo->prepare("SELECT WarrantyID FROM ReceiptItem WHERE ReceiptID IN ($receiptIn)");
                $stmt->execute($receipts);
                $warranties = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if ($warranties) {
                    $warrantyIn = implode(',', array_fill(0, count($warranties), '?'));

                    // 5. 刪維修
                    $pdo->prepare("DELETE FROM Repairs WHERE WarrantyID IN ($warrantyIn)")->execute($warranties);
                    // 6. 刪保固
                    $pdo->prepare("DELETE FROM Warranty WHERE WarrantyID IN ($warrantyIn)")->execute($warranties);
                }

                // 7. 刪收據項目
                $pdo->prepare("DELETE FROM ReceiptItem WHERE ReceiptID IN ($receiptIn)")->execute($receipts);
                // 8. 刪收據
                $pdo->prepare("DELETE FROM Receipt WHERE ReceiptID IN ($receiptIn)")->execute($receipts);
            }

            /* 9. 刪訂單項目 */
            $pdo->prepare("DELETE FROM OrderItem WHERE OrderID IN ($orderIn)")->execute($orders);

            /* 9.5 刪除 OrderRecord (如果存在) */
            $pdo->prepare("DELETE FROM OrderRecord WHERE OrderID IN ($orderIn)")->execute($orders);

            /* 10. 刪訂單 */
            $pdo->prepare("DELETE FROM Orders WHERE OrderID IN ($orderIn)")->execute($orders);
        }

        /* 11. 刪會員本身 */
        $pdo->prepare('DELETE FROM Member WHERE MemberID = ?')->execute([$memberId]);

        $pdo->commit();
        jsonExit(['success' => true, 'message' => '會員及相關資料已成功刪除']);
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Delete member error: ' . $e->getMessage());
        jsonExit(['success' => false, 'message' => '刪除會員失敗'], 500);
    }
}