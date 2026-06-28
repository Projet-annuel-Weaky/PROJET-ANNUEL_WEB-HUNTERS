<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

$sql = "
SELECT r.role_id, r.name, COUNT(u.id_user) AS total_users
FROM roles r
LEFT JOIN users u ON u.role_id = r.role_id
GROUP BY r.role_id, r.name
ORDER BY r.role_id ASC
";
$roles = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

require_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
    <?php require SRC . "/views/layouts/adminNav.php" ?>

        <h2>Familles de droit</h2>
        <div class="container">
            <table id="roles-table" class="data-table">
                <thead>
                    <tr><th>#</th><th>Rôle</th><th>Utilisateurs</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?= $role['role_id'] ?></td>
                            <td><?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $role['total_users'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (!$roles): ?><p class="table-empty">Aucun rôle.</p><?php endif; ?>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>