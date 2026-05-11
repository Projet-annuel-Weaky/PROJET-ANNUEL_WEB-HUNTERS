<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_user') {
    $userId = (int)($_POST['id_user'] ?? 0);
    $roleId = (int)($_POST['role_id'] ?? 1);
    $isVerified = (int)($_POST['is_verified'] ?? 0);

    if ($roleId !== 1 && $roleId !== 2) {
        $roleId = 1;
    }

    if ($userId > 0) {
        $stmt = $pdo->prepare('SELECT role_id FROM users WHERE id_user = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && (int)$user['role_id'] === 2 && $roleId !== 2) {
            $stmt = $pdo->query('SELECT COUNT(*) AS total FROM users WHERE role_id = 2');
            $totalAdmins = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            if ($totalAdmins <= 1) {
                $erreur = 'Impossible de retirer le dernier administrateur.';
            }
        }

        if (!$erreur) {
            $stmt = $pdo->prepare('UPDATE users SET role_id = ?, is_verified = ? WHERE id_user = ?');
            $stmt->execute([$roleId, $isVerified, $userId]);
            $succes = 'Utilisateur mis à jour.';
        }
    }
}

$roles = $pdo->query('SELECT role_id, name FROM roles ORDER BY role_id ASC')->fetchAll(PDO::FETCH_ASSOC);
$sql = "
SELECT u.id_user, u.username, u.email, u.role_id, u.is_verified, r.name AS role_name
FROM users u
JOIN roles r ON r.role_id = u.role_id
ORDER BY u.id_user ASC
";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once SRC . "/views/layouts/header.php";

?>

<main>
    <section>
        <article>
            <h1>ADMINISTRATION</h1>
        </article>
        <div class="admin-bar">
            <button><a href="admin.php" class="admin-link">HOME</a></button>
            <button><a href="manage_users.php" class="admin-link">MANAGE_USER</a></button>
            <button><a href="manage_roles.php" class="admin-link">MANAGE_ROLE</a></button>
            <button><a href="manage_captcha.php" class="admin-link">MANAGE_CAPTCHA</a></button>
            <button><a href="manage_newsletters.php" class="admin-link">MANAGE_NEWSLETTER</a></button>
            <button><a href="manage_articles.php" class="admin-link">MANAGE_ARTICLE</a></button>
            <button><a href="manage_categories.php" class="admin-link">MANAGE_SECTOR</a></button>
            <button><a href="logs.php" class="admin-link">LOGS</a></button>
            <button><a href="index.php" class="admin-link">RETURN -> HOME</a></button>
        </div>
        <div class="admin-layout">
            <div class="admin-panel">
            <h2>MANAGE_USER</h2>
            <?php if ($erreur): ?>
                <p class="error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if ($succes): ?>
                <p class="success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <p><strong>ADD USER :</strong></p>
            <form class="add-user" action="add_user.php" method="POST">
                <input type="text" name="username" placeholder="USERNAME :" required>
                <input type="email" name="email" placeholder="EMAIL :" required>
                <input type="password" name="password" placeholder="PASSWORD :" required>
                <select name="role_id">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="is_verified">
                    <option value="1">VERIFIED</option>
                    <option value="0">NOT VERIFIED</option>
                </select>
                <button type="submit">Ajouter</button>
            </form>
            <p><strong>DELETE_USER :</strong></p>
            <form class="del-user" action="delete_user.php" method="POST">
                <input type="text" name="id_user" placeholder="ID USER :" required>
                <button type="submit">Supprimer</button>
            </form>
            </div>
            <div class="admin-list">
            <h2>Liste des utilisateurs</h2>
            <div class="container">
            <?php foreach ($users as $user): ?>
                <div class="card">
                    <h3>#<?= $user['id_user'] ?> - <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p>Email : <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                        <select name="role_id">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>" <?= (int)$user['role_id'] === (int)$role['role_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="is_verified">
                            <option value="1" <?= $user['is_verified'] ? 'selected' : '' ?>>VERIFIED</option>
                            <option value="0" <?= !$user['is_verified'] ? 'selected' : '' ?>>NOT VERIFIED</option>
                        </select>
                        <button type="submit">Modifier</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        </div>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
