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
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    $path = isset($params['path']) ? $params['path'] : '/';
    $domain = isset($params['domain']) ? $params['domain'] : '';
    $secure = isset($params['secure']) ? $params['secure'] : false;
    $httponly = isset($params['httponly']) ? $params['httponly'] : false;

    setcookie(session_name(), '', time() - 42000, $path, $domain, $secure, $httponly);
}
session_unset();
session_destroy();
header('Location: index.php');
