<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

LogService::visit('view_user.php');

$idUser = (int)($_GET['id_user'] ?? 0);
$isAdmin = (($_SESSION['role_id'] ?? 0) == 2);
$user = null;

if ($idUser > 0) {
    $stmt = $pdo->prepare("
        SELECT u.id_user, u.username, u.email, u.bio, u.is_verified, u.created_at, r.name AS role_name
        FROM users u
        LEFT JOIN roles r ON r.role_id = u.role_id
        WHERE u.id_user = :id_user
        LIMIT 1
    ");
    $stmt->execute([':id_user' => $idUser]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <?php if ($user): ?>
            <article class="card">
                <h1><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Rôle : <?= htmlspecialchars($user['role_name'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8') ?></p>

                <?php if (!empty($user['bio'])): ?>
                    <p><?= nl2br(htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8')) ?></p>
                <?php else: ?>
                    <p>Aucune bio.</p>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <hr>
                    <h2>Réservé admin</h2>
                    <p>ID : <?= (int)$user['id_user'] ?></p>
                    <p>Email : <?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <p>Vérifié : <?= ((int)($user['is_verified'] ?? 0) === 1) ? 'oui' : 'non' ?></p>
                    <p>Créé le : <?= !empty($user['created_at']) ? htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') : 'N/A' ?></p>
                <?php endif; ?>

                <p><a href="user.php">Retour à la liste des utilisateurs</a></p>
            </article>
        <?php else: ?>
            <article>
                <h1>Utilisateur introuvable</h1>
                <p>Cet utilisateur n'existe pas.</p>
                <a href="user.php">Retour à la liste des utilisateurs</a>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php include_once SRC . '/views/layouts/footer.php'; ?>
