<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";
require_once SRC . "/services/ArticleService.php";
require_once SRC . "/services/CategoryService.php";

AdminService::requireAdmin();

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idArticle = (int)($_POST['id_article'] ?? 0);
    $idCategory = (int)($_POST['id_category'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $idCategory = $idCategory > 0 ? $idCategory : null;

    if ($status !== 'published') {
        $status = 'draft';
    }

    try {
        if ($action === 'create') {
            if ($title === '' || $content === '') {
                $erreur = 'Veuillez remplir le titre et le contenu.';
            } else {
                ArticleService::create($idCategory, $_SESSION['id_user'] ?? null, $title, $content, $status);
                $succes = 'Article créé.';
            }
        }

        if ($action === 'update' && $idArticle > 0) {
            if ($title === '' || $content === '') {
                $erreur = 'Veuillez remplir le titre et le contenu.';
            } else {
                ArticleService::update($idArticle, $idCategory, $title, $content, $status);
                $succes = 'Article modifié.';
            }
        }

        if ($action === 'delete' && $idArticle > 0) {
            ArticleService::delete($idArticle);
            $succes = 'Article supprimé.';
        }
    } catch (PDOException $e) {
        $erreur = 'Action impossible.';
    }
}

$categories = CategoryService::all();
$articles = ArticleService::allForAdmin();

require_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <?php require SRC . "/views/layouts/adminNav.php" ?>

        <div class="admin-layout">
            <div class="admin-panel">
                <h2>Créer un article</h2>
                <?php if ($erreur): ?>
                    <p class="error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <p class="success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <input type="text" name="title" placeholder="Titre" required>
                    <select name="id_category">
                        <option value="0">Sans catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id_category'] ?>"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status">
                        <option value="draft">Brouillon</option>
                        <option value="published">Publié</option>
                    </select>
                    <textarea name="content" placeholder="Contenu" required></textarea>
                    <button type="submit">Créer</button>
                </form>
            </div>

            <div class="admin-list">
                <h2>Liste des articles</h2>
                <div class="container">
                    <table id="articles-table" class="data-table">
                        <thead>
                            <tr><th>#</th><th>Titre</th><th>Statut</th><th>Catégorie</th><th>Auteur</th><th class="col-actions"></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $article): ?>
                                <tr>
                                    <td><?= $article['id_article'] ?></td>
                                    <td><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($article['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($article['category_name'] ?? 'Sans catégorie', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($article['username'] ?? 'Inconnu', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="col-actions">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="id_article" value="<?= $article['id_article'] ?>">
                                            <input type="text" name="title" value="<?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>" required>
                                            <select name="id_category">
                                                <option value="0">Sans catégorie</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category['id_category'] ?>" <?= (int)($article['id_category'] ?? 0) === (int)$category['id_category'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select name="status">
                                                <option value="draft" <?= $article['status'] === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                                                <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Publié</option>
                                            </select>
                                            <textarea name="content" required><?= htmlspecialchars($article['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                            <button type="submit">Modifier</button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_article" value="<?= $article['id_article'] ?>">
                                            <button type="submit">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$articles): ?><p class="table-empty">Aucun article.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
