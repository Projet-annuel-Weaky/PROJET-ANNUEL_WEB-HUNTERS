<?PHP
session_start();

function connexion() {
    try {
        $bdd = new PDO('mysql:host=localhost;dbname=camagru;charset=utf8', 'root', 'root');
        $db = new PDO($bdd, 'root', '', array(PDO::ATTR_PERSISTENT => true))
        return $bdd;
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
}