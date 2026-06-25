<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

define('ROOT', dirname(dirname(__DIR__)));
require_once __DIR__ . "/../../configs/config.php";
include_once "log.php";

header("Content-Type: application/json");

$_SESSION["captcha_valid"] = false;
unset(
    $_SESSION["captcha_validated_at"],
    $_SESSION["captcha_expected_order"],
    $_SESSION["captcha_image_id"],
);

try {
    $stmt = $pdo->prepare('
        SELECT id, filename, mime_type
        FROM captcha_images
        WHERE active = TRUE
        ORDER BY RAND()
        LIMIT 1
    ');
    $stmt->execute();
    $selected = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$selected) {
        logAction("fetch_image: No active captcha images found.");
        http_response_code(404);
        echo json_encode(["error" => "No active captcha images found"]);
        exit();
    }

    // Vérifier que le fichier existe bien sur le disque
    $filePath = ROOT . '/assets/captcha/' . $selected['filename'];
    if (!file_exists($filePath)) {
        logAction("fetch_image: File not found on disk for id={$selected['id']} filename={$selected['filename']}.");
        http_response_code(404);
        echo json_encode(["error" => "Image file not found on server"]);
        exit();
    }

    $_SESSION["captcha_image_id"] = (int) $selected["id"];
    $_SESSION["captcha_expected_order"] = [1, 2, 3, 4];
    $_SESSION["captcha_fetched_at"] = time();

    logAction("fetch_image: Served image id={$selected['id']} filename={$selected['filename']}.");

    // On renvoie l'URL relative au lieu du base64
    echo json_encode([
        "id"       => $selected["id"],
        "filename" => $selected["filename"],
        "imageUrl" => "/PROJET_ANNUEL/PROJET-ANNUEL_WEB-HUNTERS/assets/captcha/" . rawurlencode($selected["filename"]),
        "mimeType" => $selected["mime_type"],
    ]);
} catch (Exception $ex) {
    logAction("fetch_image: Exception: " . $ex->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $ex->getMessage()]);
}