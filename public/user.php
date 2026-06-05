<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

LogService::visit('user.php');

include_once SRC . "/views/layouts/header.php";
?>
    
<main>
    <section>
        <article>
            <h1>UTILISATEURS</h1>
        </article>
    </section>

<?php
$sql = "SELECT id_user, username, email FROM users ORDER BY id_user ASC";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Liste des utilisateurs</h2>

<div class="container">

<?php foreach ($users as $user): ?>
        <div class="card">
            <h3>#<?= htmlspecialchars($user['id_user'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p>Email : <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
<?php endforeach; ?>

</div>

</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
