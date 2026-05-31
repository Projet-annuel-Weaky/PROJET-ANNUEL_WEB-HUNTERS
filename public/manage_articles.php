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
                    <?php foreach ($articles as $article): ?>
                        <div class="card">
                            <h3>#<?= $article['id_article'] ?> - <?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Statut : <?= htmlspecialchars($article['status'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p>Catégorie : <?= htmlspecialchars($article['category_name'] ?? 'Sans catégorie', ENT_QUOTES, 'UTF-8') ?></p>
                            <p>Auteur : <?= htmlspecialchars($article['username'] ?? 'Inconnu', ENT_QUOTES, 'UTF-8') ?></p>
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
