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
        <h2>Familles de droit</h2>
        <div class="container">
            <?php foreach ($roles as $role): ?>
                <div class="card">
                    <h3>#<?= $role['role_id'] ?> - <?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p>Utilisateurs : <?= $role['total_users'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
