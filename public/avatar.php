<?php
define("ROOT", dirname(__DIR__));

$baseDir = ROOT . "/assets/pp/";
$defaultFile = "DEFAULT_pp.png";

$requested = isset($_GET["file"]) ? basename((string) $_GET["file"]) : $defaultFile;
if ($requested === "" || $requested === "." || $requested === "..") {
    $requested = $defaultFile;
}

$baseReal = realpath($baseDir);
$candidatePath = $baseDir . $requested;
$candidateReal = realpath($candidatePath);

$filePath = "";
if (
    $baseReal !== false &&
    $candidateReal !== false &&
    str_starts_with($candidateReal, $baseReal . DIRECTORY_SEPARATOR) &&
    is_file($candidateReal)
) {
    $filePath = $candidateReal;
} else {
    $defaultPath = $baseDir . $defaultFile;
    $defaultReal = realpath($defaultPath);
    if ($defaultReal === false || !is_file($defaultReal)) {
        http_response_code(404);
        exit;
    }
    $filePath = $defaultReal;
}

$mimeType = "image/png";
if (class_exists("finfo")) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $detected = $finfo->file($filePath);
    if (is_string($detected) && $detected !== "") {
        $mimeType = $detected;
    }
}

header("Content-Type: " . $mimeType);
header("Cache-Control: public, max-age=300");
readfile($filePath);
