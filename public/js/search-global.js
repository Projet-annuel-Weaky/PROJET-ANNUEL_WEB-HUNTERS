// Recherche globale dynamique
const searchInput = document.getElementById('searchInput');
const searchIcon = document.querySelector('.search-icon');
const searchBar = document.querySelector('.search-bar');
let globalSearchTimeout;

if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        
        clearTimeout(globalSearchTimeout);
        
        if (query.length < 2) {
            hideSearchResults();
            return;
        }
        
        globalSearchTimeout = setTimeout(() => {
            performGlobalSearch(query);
        }, 300);
    });
    
    // Fermer le dropdown quand on clique ailleurs
    document.addEventListener('click', (e) => {
        if (!searchBar.contains(e.target)) {
            hideSearchResults();
        }
    });
}

// Bouton de recherche redirige vers la page de résultats
if (searchIcon) {
    searchIcon.addEventListener('click', (e) => {
        e.preventDefault();
        const query = searchInput?.value.trim() || '';
        if (query.length >= 2) {
            window.location.href = `/search_results.php?q=${encodeURIComponent(query)}`;
        }
    });
}

// Appui sur Entrée pour soumettre la recherche
if (searchInput) {
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = e.target.value.trim();
            if (query.length >= 2) {
                window.location.href = `/search_results.php?q=${encodeURIComponent(query)}`;
            }
        }
    });
}

async function performGlobalSearch(query) {
    try {
        const response = await fetch('/search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ q: query })
        });
        
        if (!response.ok) throw new Error('Erreur réseau');
        
        const data = await response.json();
        
        if (data.error) {
            console.error('Erreur:', data.error);
            return;
        }
        
        if (data.count === 0) {
            showNoResults(query);
            return;
        }
        
        displaySearchResults(data);
    } catch (error) {
        console.error('Erreur recherche:', error);
    }
}

function displaySearchResults(data) {
    const results = data.results;
    let html = '<div class="search-results-dropdown">';
    
    // Articles
    if (results.articles.length > 0) {
        html += '<div class="search-category"><span class="category-title">Articles</span>';
        results.articles.forEach(article => {
            html += `
                <a href="/article.php?id=${article.id_article}" class="search-result-item">
                    <div class="result-title">${escapeHtml(article.title)}</div>
                    <div class="result-meta">${escapeHtml(article.auteur)} • ${escapeHtml(article.categorie)}</div>
                    <div class="result-excerpt">${escapeHtml(article.excerpt)}</div>
                </a>
            `;
        });
        html += '</div>';
    }
    
    // Utilisateurs
    if (results.users.length > 0) {
        html += '<div class="search-category"><span class="category-title">Utilisateurs</span>';
        results.users.forEach(user => {
            html += `
                <a href="/user.php?id=${user.id_user}" class="search-result-item">
                    <div class="result-title">${escapeHtml(user.username)}</div>
                    <div class="result-meta">${escapeHtml(user.role)}</div>
                    ${user.bio ? `<div class="result-excerpt">${escapeHtml(user.bio)}</div>` : ''}
                </a>
            `;
        });
        html += '</div>';
    }
    
    // Catégories
    if (results.categories.length > 0) {
        html += '<div class="search-category"><span class="category-title">Catégories</span>';
        results.categories.forEach(cat => {
            html += `
                <a href="/category.php?id=${cat.id_category}" class="search-result-item">
                    <div class="result-title">${escapeHtml(cat.name)}</div>
                    <div class="result-meta">${cat.nb_articles} article(s)</div>
                </a>
            `;
        });
        html += '</div>';
    }
    
    html += '</div>';
    
    const existingDropdown = document.querySelector('.search-results-dropdown');
    if (existingDropdown) existingDropdown.remove();
    
    searchBar.insertAdjacentHTML('beforeend', html);
}

function showNoResults(query) {
    let html = `
        <div class="search-results-dropdown">
            <div class="search-no-results">
                Aucun résultat pour "<strong>${escapeHtml(query)}</strong>"
            </div>
        </div>
    `;
    
    const existingDropdown = document.querySelector('.search-results-dropdown');
    if (existingDropdown) existingDropdown.remove();
    
    searchBar.insertAdjacentHTML('beforeend', html);
}

function hideSearchResults() {
    const dropdown = document.querySelector('.search-results-dropdown');
    if (dropdown) dropdown.remove();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
