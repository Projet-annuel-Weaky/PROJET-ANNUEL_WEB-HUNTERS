<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

$sql = "SELECT id, filename, mime_type, active, reseted, completed, failed, created_at FROM captcha_images ORDER BY id ASC";
$images = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

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
            <button><a href="manage_categories.php" class="admin-link">MANAGE_SECTOR</a></button>
            <button><a href="manage_versions.php" class="admin-link">MANAGE_VERSIONS</a></button>
            <button><a href="logs.php" class="admin-link">LOGS</a></button>
            <button><a href="index.php" class="admin-link">RETURN -> HOME</a></button>
        </div>
        <div class="admin-layout">
            <div class="admin-panel">
                <h2>Captcha</h2>
                <?php
                    $totalsStmt = $pdo->query('SELECT COALESCE(SUM(completed),0) AS total_completed, COALESCE(SUM(failed),0) AS total_failed, COALESCE(SUM(reseted),0) AS total_reseted FROM captcha_images');
                    $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class='stats-summary'>
                    <h3>Captcha Statistiques</h3>
                    <p><strong>Total Completed:</strong> <?= htmlspecialchars($totals['total_completed'] ?? 0, ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Total Failed:</strong> <?= htmlspecialchars($totals['total_failed'] ?? 0, ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Total Reseted:</strong> <?= htmlspecialchars($totals['total_reseted'] ?? 0, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <form action="captcha/add_image.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="image" accept="image/png,image/jpeg,image/gif" required>
                    <button type="submit">Ajouter une image</button>
                </form>
            </div>
            <div class="admin-list">
                <h2>Images captcha</h2>
                <div class="container">
                    <?php foreach ($images as $image): ?>
                        <div class="card">
                            <h3>#<?= $image['id'] ?> - <?= htmlspecialchars($image['filename'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Active : <?= $image['active'] ? 'oui' : 'non' ?></p>
                            <p>Réussites : <?= $image['completed'] ?></p>
                            <p>Échecs : <?= $image['failed'] ?></p>
                            <p>Reset : <?= $image['reseted'] ?></p>
                            <form action="captcha/activate.php" method="POST">
                                <input type="hidden" name="id" value="<?= $image['id'] ?>">
                                <input type="hidden" name="active" value="<?= $image['active'] ? 0 : 1 ?>">
                                <button type="submit"><?= $image['active'] ? 'Désactiver' : 'Activer' ?></button>
                            </form>
                            <form action="captcha/delete.php" method="POST">
                                <input type="hidden" name="id" value="<?= $image['id'] ?>">
                                <button type="submit">Supprimer</button>
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
