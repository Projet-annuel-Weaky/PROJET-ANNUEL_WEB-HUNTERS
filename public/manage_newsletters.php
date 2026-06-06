<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

$erreur = '';
$succes = $_GET['message'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $subject === '' || $content === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO newsletters (title, subject, content, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$title, $subject, $content, 'draft']);
        $succes = 'Newsletter créée.';
    }
}

$newsletters = $pdo->query('SELECT id_newsletter, title, subject, status, created_at, updated_at FROM newsletters ORDER BY id_newsletter DESC')->fetchAll(PDO::FETCH_ASSOC);
$history = $pdo->query('SELECT id_history, id_newsletter, title, subject, content, recipients_count, sent_at FROM newsletter_history ORDER BY id_history DESC')->fetchAll(PDO::FETCH_ASSOC);

require_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <article>
            <h1>ADMINISTRATION</h1>
        </article>
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

        <div class="admin-layout">
            <div class="admin-panel">
                <h2>Créer une newsletter</h2>
                <?php if ($erreur): ?>
                    <p class="error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <p class="success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="title" placeholder="Titre" required>
                    <input type="text" name="subject" placeholder="Sujet email" required>
                    <textarea name="content" placeholder="Contenu" required></textarea>
                    <button type="submit">Créer</button>
                </form>
            </div>
            <div class="admin-list">
                <h2>Newsletters créées</h2>
                <div class="container">
                    <?php foreach ($newsletters as $newsletter): ?>
                        <div class="card">
                            <h3>#<?= $newsletter['id_newsletter'] ?> - <?= htmlspecialchars($newsletter['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Sujet : <?= htmlspecialchars($newsletter['subject'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p>Status : <?= htmlspecialchars($newsletter['status'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p>Modifiée : <?= htmlspecialchars($newsletter['updated_at'], ENT_QUOTES, 'UTF-8') ?></p>
                            <a href="edit_newsletter.php?id_newsletter=<?= $newsletter['id_newsletter'] ?>">Modifier</a>
                            <form action="send_newsletter.php" method="POST">
                                <input type="hidden" name="id_newsletter" value="<?= $newsletter['id_newsletter'] ?>">
                                <button type="submit">Envoyer</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h2>Historique des envois</h2>
                <div class="container">
                    <?php foreach ($history as $item): ?>
                        <div class="card">
                            <h3>#<?= $item['id_history'] ?> - <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Newsletter : <?= htmlspecialchars($item['id_newsletter'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <p>Sujet : <?= htmlspecialchars($item['subject'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p>Contenu : <?= nl2br(htmlspecialchars($item['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <p>Destinataires : <?= htmlspecialchars($item['recipients_count'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p>Envoyée : <?= htmlspecialchars($item['sent_at'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
