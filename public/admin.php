<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";

AdminService::requireAdmin();

require_once SRC . "/views/layouts/header.php";
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
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
