<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define("DB_HOST", getenv("DB_HOST") ?: "");
define("DB_USER", getenv("DB_USER") ?: "");
define("DB_PASSWORD", getenv("DB_PASSWORD") ?: "");
define("DB_NAME", getenv("DB_NAME") ?: "");
define("DB_PORT", getenv("DB_PORT") ?: "3306");

define("MAIL_FROM_EMAIL", getenv("MAIL_FROM_EMAIL") ?: "");
define("MAIL_FROM_NAME", getenv("MAIL_FROM_NAME") ?: "");

define("SITE_NAME", "");
define("SITE_DESCRIPTION", "");
define(
    "SITE_URL",
    getenv("SITE_URL") ?:
    (getenv("APP_ENV") === "prod"
        ? "https://"
        : "http://localhost"),
);

$pdo = null;

try {
    $pdo = new PDO(
        "mysql:host=" .
            DB_HOST .
            ";port=" .
            DB_PORT .
            ";dbname=" .
            DB_NAME .
            ";charset=utf8mb4",
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    );
} catch (PDOException $e) {
    $pdo = null;
    error_log("Erreur de connexion BDD : " . $e->getMessage());
}
