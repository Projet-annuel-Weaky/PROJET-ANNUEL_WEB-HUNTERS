<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";
require_once SRC . "/services/ArticleService.php";

LogService::visit('article.php');

$articles = ArticleService::publicList();

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <article>
            <h1>ARTICLES</h1>
        </article>
        <div class="container">
            <?php foreach ($articles as $article): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php if ($article['category_name']): ?>
                        <p>Catégorie : <a href="category.php?id_category=<?= $article['id_category'] ?>"><?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?></a></p>
                    <?php endif; ?>
                    <p><?= htmlspecialchars(substr($article['content'], 0, 220), ENT_QUOTES, 'UTF-8') ?></p>
                    <a href="view_article.php?id_article=<?= $article['id_article'] ?>">Lire l'article</a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!$articles): ?>
            <p>Aucun article publié.</p>
        <?php endif; ?>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
