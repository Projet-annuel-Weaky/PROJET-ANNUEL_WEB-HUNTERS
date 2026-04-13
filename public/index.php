<?php

session_start();

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";

include_once SRC . "/views/layouts/header.php";

?>
    
<main>
<section>
    <h1>Bienvenue sur <?php echo SITE_NAME; ?> !</h1>
    <p><?php echo SITE_DESCRIPTION; ?></p>
    
</section>

</main>

<?php

include_once SRC . '/views/layouts/footer.php';

?>