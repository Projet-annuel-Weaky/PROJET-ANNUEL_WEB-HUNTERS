<?php
$code = http_response_code();
$errorMessages = [
    403 => ['title' => 'Accès Interdit', 'message' => 'Vous n\'avez pas les droits pour accéder à cette page.'],
    404 => ['title' => 'Page non trouvée', 'message' => 'La page que vous recherchez n\'existe pas.'],
    500 => ['title' => 'Erreur Serveur', 'message' => 'Une erreur interne s\'est produite.']
];

$error = $errorMessages[$code] ?? ['title' => 'Erreur', 'message' => 'Une erreur inconnue s\est produite.'];
?>

<div>
    <h1><?php echo $code; ?></h1>
    <h2><?php echo $error['title']; ?></h2>
    <p><?php echo $error['message']; ?></p>
    <a href="/">Retour à l'accueil</a>
</div>