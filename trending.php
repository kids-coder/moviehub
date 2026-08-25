<?php
// BDMovieHub - Trending (most viewed / highest rated)
require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle   = 'Trending Now';

$movies = getPublishedMovies();
$animeList = getPublishedAnime();

// Combine and sort by rating (desc), tie-break by views if set
$combined = array();
foreach ($movies as $m) {
    $m['_type'] = 'movie';
    $m['_score'] = floatval(isset($m['rating']) ? $m['rating'] : 0) + floatval(isset($m['views']) ? $m['views'] : 0) * 0.1;
    $combined[] = $m;
}
foreach ($animeList as $a) {
    $a['_type'] = 'anime';
    $a['_score'] = floatval(isset($a['rating']) ? $a['rating'] : 0) + floatval(isset($a['views']) ? $a['views'] : 0) * 0.1;
    $combined[] = $a;
}

usort($combined, function ($a, $b) {
    $sa = isset($a['_score']) ? $a['_score'] : 0;
    $sb = isset($b['_score']) ? $b['_score'] : 0;
    if ($sb == $sa) { return 0; }
    return ($sb > $sa) ? 1 : -1;
});

$trending = array_slice($combined, 0, 30);

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title" style="margin-bottom: 20px;">Trending Now</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            The most popular movies and anime on the platform, ranked by rating and views.
        </p>

        <?php if (empty($trending)): ?>
            <div class="empty-state">
                <i class="fas fa-fire"></i>
                <h3>No Trending Items</h3>
                <p>Add some movies or anime to see trending content.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($trending as $i => $item): ?>
                    <?php
                        $isMovie = ($item['_type'] === 'movie');
                        $url = $isMovie
                            ? BASE_URL . '/movie.php?slug=' . urlencode(isset($item['slug']) ? $item['slug'] : '')
                            : BASE_URL . '/anime-watch.php?slug=' . urlencode(isset($item['slug']) ? $item['slug'] : '');
                        $accent = $isMovie ? 'var(--primary)' : 'var(--anime-color)';
                    ?>
                    <a href="<?php e($url); ?>" class="movie-card" style="position:relative;">
                        <span style="position:absolute; top:8px; left:8px; background:<?php e($accent); ?>; color:#fff; padding:3px 9px; border-radius:5px; font-size:11px; font-weight:700; z-index:5;">#<?php echo $i + 1; ?></span>
                        <div class="card-poster">
                            <img src="<?php echo htmlspecialchars(isset($item['poster']) ? $item['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($item['title']) ? $item['title'] : 'Title'); ?>" loading="lazy">
                            <?php if (!empty($item['quality']) && $isMovie): ?>
                                <span class="card-badge"><?php e($item['quality']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($item['rating'])): ?>
                                <span class="card-badge rating"><i class="fas fa-star"></i> <?php e($item['rating']); ?></span>
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
