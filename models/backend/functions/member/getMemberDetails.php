<?php
require_once __DIR__ . '/memberFunction.php';

header('Content-Type: application/json');

// 檢查請求方法
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不支援的請求方法']);
    exit;
}

// 檢查是否提供會員ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '缺少會員ID']);
    exit;
}

$memberId = intval($_GET['id']);

try {
    // 使用 memberFunction.php 中的 getMemberDetails 函數獲取會員資料
    $member = getMemberDetails($memberId);

    if ($member === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '找不到會員資料']);
        exit;
    }

    // 返回會員資料
    echo json_encode(['success' => true, 'member' => $member]);
    exit;
} catch (Exception $e) {
    // 記錄錯誤
    error_log('getMemberDetails.php', $e->getMessage());

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '系統錯誤，請稍後再試']);
}