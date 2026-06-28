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
        <div class = "searchandresultsContainer">
            <form id="search-form">
                <input type="text" id="search-input" placeholder="Rechercher un article..." minlength="2" />
                <button type="submit">Rechercher</button>
            </form>
        <div id="search-results"></div>
        </div>
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
    const response = await fetch('/search_article.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ q })
    });

    if (!response.ok) throw new Error('Erreur serveur : ' + response.status);
    const data = await response.json();

    if (data.count === 0) {
      zone.innerHTML = `<p>Aucun article trouvé pour "<strong>${escHtml(q)}</strong>".</p>`;
      return;
    }

    let html = `<p>${data.count} article(s) trouvé(s) pour "<strong>${escHtml(q)}</strong>"</p><ul>`;
    data.results.forEach(article => {
      html += `
        <li>
          <a href="articles.php?id=${article.id_article}">
            <strong>${escHtml(article.title)}</strong>
            ${article.categorie ? `<span> — ${escHtml(article.categorie)}</span>` : ''}
            <small>par ${escHtml(article.auteur ?? 'Anonyme')} · ${article.created_at}</small>
            <p>${escHtml(article.excerpt)}</p>
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
<?php
include_once SRC . '/views/layouts/footer.php';
?>
