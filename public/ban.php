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
    <?php require SRC . "/views/layouts/adminNav.php" ?>

    <section>
        <h2>Gestion des bans</h2>

        <?php if ($erreur): ?>
            <p class="error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($succes): ?>
            <p class="success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <div class="container">
                <table id="users-table" class="data-table">
                    <thead>
                        <tr><th>#</th><th>Username</th><th>Email</th><th>Raison</th><th class="col-actions"></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id_user'] ?></td>
                                <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <?php if ($user['is_banned'] && $user['ban_reason']): ?>
                                    <td>Raison : <?= htmlspecialchars($user['ban_reason'], ENT_QUOTES, 'UTF-8') ?></td>
                                <?php endif; ?>
                                <td class="col-actions">
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
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (!$users): ?><p class="table-empty">Aucun utilisateur.</p><?php endif; ?>

        </div>
    </section>
</main>

<?php include_once SRC . '/views/layouts/footer.php'; ?>