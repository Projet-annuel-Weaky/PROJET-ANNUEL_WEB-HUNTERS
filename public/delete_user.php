<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");
require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['id_user'] ?? 0);

    if ($userId > 0) {
        $stmt = $pdo->prepare('SELECT role_id FROM users WHERE id_user = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $canDelete = true;

            if ((int)$user['role_id'] === 2) {
                $stmt = $pdo->query('SELECT COUNT(*) AS total FROM users WHERE role_id = 2');
                $totalAdmins = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
                $canDelete = $totalAdmins > 1;
            }

            if ($canDelete) {
                $sql = "DELETE FROM users WHERE id_user = :id_user";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id_user', $userId, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    }

    header('Location: manage_users.php');
    exit;
}
