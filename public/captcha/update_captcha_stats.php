<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

$allowed_actions = [
    'reseted' => 'reseted',
    'completed' => 'completed',
    'failed' => 'failed'
];

if ($id <= 0 || !isset($allowed_actions[$action])) {
    logAction("update_captcha_stats: Invalid parameters id=$id action=$action.");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $column = $allowed_actions[$action];
    $stmt = $pdo->prepare("UPDATE captcha_images SET $column = $column + 1 WHERE id = :id");
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        logAction("update_captcha_stats: Image not found id=$id action=$action.");
        http_response_code(404);
        echo json_encode(['error' => 'Captcha image not found']);
        exit;
    }

    logAction("update_captcha_stats: Action '$action' recorded for id=$id.");
    echo json_encode(['success' => true]);
} catch (Exception $ex) {
    logAction('update_captcha_stats: Exception: ' . $ex->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
}