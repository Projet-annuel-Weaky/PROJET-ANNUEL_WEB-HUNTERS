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
                    <?php foreach ($categories as $category): ?>
                        <div class="card">
                            <h3>#<?= $category['id_category'] ?> - <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Articles publiés : <?= htmlspecialchars($category['total_articles'], ENT_QUOTES, 'UTF-8') ?></p>
                            <form method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id_category" value="<?= $category['id_category'] ?>">
                                <input type="text" name="name" value="<?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                                <textarea name="description"><?= htmlspecialchars($category['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                <button type="submit">Modifier</button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_category" value="<?= $category['id_category'] ?>">
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
