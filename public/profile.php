<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

LogService::visit('profile.php');

$username = $_SESSION['username'];
$email    = $_SESSION['email'];
$roleId   = $_SESSION['role_id'];
$bio      = $_SESSION['bio'] ?? '';
$roleName = ($roleId == 2) ? 'Admin' : 'User';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail    = trim($_POST['email'] ?? '');
    $userId      = $_SESSION['id_user'];

    if (empty($newUsername) || empty($newEmail)) {
        $_SESSION['error'] = "Username and email cannot be empty.";
        header("Location: profile.php"); exit;
    }
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email address.";
        header("Location: profile.php"); exit;
    }
    if (strlen($newUsername) > 100) {
        $_SESSION['error'] = "Username too long.";
        header("Location: profile.php"); exit;
    }

    $stmt = $pdo->prepare("SELECT id_user FROM users WHERE (username = ? OR email = ?) AND id_user != ?");
    $stmt->execute([$newUsername, $newEmail, $userId]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "l' username ou le mail est le même.";
        header("Location: profile.php"); exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id_user = ?");
    $stmt->execute([$newUsername, $newEmail, $userId]);

    $_SESSION['username'] = $newUsername;
    $_SESSION['email']    = $newEmail;
    $_SESSION['success']  = "profil mise à jour.";
    header("Location: profile.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bio'])) {
    $newBio = trim($_POST['bio'] ?? '');
    $userId = $_SESSION['id_user'];

    if (strlen($newBio) > 255) {
        $_SESSION['error'] = "Bio too long (max 255 characters).";
        header("Location: profile.php"); exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id_user = ?");
    $stmt->execute([$newBio, $userId]);

    $_SESSION['bio']    = $newBio;
    $_SESSION['success'] = "Bio updated successfully.";
    header("Location: profile.php"); exit;
}

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <article>
        <h1>PROFIL</h1>
    </article>
    <section>
        <span id="pp">
        <?php $currentPicture = $_SESSION['avatar'] ?? 'DEFAULT_pp.png'; ?>
        <img src="../assets/pp/<?= htmlspecialchars($currentPicture, ENT_QUOTES, 'UTF-8') ?>"
         alt="Profile picture"
         width="150">
        </span>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="avatar" accept="image/*" required>
        <button type="submit">import</button>
        </form>
       <div class="profile-info">
    <h2>Informations personnelles</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <p class="error"><?= htmlspecialchars($_SESSION['error']) ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <p class="success"><?= htmlspecialchars($_SESSION['success']) ?></p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

        <form action="profile.php" method="POST">
            <input type="hidden" name="update_profile" value="1">

            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                   maxlength="100" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                   maxlength="255" required>

            <p>Rôle : <?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?></p>

            <button type="submit">Save changes</button>
        </form>
    </div>
    <div class="profile-bio">
    <h2>Biographie</h2>
    <form action="profile.php" method="POST">
        <input type="hidden" name="update_bio" value="1">

        <textarea id="bio" name="bio" 
                  maxlength="255" 
                  rows="4"
                  placeholder="..."
        ><?= htmlspecialchars($bio, ENT_QUOTES, 'UTF-8') ?></textarea>

        <button type="submit">Save bio</button>
    </form>
    </div>
    </section>
</main>

<?php
include_once SRC . '/views/layouts/footer.php';
?>

