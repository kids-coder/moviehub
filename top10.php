<?php
// BDMovieHub - Top 10 Charts (movies + anime side by side)
// Inspired by mlsbd's "Top 10" ranking rows.

require_once __DIR__ . '/config.php';

$pageSection = 'browse';
$pageTitle   = 'Top 10 Charts';

$movies = getPublishedMovies();
$anime  = getPublishedAnime();

usort($movies, function ($x, $y) {
    $rx = isset($x['rating']) ? (float)$x['rating'] : 0;
    $ry = isset($y['rating']) ? (float)$y['rating'] : 0;
    if ($ry === $rx) {
        $vx = isset($x['views']) ? (int)$x['views'] : 0;
        $vy = isset($y['views']) ? (int)$y['views'] : 0;
        return $vy - $vx;
    }
    return $ry > $rx ? 1 : -1;
});
usort($anime, function ($x, $y) {
    $rx = isset($x['rating']) ? (float)$x['rating'] : 0;
    $ry = isset($y['rating']) ? (float)$y['rating'] : 0;
    if ($ry === $rx) {
        $vx = isset($x['views']) ? (int)$x['views'] : 0;
        $vy = isset($y['views']) ? (int)$y['views'] : 0;
        return $vy - $vx;
    }
    return $ry > $rx ? 1 : -1;
});

$topMovies = array_slice($movies, 0, 10);
$topAnime  = array_slice($anime, 0, 10);

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title" style="margin-bottom: 12px;">Top 10 Charts</h1>
        <p style="color: var(--muted); margin-bottom: 28px;">
            The ten highest-rated titles in each category, ranked by rating and total views.
        </p>

        <div class="top10-grid">
            <!-- Movies chart -->
            <div class="top10-col">
                <h2 class="section-title"><i class="fas fa-trophy" style="color:#f1c40f; margin-right:6px;"></i> Top 10 Movies</h2>
                <?php if (empty($topMovies)): ?>
                    <div class="empty-state"><i class="fas fa-chart-bar"></i><p>No rated movies yet.</p></div>
                <?php else: ?>
                    <ol class="top10-list">
                        <?php foreach ($topMovies as $i => $m): ?>
                            <li class="top10-item">
                                <span class="top10-rank<?php echo $i === 0 ? ' gold' : ($i === 1 ? ' silver' : ($i === 2 ? ' bronze' : '')); ?>"><?php echo $i + 1; ?></span>
                                <?php if (!empty($m['poster'])): ?>
                                    <a class="top10-poster" href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>">
                                        <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                                    </a>
                                <?php endif; ?>
                                <div class="top10-info">
                                    <a class="top10-title" href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>"><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></a>
                                    <div class="top10-meta">
                                        <?php if (!empty($m['rating'])): ?><span><i class="fas fa-star"></i> <?php e($m['rating']); ?></span><?php endif; ?>
                                        <?php if (!empty($m['year'])): ?><span><i class="far fa-calendar"></i> <?php e($m['year']); ?></span><?php endif; ?>
                                        <?php if (!empty($m['views'])): ?><span><i class="fas fa-eye"></i> <?php echo number_format((int)$m['views']); ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>

            <!-- Anime chart -->
            <div class="top10-col">
                <h2 class="section-title anime-accent"><i class="fas fa-trophy" style="color:#f1c40f; margin-right:6px;"></i> Top 10 Series</h2>
                <?php if (empty($topAnime)): ?>
                    <div class="empty-state"><i class="fas fa-chart-bar"></i><p>No rated series yet.</p></div>
                <?php else: ?>
                    <ol class="top10-list">
                        <?php foreach ($topAnime as $i => $a): ?>
                            <li class="top10-item">
                                <span class="top10-rank<?php echo $i === 0 ? ' gold' : ($i === 1 ? ' silver' : ($i === 2 ? ' bronze' : '')); ?>"><?php echo $i + 1; ?></span>
                                <?php if (!empty($a['poster'])): ?>
                                    <a class="top10-poster" href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>">
                                        <img src="<?php echo htmlspecialchars(isset($a['poster']) ? $a['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($a['title']) ? $a['title'] : 'Series'); ?>" loading="lazy">
                                    </a>
                                <?php endif; ?>
                                <div class="top10-info">
                                    <a class="top10-title" href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>"><?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?></a>
                                    <div class="top10-meta">
                                        <?php if (!empty($a['rating'])): ?><span><i class="fas fa-star"></i> <?php e($a['rating']); ?></span><?php endif; ?>
                                        <?php if (!empty($a['aired'])): ?><span><i class="far fa-calendar"></i> <?php e($a['aired']); ?></span><?php endif; ?>
                                        <?php if (!empty($a['views'])): ?><span><i class="fas fa-eye"></i> <?php echo number_format((int)$a['views']); ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
