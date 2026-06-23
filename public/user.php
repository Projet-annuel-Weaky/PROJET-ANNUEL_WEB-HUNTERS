<?php
define("ROOT", dirname(__DIR__));
define("CONFIG", ROOT . "/configs");
define("SRC", ROOT . "/src");

require_once CONFIG . "/config.php";
require_once SRC . "/services/LogService.php";

LogService::visit('user.php');

include_once SRC . "/views/layouts/header.php";
?>
    
<main>
    <section>
        <article>
            <h1>UTILISATEURS</h1>
        </article>
        <div class = "searchandresultsContainer">
            <form id="search-form">
                <input type="text" id="search-input" placeholder="Rechercher un utilisateur…" minlength="2" />
                <button type="submit">Rechercher</button>
            </form>
        <div id="search-results"></div>
        </div>
    </section>

<?php
$sql = "SELECT id_user, username, email FROM users ORDER BY id_user ASC";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Liste des utilisateurs</h2>

<div class="container">

<?php foreach ($users as $user): ?>
        <div class="card">
            <h3>#<?= htmlspecialchars($user['id_user'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></h3>
            <?php if (($_SESSION['role_id'] ?? 0) == 2): ?>
                <p>Email : <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
<?php endforeach; ?>
</div>

</main>

<script>
document.getElementById('search-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const q = document.getElementById('search-input').value.trim();
    const zone = document.getElementById('search-results');

    if (q.length < 2) 
    { 
        zone.innerHTML = '<p>Saisissez au moins 2 caractères.</p>';
        return;
    }

    zone.innerHTML = '<p>Recherche en cours…</p>';
    
    try
    {
        const response = await fetch('/search_users.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ q })
    });

    if (!response.ok) throw new Error('Erreur serveur : ' + response.status);

    const data = await response.json();

    if (data.count === 0) 
    {
        zone.innerHTML = `<p>Aucun utilisateur trouvé pour "${escHtml(q)}".</p>`;
        return;
    }

    let html = `<p>${data.count} utilisateur trouvé pour "${escHtml(q)}"</p><ul>`;
    data.results.forEach(user => {
        html += `
        <li>
        <a href="/users/${user.id_user}">
        <strong>${escHtml(user.username)}</strong>
        <span>(${escHtml(user.role)})</span>
        ${user.bio ? `<p>${escHtml(user.bio)}</p>` : ''}
        </a>
        </li>`;
    });
    html += '</ul>';
    zone.innerHTML = html;

    } catch (err) {
    zone.innerHTML = `<p style="color:red;">Erreur : ${err.message}</p>`;
    }
});

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}
</script>

<?php
include_once SRC . '/views/layouts/footer.php';
?>
