<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";
require_once SRC . "/services/ArticleService.php";

$idArticle = (int)($_GET['id_article'] ?? 0);

LogService::visit('view_article.php');

$article = null;

if ($idArticle > 0) {
    $article = ArticleService::findPublished($idArticle);
}

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <?php if ($article): ?>
            <article>
                <h1><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                <?php if ($article['category_name']): ?>
                    <p>Catégorie : <a href="category.php?id_category=<?= $article['id_category'] ?>"><?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?></a></p>
                <?php endif; ?>
                <p><?= nl2br(htmlspecialchars($article['content'], ENT_QUOTES, 'UTF-8')) ?></p>
            </article>
        <?php else: ?>
            <article>
                <h1>Article introuvable</h1>
                <p>Cet article n'existe pas ou n'est pas publié.</p>
                <a href="article.php">Retour aux articles</a>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
