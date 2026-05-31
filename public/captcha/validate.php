<?php
require_once __DIR__ . "/../../configs/config.php";
include_once "log.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit();
}

$imageId = isset($data["image_id"]) ? (int) $data["image_id"] : 0;
$order = $data["order"] ?? [];
$expectedOrder = $_SESSION["captcha_expected_order"] ?? [];
$expectedImageId = $_SESSION["captcha_image_id"] ?? 0;

$_SESSION["captcha_valid"] = false;
unset($_SESSION["captcha_validated_at"]);

if (
    $imageId <= 0 ||
    $imageId !== (int) $expectedImageId ||
    !is_array($order) ||
    count($order) !== 4 ||
    count($expectedOrder) !== 4
) {
    http_response_code(400);
    echo json_encode(["success" => false]);
    exit();
}

$cleanOrder = array_map("intval", array_values($order));
$cleanExpectedOrder = array_map("intval", array_values($expectedOrder));

if ($cleanOrder === $cleanExpectedOrder) {
    $_SESSION["captcha_valid"] = true;
    $_SESSION["captcha_validated_at"] = time();
    echo json_encode(["success" => true]);
    exit();
}

echo json_encode(["success" => false]);
