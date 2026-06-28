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
    <?php require SRC . "/views/layouts/adminNav.php" ?>

    <section>
        <h2>Versions en attente de validation</h2>

        <?php if ($erreur): ?>
            <p class="error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($succes): ?>
            <p class="success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!$pendingVersions): ?>
            <p class="versions-empty">Aucune version en attente de validation.</p>
        <?php endif; ?>

        <div class="container">
            <?php foreach ($pendingVersions as $v): ?>
                <div class="card version-card">
                    <div class="version-card__header">
                        <span class="version-badge version-badge--pending">En attente</span>
                        <span class="version-card__num">v<?= (int)$v['version_number'] ?></span>
                    </div>

                    <h3><?= htmlspecialchars($v['article_title'], ENT_QUOTES, 'UTF-8') ?></h3>

                    <p class="version-card__meta">
                        <span>Par <strong><?= htmlspecialchars($v['username'] ?? 'Anonyme', ENT_QUOTES, 'UTF-8') ?></strong></span>
                        <span class="version-card__date"><?= htmlspecialchars(date('d/m/Y à H:i', strtotime($v['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                    </p>

                    <p><strong>Titre proposé :</strong> <?= htmlspecialchars($v['title'], ENT_QUOTES, 'UTF-8') ?></p>

                    <details class="version-card__details">
                        <summary>Voir le contenu proposé</summary>
                        <div class="version-card__content"><?= nl2br(htmlspecialchars($v['content'], ENT_QUOTES, 'UTF-8')) ?></div>
                    </details>

                    <div class="version-card__actions">
                        <form method="POST">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id_version" value="<?= $v['id_version'] ?>">
                            <button type="submit" class="btn-approve">✓ Approuver</button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id_version" value="<?= $v['id_version'] ?>">
                            <button type="submit" class="btn-reject">✗ Rejeter</button>
                        </form>
                        <a href="view_article.php?id_article=<?= $v['id_article'] ?>">Voir l'article</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include_once SRC . '/views/layouts/footer.php'; ?>