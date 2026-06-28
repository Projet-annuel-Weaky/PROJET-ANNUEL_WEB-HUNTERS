<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";
require_once SRC . "/services/ArticleService.php";

require_once SRC . "/middleware/Router.php";

$page = parse_url($_SERVER['REQUEST_URI'] ?? 'index.php', PHP_URL_PATH);
LogService::visit($page ?: 'index.php');

$router = new Router();

$home = function() {
    include SRC . "/views/pages/home.php";
    $articles = ArticleService::publicList(3);
    ?>
    <main>
        <section>
            <article>
                <h2>Accès rapide</h2>
            </article>
            <div class="container quick-links">
                <div class="card">
                    <a href="article.php">Articles</a>
                </div>
                <div class="card">
                    <a href="category.php">Catégories</a>
                </div>
                <div class="card">
                    <a href="chat.php">Chat</a>
                </div>
                <?php if (isset($_SESSION['id_user'])): ?>
                    <div class="card">
                        <a href="profile.php">Profil</a>
                    </div>
                    <?php if (($_SESSION['role_id'] ?? 0) == 2): ?>
                        <div class="card">
                            <a href="admin.php">Administration</a>
                        </div>
                    <?php endif; ?>
                    <div class="card">
                        <a href="logout.php">Déconnexion</a>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <a href="login.php">Connexion</a>
                    </div>
                    <div class="card">
                        <a href="signup.php">Inscription</a>
                    </div>
                <?php endif; ?>
            </div>

            <article>
                <h2>Articles récents</h2>
            </article>
            <div class="container">
                <?php foreach ($articles as $article): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if ($article['category_name']): ?>
                            <p>Catégorie : <?= htmlspecialchars($article['category_name'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <p><?= htmlspecialchars(substr($article['content'], 0, 160), ENT_QUOTES, 'UTF-8') ?></p>
                        <a href="view_article.php?id_article=<?= $article['id_article'] ?>">Lire</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php
};

$router->addRoute('GET', '/', $home);
$router->addRoute('GET', '/index.php', $home);

$router->addRoute('GET', '/contact', function() {
    include SRC . "/views/pages/contact.php";
});

include_once SRC . "/views/layouts/header.php";

$router->dispatch();

include_once SRC . '/views/layouts/footer.php';
?>
