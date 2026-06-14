<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

LogService::visit('profile.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilePicture'])) {
    $file = $_FILES['profilePicture'];

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
    $targetPath = '/PROJET-ANNUEL_WEB-HUNTERS/assets/pp/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $userId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->execute([$filename, $userId]);

        echo "Upload successful!";
        header("Location: profile.php"); 
        exit;
    } else {
        echo "Failed to save the file.";
    }
}