<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

LogService::visit('profile.php');

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <article>
        <h1>PROFIL</h1>
    </article>
    <section>
        <div class="profile-info">
            <h2>Informations personnelles</h2>
            <p>Username : <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <p>Email : <?= htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
