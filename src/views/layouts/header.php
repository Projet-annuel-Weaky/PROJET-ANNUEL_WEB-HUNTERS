<?php
include_once __DIR__ . "/../../../configs/config.php";

$assetRoot = __DIR__ . "/../../../assets";
$logoSrc =
    "data:image/png;base64," .
    base64_encode(
        file_get_contents($assetRoot . "/logo/logo_Web-Hunters_WEAKY.png"),
    );
$menuIconSrc =
    "data:image/svg+xml;base64," .
    base64_encode(file_get_contents($assetRoot . "/svg/search/3bars.svg"));
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars(
            SITE_NAME,
            ENT_QUOTES,
            "UTF-8",
        ) ?> - <?= htmlspecialchars(
     SITE_DESCRIPTION,
     ENT_QUOTES,
     "UTF-8",
 ) ?></title>
        <link rel="stylesheet" href="css/main.css">
        <link rel="icon" href="<?= htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8'); ?>" type="image/png">
        <?php foreach ($extraStyles ?? [] as $style): ?>
            <link rel="stylesheet" href="<?= htmlspecialchars(
                $style,
                ENT_QUOTES,
                "UTF-8",
            ) ?>">
        <?php endforeach; ?>
        <script src="./js/main-function.js" defer></script>
        <script src="./js/search-global.js" defer></script>
        <?php if (!empty($_SESSION['id_user'])): ?>
        <script>
        (function(){
            const TIMEOUT_SEC = <?= (int)(defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : (int) ini_get('session.gc_maxlifetime')) ?>;
            let _idleTimer;
            function _resetIdle(){ clearTimeout(_idleTimer); _idleTimer = setTimeout(function(){ window.location.href = 'login.php?timeout=1'; }, TIMEOUT_SEC * 1000); }
            ['mousemove','mousedown','keydown','touchstart','scroll','click'].forEach(function(e){ document.addEventListener(e, _resetIdle, true); });
            _resetIdle();
        })();
        </script>
        <?php endif; ?>
    </head> <body class="darkMode">
        <header>
            <div class ="header-left">
                <span>
                    <button class="menu-toggle" type="button">
                    <img class="bars-icon" src="<?= htmlspecialchars(
                        $menuIconSrc,
                        ENT_QUOTES,
                        "UTF-8",
                    ) ?>" alt="Menu">
                    </button>
                </span>
                <div class='logo-site'>
                    <a href='index.php' class='logo-link'>
                        <div class='logo'>
                           <span class='logo-mark'>
                           <img class='logo-image' src="<?= htmlspecialchars(
                               $logoSrc,
                               ENT_QUOTES,
                               "UTF-8",
                           ) ?>" alt="Logo WEAKY">
                               </span>
                               <span class="logo-name">WEAKY</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class='search-bar'>
                <button class='search-icon' type="button"> <?php include __DIR__ .
                    "/../../../assets/svg/search/search.svg"; ?> </button>
                <input type='text' class='search-input' placeholder='Rechercher un article, un thème...' id='searchInput'>
            </div>
            <div class='header-right'>
                <?php include_once __DIR__ . "/../users/userMenu.php"; ?>
            </div>
            </header>
                <div class='menu-dropdown'>
                    <aside>
                        <a href="article.php"><button>NAVIGATION</button></a>
                        <a href="category.php"><button>CATEGORIES</button></a>
                        <a href="user.php"><button>UTILISATEURS</button></a>
                        <a href="chat.php"><button>CHAT</button></a>
                    </aside>
                </div>
