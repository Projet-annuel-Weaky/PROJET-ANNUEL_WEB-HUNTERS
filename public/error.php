<?php
// Central error page used by Apache ErrorDocument and internal dispatcher
define("ROOT", dirname(__DIR__));
define("SRC", ROOT . "/src");

// Apache sets REDIRECT_STATUS when invoking ErrorDocument
$code = $_SERVER['REDIRECT_STATUS'] ?? ($_GET['code'] ?? http_response_code());
http_response_code((int)$code);

// Render within site layout for consistency
include_once SRC . "/views/layouts/header.php";
include SRC . "/views/errors.php";
include_once SRC . "/views/layouts/footer.php";

?>
