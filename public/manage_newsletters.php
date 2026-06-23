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
    <?php require SRC . "/views/layouts/adminNav.php" ?>

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
                    <table id="newsletters-table" class="data-table">
                        <thead>
                            <tr><th>#</th><th>Titre</th><th>Sujet</th><th>Statut</th><th>Modifiée</th><th class="col-actions"></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($newsletters as $newsletter): ?>
                                <tr>
                                    <td><?= $newsletter['id_newsletter'] ?></td>
                                    <td><?= htmlspecialchars($newsletter['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($newsletter['subject'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($newsletter['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($newsletter['updated_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="col-actions">
                                        <button type="button" data-modal-open="newsletter-modal-<?= $newsletter['id_newsletter'] ?>">Détails</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$newsletters): ?><p class="table-empty">Aucune newsletter.</p><?php endif; ?>
                </div>

                <h2>Historique des envois</h2>
                <div class="container">
                    <table id="history-table" class="data-table">
                        <thead>
                            <tr><th>#</th><th>Titre</th><th>Sujet</th><th>Destinataires</th><th>Envoyée</th><th class="col-actions"></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $item): ?>
                                <tr>
                                    <td><?= $item['id_history'] ?></td>
                                    <td><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($item['subject'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($item['recipients_count'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($item['sent_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="col-actions">
                                        <button type="button" data-modal-open="history-modal-<?= $item['id_history'] ?>">Détails</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$history): ?><p class="table-empty">Aucun envoi.</p><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>