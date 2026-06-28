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
    ');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        logAction("fetch_image: No active captcha images found.");
        http_response_code(404);
        echo json_encode(["error" => "No active captcha images found"]);
        exit();
    }

    $selected = null;
    $rawImage = null;
    $resolvedMime = null;
    foreach ($rows as $row) {
        $filePath = ROOT . '/assets/captcha/' . $row['filename'];
        if (file_exists($filePath) && is_readable($filePath)) {
            $raw = file_get_contents($filePath);
            if ($raw === false || $raw === '') {
                logAction("fetch_image: Skipped unreadable file id={$row['id']} filename={$row['filename']}.");
                continue;
            }
            $selected = $row;
            $rawImage = $raw;
            $resolvedMime = $row["mime_type"] ?: "image/jpeg";
            if (class_exists("finfo")) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $detected = $finfo->buffer($raw);
                if ($detected) {
                    $resolvedMime = $detected;
                }
            }
            break;
        }
        logAction("fetch_image: Skipped missing file id={$row['id']} filename={$row['filename']}.");
    }

    if (!$selected) {
        logAction("fetch_image: No usable captcha image found on server.");
        http_response_code(404);
        echo json_encode(["error" => "No usable captcha image found"]);
        exit();
    }

    $_SESSION["captcha_image_id"] = (int) $selected["id"];
    $_SESSION["captcha_expected_order"] = [1, 2, 3, 4];
    $_SESSION["captcha_fetched_at"] = time();

    logAction("fetch_image: Served image id={$selected['id']} filename={$selected['filename']}.");

    echo json_encode([
        "id"       => $selected["id"],
        "filename" => $selected["filename"],
        "imageData" => base64_encode($rawImage),
        "mimeType" => $resolvedMime,
    ]);
} catch (Exception $ex) {
    logAction("fetch_image: Exception: " . $ex->getMessage());
    http_response_code(500);
    echo json_encode(["error" => $ex->getMessage()]);
}
