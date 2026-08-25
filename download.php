<?php
// BDMovieHub - Download Center
// Lists every title that has a download_url, grouped Movies / Series.
// Inspired by movielinkbd's dedicated download pages.

require_once __DIR__ . '/config.php';

$pageSection = 'browse';
$pageTitle   = 'Download Center';

$movies    = getPublishedMovies();
$animeList = getPublishedAnime();

$dlMovies = array();
foreach ($movies as $m) {
    if (!empty($m['download_url'])) { $dlMovies[] = $m; }
}
$dlAnime = array();
foreach ($animeList as $a) {
    // Anime downloads live on episodes; a series qualifies if any episode has one.
    $eps = getEpisodesByAnime(isset($a['id']) ? $a['id'] : '');
    foreach ($eps as $ep) {
        if (!empty($ep['download_url'])) { $dlAnime[] = $a; break; }
    }
}

usort($dlMovies, function ($x, $y) { return strcasecmp(isset($x['title']) ? $x['title'] : '', isset($y['title']) ? $y['title'] : ''); });
usort($dlAnime,  function ($x, $y) { return strcasecmp(isset($x['title']) ? $x['title'] : '', isset($y['title']) ? $y['title'] : ''); });

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title" style="margin-bottom: 12px;">Download Center</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            Offline viewing made easy. Every downloadable title in one place — open the title page and hit the Download button.
        </p>

        <!-- Movies with downloads -->
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-film" style="color:var(--primary); margin-right:6px;"></i> Movies</h2>
        </div>
        <?php if (empty($dlMovies)): ?>
            <div class="empty-state"><i class="fas fa-download"></i><p>No movie downloads available yet.</p></div>
        <?php else: ?>
            <div class="scroll-row">
                <?php foreach ($dlMovies as $m): ?>
                    <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                        <div class="card-poster">
                            <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                            <span class="card-badge dl-badge"><i class="fas fa-download"></i></span>
                            <?php if (!empty($m['quality'])): ?><span class="card-badge"><?php e($m['quality']); ?></span><?php endif; ?>
                            <div class="card-overlay"><button class="card-play-btn"><i class="fas fa-play"></i></button></div>
                        </div>
                        <div class="card-info">
                            <div class="card-title"><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></div>
                            <div class="card-meta">
                                <span><i class="far fa-calendar"></i> <?php e(isset($m['year']) ? $m['year'] : ''); ?></span>
                                <span><i class="far fa-clock"></i> <?php e(isset($m['duration']) ? $m['duration'] : ''); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Series with episode downloads -->
        <div class="section-header" style="margin-top:32px;">
            <h2 class="section-title"><i class="fas fa-tv" style="color:var(--anime-color); margin-right:6px;"></i> Series &amp; Anime</h2>
        </div>
        <?php if (empty($dlAnime)): ?>
            <div class="empty-state"><i class="fas fa-download"></i><p>No series downloads available yet.</p></div>
        <?php else: ?>
            <div class="scroll-row">
                <?php foreach ($dlAnime as $a): ?>
                    <a href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>" class="movie-card">
                        <div class="card-poster">
                            <img src="<?php echo htmlspecialchars(isset($a['poster']) ? $a['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($a['title']) ? $a['title'] : 'Series'); ?>" loading="lazy">
                            <span class="card-badge dl-badge"><i class="fas fa-download"></i></span>
                            <?php if (!empty($a['episode_count'])): ?><span class="card-badge rating">EP <?php e($a['episode_count']); ?></span><?php endif; ?>
                            <div class="card-overlay"><button class="card-play-btn"><i class="fas fa-play"></i></button></div>
                        </div>
                        <div class="card-info">
                            <div class="card-title"><?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?></div>
                            <div class="card-meta">
                                <span><i class="fas fa-layer-group"></i> <?php e(isset($a['status']) ? $a['status'] : ''); ?></span>
                                <span><i class="far fa-calendar"></i> <?php e(isset($a['aired']) ? $a['aired'] : ''); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p style="color:var(--muted); font-size:13px; margin-top:28px;">
            <i class="fas fa-info-circle" style="margin-right:4px;"></i>
            Downloads are provided by third-party hosts. BDMovieHub does not host any files.
        </p>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
