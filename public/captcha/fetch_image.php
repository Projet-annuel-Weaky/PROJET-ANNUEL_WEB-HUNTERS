<?php

ini_set("display_errors", "1");
error_reporting(E_ALL);

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

    $imagePath = __DIR__ . '/images/' . $selected['filename'];
    if (!file_exists($imagePath)) {
        logAction("fetch_image: File missing for id={$selected['id']} filename={$selected['filename']}");
        http_response_code(500);
        echo json_encode(["error" => "Captcha image file missing on server"]);
        exit();
    }

    $imageData = file_get_contents($imagePath);

    $_SESSION["captcha_image_id"] = (int) $selected["id"];
    $_SESSION["captcha_expected_order"] = [1, 2, 3, 4];
    logAction(
        "fetch_image: Served image id={$selected["id"]} filename={$selected["filename"]}.",
    );

    echo json_encode([
        "id" => $selected["id"],
        "filename" => $selected["filename"],
        "imageData" => base64_encode($imageData),
        "mimeType" => $selected["mime_type"],
    ]);
} catch (Exception $ex) {
    echo json_encode(["error" => $ex->getMessage()]);
    http_response_code(501);
}
