<?php
require_once __DIR__ . '/APIs.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['carouselImage'])) {
        if (addCarouselImage($_FILES['carouselImage'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => '上傳失敗']);
        }
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data['action'] === 'delete') {
            if (deleteCarouselImage($data['imageName'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => '刪除失敗']);
            }
        }
    }
}
?>