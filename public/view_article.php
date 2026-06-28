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

$versions = [];
$approvedVersions = [];
if ($article) {
    $versions = ArticleService::getVersions($article['id_article']);
    $approvedVersions = array_filter($versions, fn($v) => $v['status'] === 'approved');
}

$selectedVersion = null;
if ($article && isset($_GET['version'])) {
    $idVersion = (int)$_GET['version'];
    foreach ($versions as $v) {
        if ((int)$v['id_version'] === $idVersion && $v['status'] === 'approved') {
            $selectedVersion = $v;
            break;
        }
    }
}

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <?php if ($article): ?>
            <article id="article-content">
                <h1><?= htmlspecialchars($selectedVersion['title'] ?? $article['title'], ENT_QUOTES, 'UTF-8') ?></h1>

                <?php if ($article['category_name']): ?>
                    <p>Catégorie : <a href="category.php?id_category=<?= $article['id_category'] ?>"><?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?></a></p>
                <?php endif; ?>

                <p class="article-meta">Publié le : <time datetime="<?= htmlspecialchars($article['created_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('d/m/Y', strtotime($article['created_at'])), ENT_QUOTES, 'UTF-8') ?></time></p>

                <?php if ($selectedVersion): ?>
                    <div class="version-notice">
                        Vous consultez la <strong>version <?= (int)$selectedVersion['version_number'] ?></strong>
                        — par <?= htmlspecialchars($selectedVersion['username'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8') ?>
                        — <a href="view_article.php?id_article=<?= $article['id_article'] ?>">Voir la version actuelle</a>
                    </div>
                <?php endif; ?>

                <div class="article-body"><?= nl2br(htmlspecialchars($selectedVersion['content'] ?? $article['content'], ENT_QUOTES, 'UTF-8')) ?></div>

                <div class="article-actions">
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <a class="btn" href="edit_article.php?id_article=<?= $article['id_article'] ?>">Modifier cet article</a>
                    <?php endif; ?>
                    <button id="exportPdf" type="button">Exporter en PDF</button>
                    <a href="article.php">Retour aux articles</a>
                </div>
            </article>

            <?php if (count($approvedVersions) > 1): ?>
                <div class="versions-history">
                    <h3>Historique des versions</h3>
                    <ul class="versions-list">
                        <?php
                            $approvedArr = array_values($approvedVersions);
                            $maxVersionNum = $approvedArr ? max(array_column($approvedArr, 'version_number')) : 0;
                        ?>
                        <?php foreach ($approvedVersions as $v): ?>
                            <?php
                                $isActive = $selectedVersion
                                    ? ((int)$v['id_version'] === (int)$selectedVersion['id_version'])
                                    : ((int)$v['version_number'] === (int)$maxVersionNum);
                            ?>
                            <li class="version-item <?= $isActive ? 'version-item--active' : '' ?>">
                                <a href="view_article.php?id_article=<?= $article['id_article'] ?>&version=<?= $v['id_version'] ?>" class="version-link">
                                    <span class="version-number">v<?= (int)$v['version_number'] ?></span>
                                    <span class="version-meta">
                                        <span class="version-author"><?= htmlspecialchars($v['username'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="version-date"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($v['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
            <script>
                (function () {
                    const btn = document.getElementById('exportPdf');
                    if (!btn) return;
                    btn.addEventListener('click', function () {
                        const element = document.getElementById('article-content');
                        if (!element) return;
                        const title = (element.querySelector('h1') && element.querySelector('h1').innerText) || 'article';
                        const timeEl = element.querySelector('.article-meta time');
                        const date = timeEl ? timeEl.getAttribute('datetime').split(' ')[0] : '';
                        const safeTitle = title.replace(/[^\w\- ]+/g, '').replace(/\s+/g, '_').substring(0, 80);
                        const filename = (safeTitle || 'article') + (date ? '_' + date : '') + '.pdf';

                        const opt = {
                            margin: [10, 10, 10, 10],
                            filename: filename,
                            image: { type: 'jpeg', quality: 0.98 },
                            html2canvas: { scale: 2, useCORS: true },
                            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                        };

                        const clone = element.cloneNode(true);
                        clone.style.background = '#fff';
                        clone.style.color = '#000';

                        const actions = clone.querySelector('.article-actions');
                        if (actions) actions.remove();
                        clone.querySelectorAll('button, input, textarea, select').forEach(el => el.remove());

                        clone.querySelectorAll('a').forEach(a => {
                            a.style.color = '#000';
                            a.removeAttribute('href');
                        });

                        const wrapper = document.createElement('div');
                        wrapper.style.padding = '10mm';
                        wrapper.appendChild(clone);

                        html2pdf().set(opt).from(wrapper).save();
                    });
                })();
            </script>
        <?php else: ?>
            <article>
                <h1>Article introuvable</h1>
                <p>Cet article n'existe pas ou n'est pas publié.</p>
                <a href="article.php">Retour aux articles</a>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php include_once SRC . '/views/layouts/footer.php'; ?>