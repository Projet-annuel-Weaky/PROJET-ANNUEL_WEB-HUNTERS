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
        <?php require SRC . "/views/layouts/adminNav.php" ?>

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
                    <table id="captcha-table" class="data-table">
                        <thead>
                            <tr><th>#</th><th>Fichier</th><th>Active</th><th>Réussites</th><th>Échecs</th><th>Reset</th><th class="col-actions"></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($images as $image): ?>
                                <tr>
                                    <td><?= $image['id'] ?></td>
                                    <td><?= htmlspecialchars($image['filename'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= $image['active'] ? 'oui' : 'non' ?></td>
                                    <td><?= $image['completed'] ?></td>
                                    <td><?= $image['failed'] ?></td>
                                    <td><?= $image['reseted'] ?></td>
                                    <td class="col-actions">
                                        <button type="button" data-modal-open="captcha-modal-<?= $image['id'] ?>">Détails</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$images): ?><p class="table-empty">Aucune image.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
