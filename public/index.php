<?php
session_start();

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";

require_once SRC . "/middleware/Router.php";

$router = new Router();

$router->addRoute('GET', '/', function() {
    include SRC . "/views/pages/home.php";
});
?>

<!DOCTYPE html>
<html lang="fr" data-theme='dark'>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_DESCRIPTION; ?></title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
<?php
include_once SRC . "/views/layouts/header.php";

$router->dispatch();

include_once SRC . '/views/layouts/footer.php';
?>
<script src="js/main.js"></script>