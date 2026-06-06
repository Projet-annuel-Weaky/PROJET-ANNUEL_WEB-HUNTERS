<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

$message = '';
$success = false;
$token = trim($_GET['token'] ?? '');

LogService::visit('verify.php');

if ($token === '') {
    $message = 'Lien de vérification invalide.';
} else {
    $stmt = $pdo->prepare('SELECT id_user, verification_expires_at FROM users WHERE verification_token = ? AND is_verified = 0');
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = 'Lien de vérification invalide.';
    } elseif (strtotime($user['verification_expires_at']) < time()) {
        $message = 'Lien de vérification expiré.';
    } else {
        $stmt = $pdo->prepare('UPDATE users SET is_verified = 1, verification_token = NULL, verification_expires_at = NULL WHERE id_user = ?');
        $stmt->execute([$user['id_user']]);
        $success = true;
        $message = 'Votre compte est maintenant vérifié.';
    }
}

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <h1>Vérification du compte</h1>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php if ($success): ?>
            <a href="login.php">Se connecter</a>
        <?php endif; ?>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
