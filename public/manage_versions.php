<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";
require_once SRC . "/services/ArticleService.php";

AdminService::requireAdmin();

$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $idVersion = (int)($_POST['id_version'] ?? 0);

    try {
        if ($action === 'approve' && $idVersion > 0) {
            ArticleService::approveVersion($idVersion);
            $succes = 'Version approuvée et appliquée à l\'article.';
        }
        if ($action === 'reject' && $idVersion > 0) {
            ArticleService::rejectVersion($idVersion);
            $succes = 'Version rejetée.';
        }
    } catch (PDOException $e) {
        $erreur = 'Action impossible.';
    }
}

// Get all pending versions
global $pdo;
$stmt = $pdo->query("
    SELECT v.*, u.username, a.title AS article_title
    FROM articles_versions v
    LEFT JOIN users u ON u.id_user = v.id_user
    LEFT JOIN articles a ON a.id_article = v.id_article
    WHERE v.status = 'pending'
    ORDER BY v.created_at ASC
");
$pendingVersions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <h1>ADMINISTRATION</h1>
    </section>
    <div class="admin-bar">
            <button><a href="ban.php" class="admin-link">BAN</a></button>
            <button><a href="admin.php" class="admin-link">HOME</a></button>
            <button><a href="manage_users.php" class="admin-link">MANAGE_USER</a></button>
            <button><a href="manage_roles.php" class="admin-link">MANAGE_ROLE</a></button>
            <button><a href="manage_captcha.php" class="admin-link">MANAGE_CAPTCHA</a></button>
            <button><a href="manage_newsletters.php" class="admin-link">MANAGE_NEWSLETTER</a></button>
            <button><a href="manage_articles.php" class="admin-link">MANAGE_ARTICLE</a></button>
            <button><a href="manage_categories.php" class="admin-link">MANAGE_SECTOR</a></button>
            <button><a href="manage_versions.php" class="admin-link">MANAGE_VERSIONS</a></button>
            <button><a href="logs.php" class="admin-link">LOGS</a></button>
            <button><a href="index.php" class="admin-link">RETURN -> HOME</a></button>
        </div>
    <section>
        <h2>Versions en attente de validation</h2>

        <?php if ($erreur): ?>
            <p class="error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($succes): ?>
            <p class="success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!$pendingVersions): ?>
            <p>Aucune version en attente.</p>
        <?php endif; ?>

        <div class="container">
            <?php foreach ($pendingVersions as $v): ?>
                <div class="card">
                    <h3>Article : <?= htmlspecialchars($v['article_title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p>Version proposée par : <?= htmlspecialchars($v['username'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8') ?></p>
                    <p>Date : <?= htmlspecialchars($v['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p>Nouveau titre : <?= htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8') ?></p>
                    <details>
                        <summary>Voir le contenu proposé</summary>
                        <div><?= nl2br(htmlspecialchars($v['content'], ENT_QUOTES, 'UTF-8')) ?></div>
                    </details>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="id_version" value="<?= $v['id_version'] ?>">
                        <button type="submit">Approuver</button>
                    </form>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="id_version" value="<?= $v['id_version'] ?>">
                        <button type="submit">Rejeter</button>
                    </form>
                    <a href="view_article.php?id_article=<?= $v['id_article'] ?>">Voir l'article actuel</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include_once SRC . '/views/layouts/footer.php'; ?>