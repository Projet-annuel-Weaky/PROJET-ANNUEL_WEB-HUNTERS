<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

class MailService
{
    public static function sendEmail($to, $subject, $message)
    {
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("Mail error: Invalid recipient email: " . $to);
            return false;
        }

        if (empty($subject) || empty($message)) {
            error_log("Mail error: Subject or message is empty");
            return false;
        }

        if (!defined('MAIL_HOST') || !defined('MAIL_PORT') || !defined('MAIL_USERNAME') || !defined('MAIL_PASSWORD')) {
            error_log("Mail error: Mail configuration constants are not defined");
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = MAIL_PORT;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($to);

            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->isHTML(false);

            return $mail->send();

        } catch (Exception $e) {
            error_log("Mail error: " . $mail->ErrorInfo);
            return false;
        }
    }

    public static function sendVerificationEmail($email, $username, $token)
    {
        $siteUrl = rtrim(SITE_URL, '/');
        $link = $siteUrl . '/verify.php?token=' . urlencode($token);
        $subject = 'Vérification de votre compte - ' . SITE_NAME;
        $message = "Bonjour " . htmlspecialchars($username) . ",\n\n";
        $message .= "Cliquez sur ce lien pour vérifier votre compte :\n";
        $message .= $link . "\n\n";
        $message .= "Ce lien expire dans 24 heures.\n\n";
        $message .= "Si vous n'avez pas créé de compte, ignorez cet email.\n\n";
        $message .= "Cordialement,\n" . SITE_NAME;

        return self::sendEmail($email, $subject, $message);
    }
}
