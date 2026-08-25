<?php
// BDMovieHub - Combined Search (Movies + Anime)

require_once __DIR__ . '/config.php';

$pageSection = 'search';
$pageTitle = 'Search';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'movies';

$results = array();
if ($q !== '') {
    $ql = strtolower($q);
    foreach (getPublishedMovies() as $m) {
        $title = strtolower(isset($m['title']) ? $m['title'] : '');
        if (strpos($title, $ql) !== false) {
            $m['_type'] = 'movie';
            $results['movies'][] = $m;
        }
    }
    foreach (getPublishedAnime() as $a) {
        $title = strtolower(isset($a['title']) ? $a['title'] : '');
        if (strpos($title, $ql) !== false) {
            $a['_type'] = 'anime';
            $results['anime'][] = $a;
        }
    }
}

$movies = isset($results['movies']) ? $results['movies'] : array();
$animeRes = isset($results['anime']) ? $results['anime'] : array();

$moviePag = paginate($movies, $page, 20);
$animePag = paginate($animeRes, $page, 20);

include __DIR__ . '/header.php';
?>

<section class="search-page">
    <div class="container">
        <h1 class="section-title" style="margin-bottom: 20px;">Search</h1>
        <form method="get" action="<?php e(BASE_URL); ?>/search.php" style="margin-bottom: 24px;">
            <div style="display:flex; gap:8px; max-width:600px;">
                <input type="text" name="q" value="<?php e($q); ?>" placeholder="Search movies and anime..."
                       style="flex:1; padding:12px 16px; background:var(--card); color:var(--text); border:1px solid var(--border); border-radius:var(--radius); outline:none;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>

        <?php if ($q !== ''): ?>
            <div class="search-tabs">
                <a href="<?php e(BASE_URL); ?>/search.php?q=<?php echo urlencode($q); ?>&tab=movies" class="search-tab <?php echo $tab === 'movies' ? 'active' : ''; ?>">
                    Movies (<?php echo count($movies); ?>)
                </a>
                <a href="<?php e(BASE_URL); ?>/search.php?q=<?php echo urlencode($q); ?>&tab=anime" class="search-tab <?php echo $tab === 'anime' ? 'active' : ''; ?>">
                    Anime (<?php echo count($animeRes); ?>)
                </a>
            </div>

            <?php if ($tab === 'movies'): ?>
                <?php if (empty($moviePag['items'])): ?>
                    <div class="empty-state">
                        <i class="fas fa-film"></i>
                        <h3>No Movies Found</h3>
                        <p>Try a different search term.</p>
                    </div>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($moviePag['items'] as $m): ?>
                            <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                                <div class="card-poster">
                                    <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                                    <?php if (!empty($m['quality'])): ?>
                                        <span class="card-badge"><?php e($m['quality']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($m['rating'])): ?>
                                        <span class="card-badge rating"><i class="fas fa-star"></i> <?php e($m['rating']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-info">
                                    <div class="card-title"><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></div>
                                    <div class="card-meta">
                                        <span><i class="far fa-calendar"></i> <?php e(isset($m['year']) ? $m['year'] : ''); ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($moviePag['total_pages'] > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $moviePag['total_pages']; $i++): ?>
                            <?php if ($i == $moviePag['page']): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php e(BASE_URL); ?>/search.php?q=<?php echo urlencode($q); ?>&tab=movies&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php if (empty($animePag['items'])): ?>
                    <div class="empty-state">
                        <i class="fas fa-tv"></i>
                        <h3>No Anime Found</h3>
                        <p>Try a different search term.</p>
                    </div>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($animePag['items'] as $a): ?>
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
                    <?php if ($animePag['total_pages'] > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $animePag['total_pages']; $i++): ?>
                            <?php if ($i == $animePag['page']): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php e(BASE_URL); ?>/search.php?q=<?php echo urlencode($q); ?>&tab=anime&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Start Searching</h3>
                <p>Enter a movie or anime title in the search box above.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
