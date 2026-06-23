<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";
require_once SRC . "/services/CategoryService.php";

AdminService::requireAdmin();

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idCategory = (int)($_POST['id_category'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    try {
        if ($action === 'create') {
            if ($name === '') {
                $erreur = 'Veuillez remplir le nom.';
            } else {
                CategoryService::create($name, $description);
                $succes = 'Catégorie créée.';
            }
        }

        if ($action === 'update' && $idCategory > 0) {
            if ($name === '') {
                $erreur = 'Veuillez remplir le nom.';
            } else {
                CategoryService::update($idCategory, $name, $description);
                $succes = 'Catégorie modifiée.';
            }
        }

        if ($action === 'delete' && $idCategory > 0) {
            CategoryService::delete($idCategory);
            $succes = 'Catégorie supprimée.';
        }
    } catch (PDOException $e) {
        $erreur = 'Action impossible.';
    }
}

$categories = CategoryService::allWithPublishedCount();

require_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
    <?php require SRC . "/views/layouts/adminNav.php" ?>

        <div class="admin-layout">
            <div class="admin-panel">
                <h2>Créer une catégorie</h2>
                <?php if ($erreur): ?>
                    <p class="error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <p class="success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <input type="text" name="name" placeholder="Nom" required>
                    <textarea name="description" placeholder="Description"></textarea>
                    <button type="submit">Créer</button>
                </form>
            </div>
            <div class="admin-list">
                <h2>Catégories</h2>
                <div class="container">
                    <table id="categories-table" class="data-table">
                        <thead>
                            <tr><th>#</th><th>Nom</th><th>Articles publiés</th><th>Likes</th><th class="col-actions"></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $category['id_category'] ?></td>
                                    <td><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($category['total_articles'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) ($category['like_count'] ?? 0) ?></td>
                                    <td class="col-actions">
                                        <button type="button" data-modal-open="category-modal-<?= $category['id_category'] ?>">Détails</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$categories): ?><p class="table-empty">Aucune catégorie.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
