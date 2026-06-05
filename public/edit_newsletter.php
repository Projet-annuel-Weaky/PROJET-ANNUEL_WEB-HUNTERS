<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

$idNewsletter = (int)($_GET['id_newsletter'] ?? $_POST['id_newsletter'] ?? 0);
$erreur = '';
$succes = '';

if ($idNewsletter <= 0) {
    header('Location: manage_newsletters.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $subject === '' || $content === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare("UPDATE newsletters SET title = ?, subject = ?, content = ?, status = 'draft' WHERE id_newsletter = ?");
        $stmt->execute([$title, $subject, $content, $idNewsletter]);
        $succes = 'Newsletter modifiée.';
    }
}

$stmt = $pdo->prepare('SELECT id_newsletter, title, subject, content, status FROM newsletters WHERE id_newsletter = ?');
$stmt->execute([$idNewsletter]);
$newsletter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$newsletter) {
    header('Location: manage_newsletters.php');
    exit;
}

require_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <article>
            <h1>ADMINISTRATION</h1>
        </article>
        <div class="admin-bar">
            <button><a href="admin.php" class="admin-link">HOME</a></button>
            <button><a href="manage_newsletters.php" class="admin-link">MANAGE_NEWSLETTER</a></button>
            <button><a href="index.php" class="admin-link">RETURN -> HOME</a></button>
        </div>

        <h2>Modifier newsletter</h2>
        <?php if ($erreur): ?>
            <p class="error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($succes): ?>
            <p class="success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="id_newsletter" value="<?= $newsletter['id_newsletter'] ?>">
            <input type="text" name="title" value="<?= htmlspecialchars($newsletter['title'], ENT_QUOTES, 'UTF-8') ?>" required>
            <input type="text" name="subject" value="<?= htmlspecialchars($newsletter['subject'], ENT_QUOTES, 'UTF-8') ?>" required>
            <textarea name="content" required><?= htmlspecialchars($newsletter['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
            <button type="submit">Enregistrer</button>
        </form>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
