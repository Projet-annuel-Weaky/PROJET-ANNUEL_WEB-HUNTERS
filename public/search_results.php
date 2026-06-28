<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

LogService::visit('search_results.php');

$query = trim($_GET['q'] ?? '');
$results = ['articles' => [], 'users' => [], 'categories' => []];
$hasResults = false;

if (mb_strlen($query) >= 2) {
    $like = '%' . $query . '%';

    try {
        $stmt = $pdo->prepare("
        SELECT a.id_article,
               a.title,
               a.content,
               a.status,
               a.created_at,
               u.username      AS auteur,
               c.name          AS categorie
        FROM articles a
        LEFT JOIN users      u ON u.id_user     = a.id_user
        LEFT JOIN categories c ON c.id_category = a.id_category
        WHERE a.status = 'published'
          AND (
               a.title   LIKE :q
            OR a.content LIKE :q
          )
        ORDER BY a.created_at DESC
        LIMIT 50
        ");
        $stmt->execute([':q' => $like]);
        $results['articles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT u.id_user,
                   u.username,
                   u.bio,
                   r.name AS role
            FROM users u
            JOIN roles r ON r.role_id = u.role_id
            WHERE u.username LIKE :q
               OR u.bio      LIKE :q
            ORDER BY u.username ASC
            LIMIT 50
        ");
        $stmt->execute([':q' => $like]);
        $results['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT c.id_category,
                   c.name,
                   c.description,
                   COUNT(a.id_article) AS nb_articles
            FROM categories c
            LEFT JOIN articles a ON a.id_category = c.id_category
            WHERE c.name        LIKE :q
               OR c.description LIKE :q
            GROUP BY c.id_category
            ORDER BY c.name ASC
            LIMIT 50
        ");
        $stmt->execute([':q' => $like]);
        $results['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hasResults = count($results['articles']) > 0 || count($results['users']) > 0 || count($results['categories']) > 0;
    } catch (PDOException $e) {
    }
}

include_once SRC . "/views/layouts/header.php";
?>

<main class="search-results-page">
    <section class="search-results-section">
        <div class="search-header">
            <h1>Résultats de recherche</h1>
            <form class="search-results-form" method="GET">
                <input type="text" name="q" placeholder="Nouvelle recherche..." value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" minlength="2" />
                <button type="submit">Rechercher</button>
            </form>
        </div>

        <?php if (mb_strlen($query) < 2): ?>
            <div class="no-search-query">
                <p>Veuillez entrer au moins 2 caractères pour rechercher.</p>
            </div>
        <?php elseif (!$hasResults): ?>
            <div class="no-results-message">
                <p>Aucun résultat trouvé pour "<strong><?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?></strong>"</p>
            </div>
        <?php else: ?>
            <?php if (count($results['articles']) > 0): ?>
                <div class="results-category">
                    <h2 class="category-header">Articles (<?= count($results['articles']) ?>)</h2>
                    <div class="results-list articles-list">
                        <?php foreach ($results['articles'] as $article): ?>
                            <div class="result-card article-card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <a href="view_article.php?id_article=<?= htmlspecialchars($article['id_article'], ENT_QUOTES, 'UTF-8') ?>" class="card-link">
                                            <?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </h3>
                                </div>
                                <div class="card-meta">
                                    <span class="meta-item">Par <?= htmlspecialchars($article['auteur'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="meta-separator">•</span>
                                    <span class="meta-item"><?= htmlspecialchars($article['categorie'] ?? 'Sans catégorie', ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="meta-separator">•</span>
                                    <span class="meta-item"><?= date('d/m/Y', strtotime($article['created_at'])) ?></span>
                                </div>
                                <div class="card-excerpt">
                                    <?= htmlspecialchars(mb_substr(strip_tags($article['content']), 0, 200), ENT_QUOTES, 'UTF-8') ?>...
                                </div>
                                <div class="card-footer">
                                    <a href="view_article.php?id_article=<?= htmlspecialchars($article['id_article'], ENT_QUOTES, 'UTF-8') ?>" class="btn-read-more">
                                        Lire l'article →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (count($results['users']) > 0): ?>
                <div class="results-category">
                    <h2 class="category-header">Utilisateurs (<?= count($results['users']) ?>)</h2>
                    <div class="results-list users-list">
                        <?php foreach ($results['users'] as $user): ?>
                            <div class="result-card user-card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <a href="view_user.php?id_user=<?= htmlspecialchars($user['id_user'], ENT_QUOTES, 'UTF-8') ?>" class="card-link">
                                            <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </h3>
                                </div>
                                <div class="card-meta">
                                    <span class="meta-item role-badge"><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <?php if (!empty($user['bio'])): ?>
                                    <div class="card-excerpt">
                                        <?= htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                                <div class="card-footer">
                                    <a href="view_user.php?id_user=<?= htmlspecialchars($user['id_user'], ENT_QUOTES, 'UTF-8') ?>" class="btn-read-more">
                                        Voir le profil →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (count($results['categories']) > 0): ?>
                <div class="results-category">
                    <h2 class="category-header">Catégories (<?= count($results['categories']) ?>)</h2>
                    <div class="results-list categories-list">
                        <?php foreach ($results['categories'] as $category): ?>
                            <div class="result-card category-card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <a href="category.php?id_category=<?= htmlspecialchars($category['id_category'], ENT_QUOTES, 'UTF-8') ?>" class="card-link">
                                            <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </h3>
                                </div>
                                <div class="card-meta">
                                    <span class="meta-item"><?= $category['nb_articles'] ?> article(s)</span>
                                </div>
                                <?php if (!empty($category['description'])): ?>
                                    <div class="card-excerpt">
                                        <?= htmlspecialchars(mb_substr($category['description'], 0, 150), ENT_QUOTES, 'UTF-8') ?>...
                                    </div>
                                <?php endif; ?>
                                <div class="card-footer">
                                    <a href="category.php?id_category=<?= htmlspecialchars($category['id_category'], ENT_QUOTES, 'UTF-8') ?>" class="btn-read-more">
                                        Voir la catégorie →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<?php include_once SRC . "/views/layouts/footer.php"; ?>
