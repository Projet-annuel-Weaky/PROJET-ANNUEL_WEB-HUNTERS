<?php
$connected = isset($_SESSION['id_user']);
$displayUsername = $connected ? ($_SESSION['username'] ?? 'Utilisateur') : 'Invité';
$avatarLabel = $connected && $displayUsername !== '' ? strtoupper(substr($displayUsername, 0, 1)) : 'I';
$userRole = $connected && (($_SESSION['role_id'] ?? 0) == 2) ? 'Admin' : ($connected ? 'Membre' : 'Non connecté');
?>

<div class='header-profile'>
    <div class='user-profile-container'>
        <button class="profile-button">
            <div class='user-profile' id='userProfile'>
                <span class='avatar'><?= htmlspecialchars($avatarLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="user-text">
                    <span class='username'><?= htmlspecialchars($displayUsername, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="user-role"><?= htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8'); ?></span>
                </span>
                <?php if ($connected): ?>
                    <span class="level-badge">Lvl 1</span>
                <?php endif; ?>
            </div>
        </button>
    </div>
</div>

<?php if ($connected): ?>
    <div class='profile-dropdown-connected'>
        <aside>
            <ul>
                <li><button><a href="profile.php">Mon Profil</a></button></li>
                <?php if (($_SESSION['role_id'] ?? 0) == 2): ?>
                    <li><button><a href="admin.php">Administration</a></button></li>
                <?php endif; ?>
                <li><button><a href="logout.php">Déconnexion</a></button></li>
            </ul>
        </aside>
    </div>
<?php else: ?>
    <div class='profile-dropdown-disconnected'>
        <aside>
            <ul>
                <li><button><a href="signup.php">Inscription</a></button></li>
                <li><button><a href="login.php">Se Connecter</a></button></li>
            </ul>
        </aside>
    </div>
<?php endif; ?>
