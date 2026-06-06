<?php

define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/AdminService.php";
require_once SRC . "/services/MailService.php";
require_once SRC . "/services/NewsletterService.php";

AdminService::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_newsletters.php');
    exit;
}

$idNewsletter = (int)($_POST['id_newsletter'] ?? 0);

if ($idNewsletter <= 0) {
    header('Location: manage_newsletters.php?message=' . urlencode('Newsletter introuvable.'));
    exit;
}

$stmt = $pdo->prepare('SELECT id_newsletter, title, subject, content FROM newsletters WHERE id_newsletter = ?');
$stmt->execute([$idNewsletter]);
$newsletter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$newsletter) {
    header('Location: manage_newsletters.php?message=' . urlencode('Newsletter introuvable.'));
    exit;
}

$result = NewsletterService::sendNewsletter($newsletter);
$message = 'Newsletter envoyée à ' . $result['sent'] . ' destinataire(s) sur ' . $result['targeted'] . ' ciblé(s).';

header('Location: manage_newsletters.php?message=' . urlencode($message));
exit;