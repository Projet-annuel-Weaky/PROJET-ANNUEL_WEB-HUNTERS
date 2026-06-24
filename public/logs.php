<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

if (isset($_POST['delete_all'])) {
    $sql = "DELETE FROM logs";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['delete_one'])) {
    $id_log = $_POST['id_log'];

    $sql = "DELETE FROM logs WHERE id_log = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_log]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$allowedSorts = [
    'id_log',
    'id_user',
    'username',
    'action',
    'page',
    'ip_address',
    'created_at'
];

$sort = $_GET['sort'] ?? 'id_log';
$order = strtoupper($_GET['order'] ?? 'ASC');

if (!in_array($sort, $allowedSorts)) {
    $sort = 'id_log';
}

if (!in_array($order, ['ASC', 'DESC'])) {
    $order = 'ASC';
}

$sql = "
SELECT
    l.id_log,
    l.id_user,
    u.username,
    l.action,
    l.page,
    l.ip_address,
    l.user_agent,
    l.created_at
FROM logs l
LEFT JOIN users u ON u.id_user = l.id_user
ORDER BY $sort $order
";

$stmt = $pdo->query($sql);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <article>
            <h1>ADMINISTRATION</h1>
        </article>

        <div class="admin-bar">
            <button><a href="admin.php" class="admin-link">HOME</a></button>
            <button><a href="manage_users.php" class="admin-link">MANAGE_USER</a></button>
            <button><a href="manage_roles.php" class="admin-link">MANAGE_ROLE</a></button>
            <button><a href="manage_captcha.php" class="admin-link">MANAGE_CAPTCHA</a></button>
            <button><a href="manage_newsletters.php" class="admin-link">MANAGE_NEWSLETTER</a></button>
            <button><a href="manage_articles.php" class="admin-link">MANAGE_ARTICLE</a></button>
            <button><a href="manage_categories.php" class="admin-link">MANAGE_SECTOR</a></button>
            <button><a href="logs.php" class="admin-link">LOGS</a></button>
            <button><a href="index.php" class="admin-link">RETURN -> HOME</a></button>
        </div>

        <h2>Liste des logs</h2>

        <div class="logs-toolbar">
            <input type="text" id="logFilter" placeholder="Filtrer par action, utilisateur, page, ip...">

            <form method="GET">
                <select name="sort">
                    <option value="id_log" <?= $sort === 'id_log' ? 'selected' : '' ?>>ID Log</option>
                    <option value="id_user" <?= $sort === 'id_user' ? 'selected' : '' ?>>ID Utilisateur</option>
                    <option value="username" <?= $sort === 'username' ? 'selected' : '' ?>>Nom utilisateur</option>
                    <option value="action" <?= $sort === 'action' ? 'selected' : '' ?>>Action</option>
                    <option value="page" <?= $sort === 'page' ? 'selected' : '' ?>>Page</option>
                    <option value="ip_address" <?= $sort === 'ip_address' ? 'selected' : '' ?>>IP</option>
                    <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Date</option>
                </select>

                <select name="order">
                    <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Croissant</option>
                    <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Décroissant</option>
                </select>

                <button type="submit">Trier</button>
            </form>

            <form method="POST">
                <button type="submit" name="delete_all">Supprimer tous les logs</button>
            </form>
        </div>

        <div class="logs-list">
            <?php foreach ($logs as $log): ?>
                <div class="log-row" data-log-row>
                    <div class="log-main">
                        <span class="log-id">#<?= htmlspecialchars($log['id_log']) ?></span>
                        <strong class="log-action"><?= htmlspecialchars($log['action']) ?></strong>
                        <span><?= htmlspecialchars($log['username'] ?? 'Visiteur') ?></span>
                        <span>ID <?= htmlspecialchars($log['id_user'] ?? '-') ?></span>
                    </div>

                    <div class="log-meta">
                        <span><?= htmlspecialchars($log['page']) ?></span>
                        <span><?= htmlspecialchars($log['ip_address'] ?? '') ?></span>
                        <span><?= htmlspecialchars($log['created_at']) ?></span>
                    </div>

                    <p class="log-agent"><?= htmlspecialchars($log['user_agent'] ?? '') ?></p>

                    <form method="POST" class="log-action-form">
                        <input type="hidden" name="id_log" value="<?= $log['id_log'] ?>">
                        <button type="submit" name="delete_one">Supprimer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
