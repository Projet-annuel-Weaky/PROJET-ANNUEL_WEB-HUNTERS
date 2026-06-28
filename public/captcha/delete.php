<?php
require_once __DIR__ . "/../../configs/config.php";
require_once __DIR__ . "/../../src/services/AdminService.php";
include_once "log.php";

AdminService::requireAdmin("../login.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["id"])) {
    $id = intval($_POST["id"]);

    $stmt = $pdo->prepare("SELECT filename FROM captcha_images WHERE id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $filePath = ROOT . '/assets/captcha/' . $row['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
            logAction("delete: Deleted file {$row['filename']} from disk.");
        } else {
            logAction("delete: File {$row['filename']} not found on disk (already removed?).");
        }
    }

    $sql = "DELETE FROM captcha_images WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();

    logAction("delete: Deleted image id=$id from database.");
}

header("Location: ../manage_captcha.php");
exit();
