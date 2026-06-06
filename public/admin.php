<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

require_once SRC . "/views/layouts/header.php";

$stmt = $pdo->query("
    SELECT
        id_user,
        username,
        email,
        last_activity
    FROM users
    WHERE last_activity >= NOW() - INTERVAL 5 MINUTE
    ORDER BY username ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <button><a href="manage_categories.php" class="admin-link">MANAGE_SECTOR</a></button>
            <button><a href="manage_versions.php" class="admin-link">MANAGE_VERSIONS</a></button>
            <button><a href="logs.php" class="admin-link">LOGS</a></button>
            <button><a href="index.php" class="admin-link">RETURN -> HOME</a></button>
        </div>
    </section>
    <section>
    <h2>Online users</h2>

    <div class="container">
        <?php foreach ($users as $user): ?>
            <div class="card">
                <h3>
                    #<?= htmlspecialchars($user['id_user'], ENT_QUOTES, 'UTF-8') ?>
                    - <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <?php if (($_SESSION['role_id'] ?? 0) == 2): ?>
                    <p>Email : <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($user['last_activity']): ?>
                    <p>Last seen : <?= htmlspecialchars($user['last_activity'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
