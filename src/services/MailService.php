<?php

class MailService
{
    public static function sendEmail($email, $subject, $message)
    {
        $headers = [];

        if (MAIL_FROM_EMAIL !== '') {
            $fromName = MAIL_FROM_NAME !== '' ? MAIL_FROM_NAME : SITE_NAME;
            $headers[] = 'From: ' . $fromName . ' <' . MAIL_FROM_EMAIL . '>';
        }

        return mail($email, $subject, $message, implode("\r\n", $headers));
    }

    public static function sendVerificationEmail($email, $username, $token)
    {
        $siteUrl = rtrim(SITE_URL, '/');
        $link = $siteUrl . '/verify.php?token=' . urlencode($token);
        $subject = 'Verification de votre compte ' . SITE_NAME;
        $message = "Bonjour " . $username . ",\n\n";
        $message .= "Cliquez sur ce lien pour verifier votre compte :\n";
        $message .= $link . "\n\n";
        $message .= "Ce lien expire dans 24 heures.";

        return self::sendEmail($email, $subject, $message);
    }
}
