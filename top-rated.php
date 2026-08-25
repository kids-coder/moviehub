<?php
// BDMovieHub - Top Rated (movies + anime, sorted by rating)
require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle   = 'Top Rated';

$movies = getPublishedMovies();
$animeList = getPublishedAnime();

$combined = array();
foreach ($movies as $m) {
    if (!empty($m['rating'])) { $m['_type'] = 'movie'; $combined[] = $m; }
}
foreach ($animeList as $a) {
    if (!empty($a['rating'])) { $a['_type'] = 'anime'; $combined[] = $a; }
}

usort($combined, function ($a, $b) {
    $ra = floatval(isset($a['rating']) ? $a['rating'] : 0);
    $rb = floatval(isset($b['rating']) ? $b['rating'] : 0);
    if ($rb == $ra) { return 0; }
    return ($rb > $ra) ? 1 : -1;
});

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title" style="margin-bottom: 20px;">Top Rated</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            The highest-rated movies and anime in our catalog. Quality picks, ranked by community ratings.
        </p>

        <?php if (empty($combined)): ?>
            <div class="empty-state">
                <i class="fas fa-star"></i>
                <h3>No Rated Items Yet</h3>
                <p>Add ratings to movies or anime to populate this page.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($combined as $i => $item): ?>
                    <?php
                        $isMovie = ($item['_type'] === 'movie');
                        $url = $isMovie
                            ? BASE_URL . '/movie.php?slug=' . urlencode(isset($item['slug']) ? $item['slug'] : '')
                            : BASE_URL . '/anime-watch.php?slug=' . urlencode(isset($item['slug']) ? $item['slug'] : '');
                        $accent = $isMovie ? 'var(--primary)' : 'var(--anime-color)';
                    ?>
                    <a href="<?php e($url); ?>" class="movie-card" style="position:relative;">
                        <span style="position:absolute; top:8px; left:8px; background:#FFD700; color:#0a0a0f; padding:3px 9px; border-radius:5px; font-size:11px; font-weight:700; z-index:5;"><i class="fas fa-star"></i> <?php e($item['rating']); ?></span>
                        <div class="card-poster">
                            <img src="<?php echo htmlspecialchars(isset($item['poster']) ? $item['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($item['title']) ? $item['title'] : 'Title'); ?>" loading="lazy">
                            <?php if (!empty($item['quality']) && $isMovie): ?>
                                <span class="card-badge"><?php e($item['quality']); ?></span>
                            <?php endif; ?>
                            <div class="card-overlay">
                                <button class="card-play-btn"><i class="fas fa-play"></i></button>
                            </div>
                        </div>
                        <div class="card-info">
                            <div class="card-title"><?php e(isset($item['title']) ? $item['title'] : 'Untitled'); ?></div>
                            <div class="card-meta">
                                <span style="color:<?php e($accent); ?>;"><i class="fas fa-<?php echo $isMovie ? 'film' : 'tv'; ?>"></i> <?php echo $isMovie ? 'Movie' : 'Anime'; ?></span>
                                <?php if (!empty($item['year'])): ?>
                                    <span><i class="far fa-calendar"></i> <?php e($item['year']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
