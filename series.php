<?php
// BDMovieHub - Series browse page
// Lists movies flagged as kind=series. A specific /series/{slug} request is
// redirected to the canonical movie detail page so metadata lives in one place.
require_once __DIR__ . '/config.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if ($slug !== '') {
    header('Location: ' . (BASE_URL === '' ? '' : BASE_URL) . '/movie.php?slug=' . urlencode($slug), true, 301);
    exit;
}

$pageSection = 'movies';
$pageTitle   = 'Series';
$ogTitle     = SITE_NAME . ' - Series';

$all = getPublishedMovies();
$series = array();
foreach ($all as $m) {
    if ((isset($m['kind']) ? $m['kind'] : 'movie') === 'series') { $series[] = $m; }
}
usort($series, function ($a, $b) {
    $ta = isset($a['created_at']) ? $a['created_at'] : '';
    $tb = isset($b['created_at']) ? $b['created_at'] : '';
    return strcmp($tb, $ta);
});

include __DIR__ . '/header.php';
?>
<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title" style="margin-bottom:20px;">Series</h1>
        <p style="color:var(--muted); margin-bottom:24px;">Binge-worthy shows and multi-season titles.</p>
        <?php if (empty($series)): ?>
            <div class="empty-state">
                <i class="fas fa-tv"></i>
                <h3>No Series Yet</h3>
                <p>Series will appear here once added. Explore <a href="<?php e(BASE_URL); ?>/trending.php">Trending Now</a>.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($series as $s): ?>
                    <a href="<?php e(BASE_URL); ?>/series/<?php echo urlencode(isset($s['slug']) ? $s['slug'] : ''); ?>" class="movie-card">
                        <div class="card-poster">
                            <img src="<?php echo htmlspecialchars(isset($s['poster']) ? $s['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($s['title']) ? $s['title'] : 'Series'); ?>" loading="lazy">
                            <?php if (!empty($s['quality'])): ?><span class="card-badge"><?php e($s['quality']); ?></span><?php endif; ?>
                        </div>
                        <div class="card-info">
                            <div class="card-title"><?php e(isset($s['title']) ? $s['title'] : 'Untitled'); ?></div>
                            <div class="card-meta"><span><i class="far fa-calendar"></i> <?php e(isset($s['year']) ? $s['year'] : ''); ?></span></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
