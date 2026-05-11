<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

if (isset($_SESSION['id_user'])) {
    LogService::add('logout', 'logout.php', $_SESSION['id_user']);
}

$_SESSION = [];
session_destroy();
header('Location: index.php');
