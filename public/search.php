<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$q    = trim($body['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode(['results' => ['articles' => [], 'users' => [], 'categories' => []], 'count' => 0, 'query' => $q]);
    exit;
}

$like = '%' . $q . '%';
$allResults = ['articles' => [], 'users' => [], 'categories' => []];
$totalCount = 0;

try {
    $stmt = $pdo->prepare("
    SELECT a.id_article,
           a.title,
           a.content,
           a.status,
           a.created_at,
           u.username      AS auteur,
           c.name          AS categorie
    FROM articles a
    LEFT JOIN users      u ON u.id_user     = a.id_user
    LEFT JOIN categories c ON c.id_category = a.id_category
    WHERE a.status = 'published'
      AND (
           a.title   LIKE :q
        OR a.content LIKE :q
      )
    ORDER BY a.created_at DESC
    LIMIT 20
    ");
    $stmt->execute([':q' => $like]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($articles as &$article) {
        $article['excerpt'] = mb_substr(strip_tags($article['content']), 0, 100) . '…';
        unset($article['content']);
    }

    $allResults['articles'] = $articles;
    $totalCount += count($articles);

    $stmt = $pdo->prepare("
        SELECT u.id_user,
               u.username,
               u.bio,
               r.name AS role
        FROM users u
        JOIN roles r ON r.role_id = u.role_id
        WHERE u.username LIKE :q
           OR u.bio      LIKE :q
        ORDER BY u.username ASC
        LIMIT 15
    ");
    $stmt->execute([':q' => $like]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allResults['users'] = $users;
    $totalCount += count($users);

    $stmt = $pdo->prepare("
        SELECT c.id_category,
               c.name,
               c.description,
               COUNT(a.id_article) AS nb_articles
        FROM categories c
        LEFT JOIN articles a ON a.id_category = c.id_category
        WHERE c.name        LIKE :q
           OR c.description LIKE :q
        GROUP BY c.id_category
        ORDER BY c.name ASC
        LIMIT 10
    ");
    $stmt->execute([':q' => $like]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allResults['categories'] = $categories;
    $totalCount += count($categories);

    echo json_encode([
        'results' => $allResults,
        'count'   => $totalCount,
        'query'   => $q
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur BDD : ' . $e->getMessage(), 'results' => [], 'count' => 0]);
}
