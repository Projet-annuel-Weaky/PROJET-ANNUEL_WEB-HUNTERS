<?php
require_once __DIR__ . "/../../configs/config.php";
require_once __DIR__ . "/../../src/services/AdminService.php";
include_once "log.php";

AdminService::requireAdmin("../login.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["id"])) {
    $id = intval($_POST["id"]);

    // Fetch filename and remove file from disk if present
    $stmt = $pdo->prepare("SELECT filename FROM captcha_images WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $filePath = __DIR__ . '/images/' . $row['filename'];
        if (file_exists($filePath)) {
            @unlink($filePath);
            logAction("delete: Removed file $filePath for id=$id.");
        } else {
            logAction("delete: File not found $filePath for id=$id.");
        }
    }

    $sql = "DELETE FROM captcha_images WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    logAction("delete: Deleted image id=$id.");
}

header("Location: ../manage_captcha.php");
exit();
