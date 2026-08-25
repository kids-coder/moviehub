<?php
// BDMovieHub - Anime Listing Page (with genre filter & pagination)

require_once __DIR__ . '/config.php';

$pageSection = 'anime';
$isAnimePage = true;
$pageTitle = 'Anime';

$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$animeList = getPublishedAnime();

if ($genre !== '') {
    $filtered = array();
    foreach ($animeList as $a) {
        $g = isset($a['genre']) ? $a['genre'] : array();
        if (is_array($g) && in_array($genre, $g)) { $filtered[] = $a; }
    }
    $animeList = $filtered;
}

// Sort by created_at desc (only if non-empty)
if (!empty($animeList)) {
    usort($animeList, function ($a, $b) {
        $ta = isset($a['created_at']) ? $a['created_at'] : '';
        $tb = isset($b['created_at']) ? $b['created_at'] : '';
        return strcmp($tb, $ta);
    });
}

$pag = paginate($animeList, $page, 24);
$genres = getAllGenres();

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title anime-accent" style="margin-bottom: 20px;">Anime Library</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            Browse our complete collection of anime series. Filter by genre to find your next favorite show.
        </p>

        <!-- Genre Filter -->
        <div class="genre-filter">
            <a href="<?php e(BASE_URL); ?>/anime.php" class="genre-pill <?php echo $genre === '' ? 'active' : ''; ?>">All</a>
            <?php if (!empty($genres)): ?>
                <?php foreach ($genres as $g): ?>
                    <a href="<?php e(BASE_URL); ?>/anime.php?genre=<?php echo urlencode($g); ?>" class="genre-pill <?php echo $genre === $g ? 'active' : ''; ?>">
                        <?php e($g); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (empty($pag['items'])): ?>
            <div class="empty-state">
                <i class="fas fa-tv"></i>
                <h3>No Anime Found</h3>
                <p><?php echo $genre ? 'No anime in this genre yet.' : 'No anime available. Add some from the admin panel.'; ?></p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($pag['items'] as $a): ?>
                    <a href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>" class="movie-card">
                        <div class="card-poster">
                            <img src="<?php echo htmlspecialchars(isset($a['poster']) ? $a['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($a['title']) ? $a['title'] : 'Anime'); ?>" loading="lazy">
                            <?php
                                $st = isset($a['status']) ? $a['status'] : 'ongoing';
                                $stClass = $st === 'completed' ? 'completed' : '';
                            ?>
                            <span class="card-badge status <?php e($stClass); ?>"><?php e(ucfirst($st)); ?></span>
                            <?php if (!empty($a['rating'])): ?>
                                <span class="card-badge rating"><i class="fas fa-star"></i> <?php e($a['rating']); ?></span>
                            <?php endif; ?>
                            <div class="card-overlay">
                                <button class="card-play-btn"><i class="fas fa-play"></i></button>
                            </div>
                        </div>
                        <div class="card-info">
                            <div class="card-title"><?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?></div>
                            <div class="card-meta">
                                <span><i class="fas fa-list"></i> <?php e(isset($a['episode_count']) ? $a['episode_count'] : 0); ?> EPs</span>
                                <?php if (!empty($a['aired'])): ?>
                                    <span><i class="far fa-calendar"></i> <?php e($a['aired']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($pag['total_pages'] > 1): ?>
            <div class="pagination">
                <?php if ($pag['page'] > 1): ?>
                    <a href="<?php e(BASE_URL); ?>/anime.php?genre=<?php echo urlencode($genre); ?>&page=<?php echo $pag['page'] - 1; ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $pag['total_pages']; $i++): ?>
                    <?php if ($i == $pag['page']): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php e(BASE_URL); ?>/anime.php?genre=<?php echo urlencode($genre); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($pag['page'] < $pag['total_pages']): ?>
                    <a href="<?php e(BASE_URL); ?>/anime.php?genre=<?php echo urlencode($genre); ?>&page=<?php echo $pag['page'] + 1; ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
