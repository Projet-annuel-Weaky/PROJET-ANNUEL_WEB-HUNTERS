<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");

require_once CONFIG . "/config.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["id_user"])) {
    http_response_code(401);
    echo json_encode(["error" => "Non authentifié"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $lastId = (int) ($_GET["last_id"] ?? 0);

    $stmt = $pdo->prepare("
        SELECT m.id_message, m.id_user, m.message, m.created_at, u.username
        FROM chat_messages m
        JOIN users u ON u.id_user = m.id_user
        WHERE m.id_message > :last_id
        ORDER BY m.id_message ASC
        LIMIT 100
    ");
    $stmt->execute([":last_id" => $lastId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["messages" => $messages]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $body = json_decode(file_get_contents("php://input"), true);
    $message = trim($body["message"] ?? "");

    if ($message === "") {
        http_response_code(422);
        echo json_encode(["error" => "Message vide"]);
        exit;
    }

    if (mb_strlen($message) > 1000) {
        http_response_code(422);
        echo json_encode(["error" => "Message trop long (1000 caractères max)"]);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO chat_messages (id_user, message, created_at)
        VALUES (:id_user, :message, NOW())
    ");
    $stmt->execute([
        ":id_user" => (int) $_SESSION["id_user"],
        ":message" => $message,
    ]);

    $messageId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare("
        SELECT m.id_message, m.id_user, m.message, m.created_at, u.username
        FROM chat_messages m
        JOIN users u ON u.id_user = m.id_user
        WHERE m.id_message = :id_message
        LIMIT 1
    ");
    $stmt->execute([":id_message" => $messageId]);
    $created = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["message" => $created]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Méthode non autorisée"]);
