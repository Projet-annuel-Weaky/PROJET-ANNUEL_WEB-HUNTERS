<?php

class LogService
{
    public static function add($action, $page, $idUser = null)
    {
        global $pdo;

        if (!$pdo) {
            return;
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if ($userAgent !== null) {
            $userAgent = substr($userAgent, 0, 255);
        }

        $stmt = $pdo->prepare('INSERT INTO logs (id_user, action, page, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $idUser,
            $action,
            $page,
            $ipAddress,
            $userAgent
        ]);
    }

    public static function visit($page)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            self::add('visit', $page, $_SESSION['id_user'] ?? null);
        }
    }
}
