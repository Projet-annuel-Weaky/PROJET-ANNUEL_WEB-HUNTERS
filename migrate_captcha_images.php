<?php
define("ROOT", __DIR__);
define("CONFIG", ROOT . "/configs");
require_once CONFIG . "/config.php";

$uploadDir = ROOT . '/assets/captcha/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    echo "Dossier créé : $uploadDir<br>";
}

$rows = $pdo->query("SELECT id, filename, image_data, mime_type FROM captcha_images WHERE image_data IS NOT NULL")
            ->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "Aucune image à migrer (image_data vide ou colonne inexistante).<br>";
    exit;
}

foreach ($rows as $row) {
    $ext = pathinfo($row['filename'], PATHINFO_EXTENSION) ?: 'jpg';
    $newFilename = uniqid('captcha_', true) . '.' . $ext;
    $targetPath = $uploadDir . $newFilename;

    if (file_put_contents($targetPath, $row['image_data']) === false) {
        echo "❌ Échec écriture pour id={$row['id']}<br>";
        continue;
    }

    $stmt = $pdo->prepare("UPDATE captcha_images SET filename = ? WHERE id = ?");
    $stmt->execute([$newFilename, $row['id']]);

    echo "✅ Migré id={$row['id']} → $newFilename<br>";
}

echo "<br><strong>Migration terminée. Supprimez ce fichier maintenant.</strong><br>";
echo "Vous pouvez ensuite exécuter : <code>ALTER TABLE captcha_images DROP COLUMN image_data;</code>";