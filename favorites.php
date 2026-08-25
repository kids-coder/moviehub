<?php
// BDMovieHub - Favorites page (renders favorites stored in localStorage via JS)
require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle   = 'My Favorites';

$movies = getPublishedMovies();
$animeList = getPublishedAnime();

// Build maps for JS to lookup titles/posters
$movieMap = array();
foreach ($movies as $m) {
    $movieMap[$m['id']] = $m;
}
$animeMap = array();
foreach ($animeList as $a) {
    $animeMap[$a['id']] = $a;
}

// Output as JSON for the JS
$movieJson = json_encode(array_values($movieMap));
$animeJson = json_encode(array_values($animeMap));

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title" style="margin-bottom: 20px;">My Favorites</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            Movies and anime you have favorited. Stored locally in your browser (no login required).
        </p>

        <div id="favs-container">
            <div class="empty-state" id="favs-empty">
                <i class="fas fa-heart"></i>
                <h3>No Favorites Yet</h3>
                <p>Click the heart icon on any movie or anime to add it here.</p>
            </div>
            <div class="card-grid" id="favs-grid" style="display:none;"></div>
        </div>
    </div>
</section>

<script>
(function () {
    var FAV_KEY = 'moviehub-favs';
    var movies = <?php echo $movieJson; ?>;
    var anime = <?php echo $animeJson; ?>;

    function getFavs() {
        try {
            var raw = localStorage.getItem(FAV_KEY);
            return raw ? JSON.parse(raw) : { movies: [], anime: [] };
        } catch (e) { return { movies: [], anime: [] }; }
    }

    function escapeHtml(s) {
        if (s === undefined || s === null) { return ''; }
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c];
        });
    }

    function renderCard(item, type) {
        var url = type === 'anime'
            ? '<?php e(BASE_URL); ?>/anime-watch.php?slug=' + encodeURIComponent(item.slug || '')
            : '<?php e(BASE_URL); ?>/movie.php?slug=' + encodeURIComponent(item.slug || '');
        var accent = type === 'anime' ? 'var(--anime-color)' : 'var(--primary)';
        var icon = type === 'anime' ? 'tv' : 'film';
        var badge = '';
        if (item.quality && type === 'movie') {
            badge = '<span class="card-badge">' + escapeHtml(item.quality) + '</span>';
        }
        var rating = item.rating ? '<span class="card-badge rating"><i class="fas fa-star"></i> ' + escapeHtml(item.rating) + '</span>' : '';
        var year = item.year ? '<span><i class="far fa-calendar"></i> ' + escapeHtml(item.year) + '</span>' : '';
        var eps = (type === 'anime' && item.episode_count) ? '<span><i class="fas fa-list"></i> ' + escapeHtml(item.episode_count) + ' EPs</span>' : '';

        return '<a href="' + url + '" class="movie-card">' +
            '<div class="card-poster">' +
                '<img src="' + escapeHtml(item.poster || '') + '" alt="' + escapeHtml(item.title || 'Title') + '" loading="lazy">' +
                badge + rating +
                '<div class="card-overlay"><button class="card-play-btn"><i class="fas fa-play"></i></button></div>' +
            '</div>' +
            '<div class="card-info">' +
                '<div class="card-title">' + escapeHtml(item.title || 'Untitled') + '</div>' +
                '<div class="card-meta">' +
                    '<span style="color:' + accent + ';"><i class="fas fa-' + icon + '"></i> ' + (type === 'anime' ? 'Anime' : 'Movie') + '</span>' +
                    year + eps +
                '</div>' +
            '</div>' +
        '</a>';
    }

    var favs = getFavs();
    var cards = [];
    (favs.movies || []).forEach(function (id) {
        var found = null;
        for (var i = 0; i < movies.length; i++) { if (movies[i].id === id) { found = movies[i]; break; } }
        if (found) { cards.push(renderCard(found, 'movie')); }
    });
    (favs.anime || []).forEach(function (id) {
        var found = null;
        for (var i = 0; i < anime.length; i++) { if (anime[i].id === id) { found = anime[i]; break; } }
        if (found) { cards.push(renderCard(found, 'anime')); }
    });

    var grid = document.getElementById('favs-grid');
    var empty = document.getElementById('favs-empty');
    if (cards.length > 0) {
        grid.innerHTML = cards.join('');
        grid.style.display = 'grid';
        empty.style.display = 'none';
    } else {
        grid.style.display = 'none';
        empty.style.display = 'block';
    }
})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
