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