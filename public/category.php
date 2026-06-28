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
$articleCount = 0;

if ($idCategory > 0) {
    $category = CategoryService::find($idCategory);

    if ($category) {
        $articles = ArticleService::publicList(null, $idCategory);
        $articleCount = count($articles);
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

        <div class="searchandresultsContainer">
            <form id="search-form">
                <input type="text" id="search-input" placeholder="Rechercher une catégorie..." minlength="2" />
                <button type="submit">Rechercher</button>
            </form>
            <div id="search-results"></div>
        </div>

        <?php if ($category): ?>
            <h2>
                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                <span class="article-count"><?= $articleCount ?> article<?= $articleCount > 1 ? 's' : '' ?></span>
            </h2>

            <?php if (!empty($category['description'])): ?>
                <p><?= nl2br(htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>

            <?php if ($articles): ?>
                <div class="container">
                    <?php foreach ($articles as $article): ?>
                        <div class="card">
                            <h3><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p><?= htmlspecialchars(substr($article['content'], 0, 220), ENT_QUOTES, 'UTF-8') ?>…</p>
                            <a href="view_article.php?id_article=<?= $article['id_article'] ?>">Lire l'article</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aucun article publié dans cette catégorie.</p>
            <?php endif; ?>

            <a href="category.php">← Voir toutes les catégories</a>

        <?php elseif ($idCategory > 0): ?>
            <p>Catégorie introuvable.</p>
            <a href="category.php">← Voir toutes les catégories</a>

        <?php else: ?>
            <?php if ($categories): ?>
                <div class="container">
                    <?php foreach ($categories as $item): ?>
                        <div class="card">
                            <h3><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></h3>

                            <?php if (!empty($item['description'])): ?>
                                <p><?= nl2br(htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <?php endif; ?>

                            <p class="article-count">
                                <?= (int)$item['total_articles'] ?> article<?= $item['total_articles'] > 1 ? 's' : '' ?> publié<?= $item['total_articles'] > 1 ? 's' : '' ?>
                            </p>

                            <?php if ($item['total_articles'] > 0): ?>
                                <a href="category.php?id_category=<?= $item['id_category'] ?>">
                                    Voir les articles →
                                </a>
                            <?php else: ?>
                                <span class="no-articles">Aucun article pour l'instant</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aucune catégorie.</p>
            <?php endif; ?>
        <?php endif; ?>

    </section>
</main>

<script>
let searchTimeout;

async function performSearch() {
  const q = document.getElementById('search-input').value.trim();
  const zone = document.getElementById('search-results');

  if (q.length < 2) {
    zone.innerHTML = '';
    return;
  }
  zone.innerHTML = '<p>Recherche en cours…</p>';

    try {
        const response = await fetch('./search_categorie.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ q })
        });

        const data = await response.json();

        if (!response.ok) throw new Error('Erreur serveur : ' + response.status);

    if (data.count === 0) {
        zone.innerHTML = `<p>Aucune catégories trouvé pour "${escHtml(q)}".</p>`;
        return;
    }

    let html = `<p>${data.count} catégorie(s) trouvée(s) pour "<strong>${escHtml(q)}</strong>"</p><ul>`;
    data.results.forEach(cat => {
        html += `
        <li>
        <a href="./category.php?id_category=${cat.id_category}">
        <strong>${escHtml(cat.name)}</strong>
        <span>(${cat.nb_articles} article</span>
        ${cat.description ? `<p>${escHtml(cat.description)}</p>` : ''}
        </a>
        </li>`;
    });
    html += '</ul>';
    zone.innerHTML = html;

  } catch (err) {
    zone.innerHTML = `<p style="color:red;">Erreur : ${err.message}</p>`;
  }
}

document.getElementById('search-input').addEventListener('input', function() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(performSearch, 300);
});

document.getElementById('search-form').addEventListener('submit', function(e) {
  e.preventDefault();
  clearTimeout(searchTimeout);
  performSearch();
});

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}
</script>

<?php include_once SRC . '/views/layouts/footer.php'; ?>