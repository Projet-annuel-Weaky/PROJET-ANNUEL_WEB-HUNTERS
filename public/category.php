<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";
require_once SRC . "/services/CategoryService.php";
require_once SRC . "/services/ArticleService.php";

LogService::visit('category.php');

$idCategory = (int)($_GET['id_category'] ?? 0);
$category = null;
$articles = [];

if ($idCategory > 0) {
    $category = CategoryService::find($idCategory);

    if ($category) {
        $articles = ArticleService::publicList(null, $idCategory);
    }
}

$categories = CategoryService::allWithPublishedCount();

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <article>
            <h1>CATEGORIES</h1>
        </article>
        <?php if ($category): ?>
            <h2><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= nl2br(htmlspecialchars($category['description'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
            <div class="container">
                <?php foreach ($articles as $article): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= htmlspecialchars(substr($article['content'], 0, 220), ENT_QUOTES, 'UTF-8') ?></p>
                        <a href="view_article.php?id_article=<?= $article['id_article'] ?>">Lire l'article</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!$articles): ?>
                <p>Aucun article publié dans cette catégorie.</p>
            <?php endif; ?>
            <a href="category.php">Voir toutes les catégories</a>
        <?php elseif ($idCategory > 0): ?>
            <p>Catégorie introuvable.</p>
            <a href="category.php">Voir toutes les catégories</a>
        <?php else: ?>
            <div class="container">
                <?php foreach ($categories as $item): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p><?= nl2br(htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                        <p>Articles publiés : <?= htmlspecialchars($item['total_articles'], ENT_QUOTES, 'UTF-8') ?></p>
                        <a href="category.php?id_category=<?= $item['id_category'] ?>">Voir les articles</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!$categories): ?>
                <p>Aucune catégorie.</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
