<?php

require_once __DIR__ . '/MailService.php';

class NewsletterService
{
    public static function getRecipients()
    {
        global $pdo;

        $stmt = $pdo->query('SELECT email, username FROM users WHERE is_verified = 1 AND newsletter_enabled = 1 ORDER BY id_user ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function sendNewsletter($newsletter)
    {
        global $pdo;

        $recipients = self::getRecipients();
        $sentCount = 0;

        foreach ($recipients as $recipient) {
            $message = "Bonjour " . $recipient['username'] . ",\n\n";
            $message .= $newsletter['content'];

            if (MailService::sendEmail($recipient['email'], $newsletter['subject'], $message)) {
                $sentCount++;
            }
        }

        $stmt = $pdo->prepare('INSERT INTO newsletter_history (id_newsletter, title, subject, content, recipients_count) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $newsletter['id_newsletter'],
            $newsletter['title'],
            $newsletter['subject'],
            $newsletter['content'],
            count($recipients)
        ]);

        $stmt = $pdo->prepare("UPDATE newsletters SET status = 'sent' WHERE id_newsletter = ?");
        $stmt->execute([$newsletter['id_newsletter']]);

        return [
            'targeted' => count($recipients),
            'sent' => $sentCount
        ];
    }
}