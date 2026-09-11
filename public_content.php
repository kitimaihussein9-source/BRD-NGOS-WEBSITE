<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $database = db();
    $statement = $database->query('SELECT type, title, details, image_path, video_path, created_at FROM content ORDER BY id DESC LIMIT 6');
    echo json_encode(['success' => true, 'items' => $statement->fetchAll()]);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'items' => []]);
}
