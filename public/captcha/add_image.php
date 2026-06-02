<?php
require_once __DIR__ . "/../../configs/config.php";
require_once __DIR__ . "/../../src/services/AdminService.php";
include_once "log.php";

AdminService::requireAdmin("../login.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["image"])) {
    $file = $_FILES["image"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        logAction("add_image: Upload error code={$file["error"]}.");
        die("Upload error: " . $file["error"]);
    }

    $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file["tmp_name"]);

    if (
        !in_array(strtolower($ext), ["jpg", "jpeg", "png", "gif"]) ||
        !in_array($mimeType, ["image/jpeg", "image/png", "image/gif"])
    ) {
        logAction("add_image: Invalid file type ext=$ext mime=$mimeType.");
        die("Invalid file type. Only JPG, PNG, GIF allowed.");
    }

    $imageDir = __DIR__ . '/images';
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0755, true);
    }

    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file["name"]));
    $targetPath = $imageDir . '/' . $filename;

    if (file_exists($targetPath)) {
        logAction("add_image: File exists, overwriting $targetPath");
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['captcha_warn'] = "Le fichier {$filename} existe déjà, il sera écrasé.";
    }

    if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
        logAction("add_image: Failed to move uploaded file to $targetPath");
        die("Failed to save uploaded file.");
    }

    $stmt = $pdo->prepare(
        "INSERT INTO captcha_images (filename, mime_type, active, created_at) VALUES (?, ?, 1, NOW())"
    );
    $stmt->execute([$filename, $mimeType]);

    logAction("add_image: Inserted image filename=$filename mime=$mimeType.");
    header("Location: ../manage_captcha.php");
    exit();
} else {
    logAction("add_image: Invalid request method or missing file.");
    die("Invalid request.");
}
