<?php
//0 2 * * * /usr/bin/php /var/www/wiki/remind_inactive_users.php >> /var/log/remind_inactive_users.log 2>&1
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('CONFIG', ROOT . '/configs');
define('SRC', ROOT . '/src');

require_once CONFIG . '/config.php';
require_once SRC . '/services/MailService.php';

$sql = "
SELECT id_user, username, email, last_activity, reminder_count
FROM users
WHERE is_verified = 1
  AND is_banned = 0
  AND newsletter_enabled = 1
  AND last_activity IS NOT NULL
  AND last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND (
      last_reminder_at IS NULL
      OR last_reminder_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
  )
  AND reminder_count < 3
";

$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $subject = 'On ne vous a pas vu depuis un moment - ' . SITE_NAME;

    $message = "Bonjour {$user['username']},\n\n";
    $message .= "Cela fait un moment que vous ne vous êtes pas connecté à votre compte.\n";
    $message .= "Revenez faire un tour sur " . SITE_NAME . ".\n\n";
    $message .= "À bientôt,\n" . SITE_NAME;

    $sent = MailService::sendEmail($user['email'], $subject, $message);

    if ($sent) {
        $update = $pdo->prepare("
            UPDATE users
            SET last_reminder_at = NOW(),
                reminder_count = reminder_count + 1
            WHERE id_user = ?
        ");
        $update->execute([$user['id_user']]);
    }
}
