<?php

class AdminService
{
    public static function requireAdmin($loginPath = 'login.php')
    {
        if (!isset($_SESSION['id_user'])) {
            header('Location: ' . $loginPath);
            exit;
        }

        if (($_SESSION['role_id'] ?? 0) != 2) {
            http_response_code(403);
            echo "Accès refusé.";
            exit;
        }
    }
}
