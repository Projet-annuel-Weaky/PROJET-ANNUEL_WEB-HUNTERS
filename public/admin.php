<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

require_once SRC . "/views/layouts/header.php";

$stmt = $pdo->query("
    SELECT
        id_user,
        username,
        email,
        last_activity
    FROM users
    WHERE last_activity >= NOW() - INTERVAL 5 MINUTE
    ORDER BY username ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<main>
    <?php require SRC . "/views/layouts/adminNav.php" ?>
    
    <section>
    <h2>Online users</h2>

    <div class="container">
        <?php foreach ($users as $user): ?>
            <div class="card">
                <h3>
                    #<?= htmlspecialchars($user['id_user'], ENT_QUOTES, 'UTF-8') ?>
                    - <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
                <?php if (($_SESSION['role_id'] ?? 0) == 2): ?>
                    <p>Email : <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($user['last_activity']): ?>
                    <p>Last seen : <?= htmlspecialchars($user['last_activity'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
