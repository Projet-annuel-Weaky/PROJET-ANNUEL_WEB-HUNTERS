<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

LogService::visit('profile.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];

    $userId = $_SESSION['id_user'] ?? $_SESSION['id'] ?? null;
    if (!$userId) {
        die("User not logged in.");
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 2 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload error.");
    }
    if (!in_array($file['type'], $allowedTypes)) {
        die("Invalid file type.");
    }
    if ($file['size'] > $maxSize) {
        die("File too large.");
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('pp_', true) . '.' . $ext;

    $uploadDir = ROOT . '/assets/pp/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $targetPath = $uploadDir . $filename;

    $oldAvatar = $_SESSION['avatar'] ?? null;
    if ($oldAvatar && $oldAvatar !== 'DEFAULT_pp.png') {
    $oldPath = $uploadDir . $oldAvatar;
    if (file_exists($oldPath)) {
        unlink($oldPath);
        }
    }       

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id_user = ?");
        $stmt->execute([$filename, $userId]);

        $_SESSION['avatar'] = $filename;

        header("Location: profile.php");
        exit;
    } else {
        die("Failed to save the file.");
    }
}