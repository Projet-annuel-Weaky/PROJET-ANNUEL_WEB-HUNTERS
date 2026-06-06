<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

$erreur = '';
if (!empty($_GET['timeout'])) {
    $erreur = 'Votre session a expiré pour inactivité. Veuillez vous reconnecter.';
}
$extraStyles = ['captcha/style.css'];

LogService::visit('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $captchaOk = !empty($_SESSION['captcha_valid'])
        && !empty($_SESSION['captcha_validated_at'])
        && time() - $_SESSION['captcha_validated_at'] <= 300
        && !empty($_POST['captcha_token'])
        && !empty($_SESSION['captcha_token'])
        && hash_equals($_SESSION['captcha_token'], $_POST['captcha_token']);

    unset($_SESSION['captcha_valid'], $_SESSION['captcha_validated_at'], $_SESSION['captcha_expected_order'], $_SESSION['captcha_image_id'], $_SESSION['captcha_token']);

    if (!$captchaOk) {
        $erreur = 'Veuillez compléter le captcha avant de vous connecter.';
        LogService::add('login_failed', 'login.php');
    } elseif ($username === '' || $password === '') {
        $erreur = 'Veuillez remplir tous les champs.';
        LogService::add('login_failed', 'login.php');
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_verified']) {
                $erreur = 'Votre compte doit être vérifié par email avant la connexion.';
                LogService::add('login_failed', 'login.php', $user['id_user']);
            } else {
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role_id'] = $user['role_id'] ?? 1;
                // track last activity for auto-logout
                $_SESSION['last_activity'] = time();
                // prevent session fixation
                session_regenerate_id(true);

                LogService::add('login_success', 'login.php', $user['id_user']);

                header('Location: index.php');
                exit;
            }
        } else {
            $erreur = 'Nom d’utilisateur ou mot de passe incorrect.';
            LogService::add('login_failed', 'login.php', $user['id_user'] ?? null);
        }
    }
}

include_once SRC . "/views/layouts/header.php";
?>

    <main class="auth-main">
        <div class="auth-container">
            <div class="card auth-card">
                <h2>Connexion</h2>

                <?php if (!empty($erreur)): ?>
                    <p class="status-message fail-message">
                        <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>

                <form method="POST" id="loginForm" class="login-form">
                    <div class="login-grid">
                        <div class="login-fields">
                            <div class="form-field">
                                <label for="username">Nom d'utilisateur</label>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    required
                                >
                            </div>

                            <div class="form-field">
                                <label for="password">Mot de passe</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                >
                            </div>

                            <button type="submit">
                                Se connecter
                            </button>
                        </div>

                        <div class="captcha-login-block">
                            <p class="section-title">Captcha</p>
                            <p>Rearrange the pieces into the correct order</p>

                            <div id="game">
                                <div id="parts"></div>
                            </div>

                            <div class="captcha-actions">
                                <button type="button" id="validateBtn">Validate</button>
                                <button type="button" id="resetBtn">Reset puzzle</button>
                            </div>

                            <p id="status" class="status-message"></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="captcha/captcha.js"></script>
    <script>
        window.captchaSolved = false;

        const loginForm = document.getElementById('loginForm');
        const captchaStatus = document.getElementById('status');

        if (loginForm) {
            loginForm.addEventListener('submit', function (event) {
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
