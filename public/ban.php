<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";
require_once SRC . "/services/BanService.php";

AdminService::requireAdmin();

$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idUser = (int)($_POST['id_user'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');

    if ($idUser === (int)$_SESSION['id_user']) {
        $erreur = 'Vous ne pouvez pas vous bannir vous-même.';
    } elseif ($idUser > 0) {
        try {
            if ($action === 'ban') {
                BanService::ban($idUser, $reason);
                $succes = 'Utilisateur banni.';
            } elseif ($action === 'unban') {
                BanService::unban($idUser);
                $succes = 'Utilisateur débanni.';
            }
        } catch (PDOException $e) {
            $erreur = 'Action impossible.';
        }
    }
}

$users = BanService::allUsers();

require_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <article>
            <h1>ADMINISTRATION</h1>
        </article>
        <div class="admin-bar">
            <button><a href="ban.php" class="admin-link">BAN</a></button>
            <button><a href="admin.php" class="admin-link">HOME</a></button>
            <button><a href="manage_users.php" class="admin-link">MANAGE_USER</a></button>
            <button><a href="manage_roles.php" class="admin-link">MANAGE_ROLE</a></button>
            <button><a href="manage_captcha.php" class="admin-link">MANAGE_CAPTCHA</a></button>
            <button><a href="manage_newsletters.php" class="admin-link">MANAGE_NEWSLETTER</a></button>
            <button><a href="manage_articles.php" class="admin-link">MANAGE_ARTICLE</a></button>
            <button><a href="manage_articles.php" class="admin-link">MANAGE_ARTICLE</a></button>
            <button><a href="manage_categories.php" class="admin-link">MANAGE_SECTOR</a></button>
            <button><a href="manage_versions.php" class="admin-link">LOGS</a></button>
            <button><a href="logs.php" class="admin-link">LOGS</a></button>
            <button><a href="index.php" class="admin-link">RETURN -> HOME</a></button>
        </div>
    </section>

    <section>
        <h2>Gestion des bans</h2>

        <?php if ($erreur): ?>
            <p class="error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($succes): ?>
            <p class="success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <div class="container">
            <?php foreach ($users as $user): ?>
                <div class="card <?= $user['is_banned'] ? 'banned' : '' ?>">
                    <h3>
                        #<?= $user['id_user'] ?> - <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($user['is_banned']): ?>
                            <span class="badge offline">Banni</span>
                        <?php else: ?>
                            <span class="badge online">Actif</span>
                        <?php endif; ?>
                    </h3>
                    <p>Email : <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if ($user['is_banned'] && $user['ban_reason']): ?>
                        <p>Raison : <?= htmlspecialchars($user['ban_reason'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <?php if ($user['id_user'] !== (int)$_SESSION['id_user']): ?>
                        <?php if (!$user['is_banned']): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="ban">
                                <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                                <input type="text" name="reason" placeholder="Raison du ban (optionnel)">
                                <button type="submit">Bannir</button>
                            </form>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="unban">
                                <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                                <button type="submit">Débannir</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><em>(Votre compte)</em></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include_once SRC . '/views/layouts/footer.php'; ?>