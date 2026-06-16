<?php
define("ROOT", dirname(__DIR__));
define("SRC", ROOT . "/src");

$code = $_SERVER['REDIRECT_STATUS'] ?? ($_GET['code'] ?? http_response_code());
http_response_code((int)$code);

include_once SRC . "/views/layouts/header.php";
include SRC . "/views/errors.php";
include_once SRC . "/views/layouts/footer.php";

?>
