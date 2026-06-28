<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

if (!isset($_SESSION["id_user"])) {
    header("Location: login.php");
    exit;
}

LogService::visit("chat.php");
$extraStyles = ["/css/chat.css"];

include_once SRC . "/views/layouts/header.php";
?>

<main class="chat-main">
    <section class="chat-container" data-current-user-id="<?= (int) $_SESSION["id_user"] ?>">
        <h1>Chat général</h1>
        <div id="messages"></div>

        <div class="input-area">
            <input type="text" id="input" placeholder="Écris un message..." maxlength="1000">
            <button type="button" onclick="sendMessage()">Envoyer</button>
        </div>
    </section>
</main>

<script src="/js/chat.js" defer></script>

<?php include_once SRC . "/views/layouts/footer.php"; ?>
