<?php
// BDMovieHub - Anime-only Search Page

require_once __DIR__ . '/config.php';

$pageSection = 'anime';
$isAnimePage = true;
$pageTitle = 'Anime Search';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$animeRes = array();
if ($q !== '') {
    $ql = strtolower($q);
    foreach (getPublishedAnime() as $a) {
        $title = strtolower(isset($a['title']) ? $a['title'] : '');
        if (strpos($title, $ql) !== false) { $animeRes[] = $a; }
    }
}

$pag = paginate($animeRes, $page, 24);

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title anime-accent" style="margin-bottom: 20px;">Search Anime</h1>
        <form method="get" action="<?php e(BASE_URL); ?>/anime-search.php" style="margin-bottom: 24px;">
            <div style="display:flex; gap:8px; max-width:600px;">
                <input type="text" name="q" value="<?php e($q); ?>" placeholder="Search anime by title..."
                       style="flex:1; padding:12px 16px; background:var(--card); color:var(--text); border:1px solid var(--border); border-radius:var(--radius); outline:none;">
                <button type="submit" class="btn btn-anime"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>

        <?php if ($q !== ''): ?>
            <?php if (empty($pag['items'])): ?>
                <div class="empty-state">
                    <i class="fas fa-tv"></i>
                    <h3>No Anime Found</h3>
                    <p>Try a different search term.</p>
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
                            </div>
                            <div class="card-info">
                                <div class="card-title"><?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?></div>
                                <div class="card-meta">
                                    <span><i class="fas fa-list"></i> <?php e(isset($a['episode_count']) ? $a['episode_count'] : 0); ?> EPs</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php if ($pag['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $pag['total_pages']; $i++): ?>
                        <?php if ($i == $pag['page']): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php e(BASE_URL); ?>/anime-search.php?q=<?php echo urlencode($q); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Search Anime</h3>
                <p>Enter an anime title to find it.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
