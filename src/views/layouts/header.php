<!DOCTYPE html>
    <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo SITE_NAME; ?> - <?php echo SITE_DESCRIPTION; ?></title>
            <link rel="stylesheet" href="css/main.css">
        </head>
        <body>
            <header>
                <div class ="header-left">
                    <div class ="menu">
                        <span class ="bars-icon">
                            <img class ="bars-icon" src="../assets/svg/search/3barsv2.svg" width="50"/>
                        </span>
                    </div>
                    <div class='logo-site'>
                        <a href='index.php' class='logo-link'>
                            <div class='logo'>
                                <span class='logo-icon'>
                                    <img class='logo-icon' src="../assets/logo/logo_Web-Hunters_WEAKY.png" alt="Logo de WEAKY" />
                                </span>
                              weaky  
                            </div>
                            
                        </a>
                </div>
                <div class='search-bar'>
                    <span class='search-icon' >
                        <?php include ROOT . "../assets/svg/search/search.svg";  ?>
                    </span>
                    <input type='text' class='search-input' placeholder='Rechercher un article, un thème...' id='searchInput'>
                </div>
                <div class='header-right'>
                    <div class='header-profile'>
                        <div class='user-profile-container'>
                            <div class='user-profile' id='userProfile'>
                                <div class='avatar'><?php echo CURRENT_USER_INITIAL; ?></div>
                                <span class='username'><?php echo CURRENT_USER; ?></span>
                                <div class="level-badge">Lvl <?php echo CURRENT_USER_LEVEL; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class='history'>
                        <span>
                            <img class ="history-logo" src="../assets/svg/search/clock-illuv2.svg" width="70"/>
                        </span>
                    </div>
                </div>
            </header>