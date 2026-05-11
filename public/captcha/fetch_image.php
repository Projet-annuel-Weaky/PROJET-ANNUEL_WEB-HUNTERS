<?php

ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once __DIR__ . "/../../configs/config.php";

header("Content-Type: application/json");

$_SESSION["captcha_valid"] = false;
unset(
    $_SESSION["captcha_validated_at"],
    $_SESSION["captcha_expected_order"],
    $_SESSION["captcha_image_id"],
);

try {
    $stmt = $pdo->prepare('
        SELECT id, filename, image_data, mime_type
        FROM captcha_images
        WHERE active = TRUE AND image_data IS NOT NULL
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

    $_SESSION["captcha_image_id"] = (int) $selected["id"];
    $_SESSION["captcha_expected_order"] = [1, 2, 3, 4];
    logAction(
        "fetch_image: Served image id={$selected["id"]} filename={$selected["filename"]}.",
    );

    echo json_encode([
        "id" => $selected["id"],
        "filename" => $selected["filename"],
        "imageData" => base64_encode($selected["image_data"]),
        "mimeType" => $selected["mime_type"],
    ]);
} catch (Exception $ex) {
    //logAction("fetch_image: Exception: " . $ex->getMessage());
    echo json_encode(["error" => $ex->getMessage()]);
    http_response_code(501);
}
