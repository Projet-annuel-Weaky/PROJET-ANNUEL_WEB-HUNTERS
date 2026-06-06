<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/MailService.php";
require_once SRC . "/services/LogService.php";

$erreur = '';
$succes = '';
$extraStyles = ['captcha/style.css'];

LogService::visit('signup.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $roleId = 1;
    $captchaOk = !empty($_SESSION['captcha_valid'])
        && !empty($_SESSION['captcha_validated_at'])
        && time() - $_SESSION['captcha_validated_at'] <= 300
        && !empty($_POST['captcha_token'])
        && !empty($_SESSION['captcha_token'])
        && hash_equals($_SESSION['captcha_token'], $_POST['captcha_token']);

    unset($_SESSION['captcha_valid'], $_SESSION['captcha_validated_at'], $_SESSION['captcha_expected_order'], $_SESSION['captcha_image_id'], $_SESSION['captcha_token']);

    if (!$captchaOk) {
        $erreur = 'Veuillez compléter le captcha avant de vous inscrire.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $erreur = 'Le nom d\'utilisateur doit contenir entre 3 et 20 caractères.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Veuillez entrer un email valide.';
    } elseif (strlen($password) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $stmt = $pdo->prepare('SELECT id_user FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $erreur = 'Ce nom d\'utilisateur est déjà pris.';
        } else {
            $stmt = $pdo->prepare('SELECT id_user FROM users WHERE email = ?');
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $erreur = 'Cet email est déjà utilisé.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 86400);

                $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role_id, is_verified, verification_token, verification_expires_at, created_at) VALUES (?, ?, ?, ?, 0, ?, ?, ?)');
                $stmt->execute([
                    $username,
                    $email,
                    $hash,
                    $roleId,
                    $token,
                    $expiresAt,
                    date('Y-m-d H:i:s')
                ]);

                $mailSent = MailService::sendVerificationEmail($email, $username, $token);

                if ($mailSent) {
                    $succes = 'Compte créé. Vérifiez votre email pour activer votre compte.';
                } else {
                    $succes = 'Compte créé. L’envoi de l’email doit être configuré pour recevoir le lien de vérification.';
                }
            }
        }
    }
}

include_once SRC . "/views/layouts/header.php";
?>

<main>
    <section>
        <h1>Inscription</h1>
        <?php if ($erreur): ?>
            <div class="error"><?php echo $erreur; ?></div>
        <?php endif; ?>
        <?php if ($succes): ?>
            <div class="success"><?php echo $succes; ?></div>
        <?php endif; ?>
        <?php if (!$succes): ?>
        <form method="POST">
            <label for="username">username :</label>
            <input type="text" id="username" name="username" required>

            <label for="email">email :</label>
            <input type="email" id="email" name="email" required>

            <label for="password">password :</label>
            <input type="password" id="password" name="password" required>

            <div class="captcha-login-block">
                <p class="section-title">Captcha</p>
                <p style="margin-bottom:10px;">Rearrange the pieces into the correct order</p>

                <div id="game">
                    <div id="parts"></div>
                </div>

                <div class="captcha-actions">
                    <button type="button" id="validateBtn">Validate</button>
                    <button type="button" id="resetBtn">Reset puzzle</button>
                </div>

                <p id="status" class="status-message"></p>
            </div>

            <button type="submit">sign up</button>
        </form>
        <?php endif; ?>
    </section>
</main>

<script src="captcha/captcha.js"></script>
<script>
    window.captchaSolved = false;

    const signupForm = document.querySelector('form');
    const captchaStatus = document.getElementById('status');

    if (signupForm) {
        signupForm.addEventListener('submit', function (event) {
            if (!window.captchaSolved) {
                event.preventDefault();
                if (captchaStatus) {
                    captchaStatus.textContent = 'Veuillez valider le captcha avant de continuer.';
                    captchaStatus.classList.remove('success-message');
                    captchaStatus.classList.add('fail-message');
                }
            }
        });
    }
</script>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
