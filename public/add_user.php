<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = strtolower(trim($_POST['email'] ?? ''));
    $roleId = (int)($_POST['role_id'] ?? 1);
    $isVerified = (int)($_POST['is_verified'] ?? 1);

    if ($roleId !== 1 && $roleId !== 2) {
        $roleId = 1;
    }

    if (strlen($username) < 3 || strlen($username) > 20) {
        $erreur = 'Le nom d\'utilisateur doit contenir entre 3 et 20 caractères.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Veuillez entrer un email valide.';
    } elseif (strlen($password) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $stmt = $pdo->prepare('SELECT id_user FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $erreur = 'Ce nom d\'utilisateur est déjà pris.';
        } else {
            $stmt = $pdo->prepare('SELECT id_user FROM users WHERE email = ?');
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $erreur = 'Cet email est déjà utilisé.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role_id, is_verified, created_at) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $username,
                    $email,
                    $hash,
                    $roleId,
                    $isVerified,
                    date('Y-m-d H:i:s')
                ]);

                header('Location: manage_users.php');
                exit;
            }
        }
    }
}

header('Location: manage_users.php');
exit;
?>
