<?php
// BDMovieHub - Homepage
// Hero slider, featured movies, recent movies, trending, top rated, latest anime

require_once __DIR__ . '/config.php';

$pageSection = 'home';
$pageTitle   = SITE_NAME . ' - Bangla Movies, Series & Anime';
$ogTitle     = SITE_NAME . ' - Bangla Movies, Series & Anime';
$ogDescription = SITE_DESC;

// All data calls are wrapped to be fail-safe
$slides    = getData(FILE_SLIDES);
$featured  = getFeaturedMovies(10);
$movies    = getPublishedMovies();
$animeList = getPublishedAnime();

// Sort slides by order (if set), newest first as fallback
if (!empty($slides)) {
    usort($slides, function ($a, $b) {
        $oa = isset($a['order']) ? intval($a['order']) : 999;
        $ob = isset($b['order']) ? intval($b['order']) : 999;
        return $oa - $ob;
    });
}

// Sort movies by created_at desc
if (!empty($movies)) {
    usort($movies, function ($a, $b) {
        $ta = isset($a['created_at']) ? $a['created_at'] : '';
        $tb = isset($b['created_at']) ? $b['created_at'] : '';
        return strcmp($tb, $ta);
    });
}
$recentMovies = array_slice($movies, 0, 12);

// Trending: sort by views desc, then rating
$trending = $movies;
usort($trending, function ($a, $b) {
    $va = isset($a['views']) ? intval($a['views']) : 0;
    $vb = isset($b['views']) ? intval($b['views']) : 0;
    if ($vb == $va) {
        $ra = isset($a['rating']) ? floatval($a['rating']) : 0;
        $rb = isset($b['rating']) ? floatval($b['rating']) : 0;
        return ($rb > $ra) ? 1 : -1;
    }
    return ($vb > $va) ? 1 : -1;
});
$trendingMovies = array_slice($trending, 0, 10);

// Top rated movies
$topRated = $movies;
usort($topRated, function ($a, $b) {
    $ra = isset($a['rating']) ? floatval($a['rating']) : 0;
    $rb = isset($b['rating']) ? floatval($b['rating']) : 0;
    return ($rb > $ra) ? 1 : -1;
});
$topRatedMovies = array_slice($topRated, 0, 10);

// Sort anime by created_at desc
if (!empty($animeList)) {
    usort($animeList, function ($a, $b) {
        $ta = isset($a['created_at']) ? $a['created_at'] : '';
        $tb = isset($b['created_at']) ? $b['created_at'] : '';
        return strcmp($tb, $ta);
    });
}
$latestAnime = array_slice($animeList, 0, 10);

// Popular in Bangladesh: Bangla-language or BD-region titles
$popularBD = array();
foreach ($movies as $m) {
    $lang = strtolower(isset($m['language']) ? $m['language'] : '');
    $country = strtolower(isset($m['country']) ? $m['country'] : '');
    if (strpos($lang, 'bangla') !== false || strpos($lang, 'bengali') !== false || strpos($country, 'bangladesh') !== false) {
        $popularBD[] = $m;
    }
}
usort($popularBD, function ($a, $b) {
    return intval(isset($b['views']) ? $b['views'] : 0) <=> intval(isset($a['views']) ? $a['views'] : 0);
});
$popularBD = array_slice($popularBD, 0, 10);

// Recently Added: newest by created_at (same as recent, but capped for the row)
$recentlyAdded = array_slice($movies, 0, 10);

// Coming Soon: unreleased / upcoming titles (year in the future or flagged upcoming)
$comingSoon = array();
$__nowYear = (int)date('Y');
foreach ($movies as $m) {
    $__y = isset($m['year']) ? intval($m['year']) : 0;
    $__st = strtolower(isset($m['status']) ? $m['status'] : '');
    if ($__y > $__nowYear || $__st === 'upcoming' || $__st === 'coming_soon') { $comingSoon[] = $m; }
}
$comingSoon = array_slice($comingSoon, 0, 10);

include __DIR__ . '/header.php';
outputWebsiteJsonLd();

// Metadata map for the localStorage-based My Watchlist preview
$favMeta = array();
foreach ($movies as $m) {
    $favMeta[isset($m['id']) ? $m['id'] : ''] = array(
        'title' => isset($m['title']) ? $m['title'] : '',
        'poster' => isset($m['poster']) ? $m['poster'] : '',
        'url' => BASE_URL . '/movie.php?slug=' . urlencode(isset($m['slug']) ? $m['slug'] : ''),
        'meta' => implode(', ', array_slice((isset($m['genre']) && is_array($m['genre']) ? $m['genre'] : array()), 0, 2)),
    );
}
foreach ($animeList as $a) {
    $favMeta[isset($a['id']) ? $a['id'] : ''] = array(
        'title' => isset($a['title']) ? $a['title'] : '',
        'poster' => isset($a['poster']) ? $a['poster'] : '',
        'url' => BASE_URL . '/anime-watch.php?slug=' . urlencode(isset($a['slug']) ? $a['slug'] : ''),
        'meta' => (isset($a['episode_count']) ? $a['episode_count'] : '') . ' EPs',
    );
}
?>
<script>window.BDMH_FAV_META = <?php echo json_encode($favMeta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;</script>

<!-- Hero Slider -->
<section class="hero-slider" id="hero">
    <?php if (empty($slides)): ?>
        <div class="hero-slide active" style="background-image: linear-gradient(135deg, #469AFF 0%, #9b59b6 100%);">
            <div class="hero-content">
                <h1 class="hero-title">Welcome to BDMovieHub</h1>
                <p style="font-size:18px;color:#fff;margin:12px 0 24px;opacity:0.9;">Discover Bangla movies, series and anime</p>
                <a href="<?php e(BASE_URL); ?>/search.php" class="hero-cta"><i class="fas fa-play"></i> Browse Movies</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($slides as $i => $slide): ?>
            <?php
                $slideImage = isset($slide['image']) ? $slide['image'] : '';
                $slideTitle = isset($slide['title']) ? $slide['title'] : '';
                $slideUrl   = isset($slide['url']) ? $slide['url'] : '#';
            ?>
            <div class="hero-slide <?php echo $i === 0 ? 'active' : ''; ?>"
                 style="background-image: url('<?php echo htmlspecialchars($slideImage, ENT_QUOTES, 'UTF-8'); ?>');">
                <div class="hero-content">
                    <h1 class="hero-title"><?php e($slideTitle); ?></h1>
                    <a href="<?php echo htmlspecialchars($slideUrl, ENT_QUOTES, 'UTF-8'); ?>" class="hero-cta">
                        <i class="fas fa-play"></i> Watch Now
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="hero-dots">
            <?php foreach ($slides as $i => $s): ?>
                <span class="<?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>"></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Quick category pills -->
<section class="section" style="padding: 24px 0;">
    <div class="container">
        <div class="quick-nav">
            <a href="<?php e(BASE_URL); ?>/trending.php" class="quick-nav-pill"><i class="fas fa-fire"></i> Trending</a>
            <a href="<?php e(BASE_URL); ?>/top-rated.php" class="quick-nav-pill"><i class="fas fa-star"></i> Top Rated</a>
            <a href="<?php e(BASE_URL); ?>/genres.php" class="quick-nav-pill"><i class="fas fa-th"></i> Genres</a>
            <a href="<?php e(BASE_URL); ?>/search.php" class="quick-nav-pill"><i class="fas fa-film"></i> All Movies</a>
            <a href="<?php e(BASE_URL); ?>/anime.php" class="quick-nav-pill anime-accent"><i class="fas fa-tv"></i> All Anime</a>
            <a href="<?php e(BASE_URL); ?>/anime-schedule.php" class="quick-nav-pill anime-accent"><i class="fas fa-calendar-alt"></i> Schedule</a>
            <a href="<?php e(BASE_URL); ?>/favorites.php" class="quick-nav-pill"><i class="fas fa-heart"></i> My Favorites</a>
        </div>
    </div>
</section>

<!-- Continue Watching (from localStorage - only shown if user has watch history) -->
<section class="section" id="continue-watching-section" style="display:none; padding: 24px 0;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-history" style="color:var(--primary); margin-right:6px;"></i> <?php echo t('Continue Watching'); ?></h2>
            <button class="section-link" id="clear-history-btn" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:13px;"><i class="fas fa-trash"></i> <?php e('Clear'); ?></button>
        </div>
        <div class="scroll-row" id="recently-watched">
            <!-- Populated by features.js renderHistory() -->
        </div>
    </div>
</section>

<!-- My Watchlist (from localStorage favorites - only shown if user has favorites) -->
<section class="section" id="watchlist-section" style="display:none; padding: 24px 0;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-heart" style="color:#e74c3c; margin-right:6px;"></i> <?php echo t('My Favorites'); ?></h2>
            <a href="<?php e(BASE_URL); ?>/favorites.php" class="section-link"><?php echo t('View All'); ?> <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="scroll-row" id="watchlist-row">
            <!-- Populated by features.js renderWatchlist() -->
        </div>
    </div>
</section>

<!-- Featured Movies -->
<?php if (!empty($featured)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo t('Featured Movies'); ?></h2>
            <a href="<?php e(BASE_URL); ?>/search.php" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="scroll-row">
            <?php foreach ($featured as $m): ?>
                <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                    <div class="card-poster">
                        <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                        <?php if (!empty($m['quality'])): ?>
                            <span class="card-badge"><?php e($m['quality']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($m['rating'])): ?>
                            <span class="card-badge rating"><i class="fas fa-star"></i> <?php e($m['rating']); ?></span>
                        <?php endif; ?>
                        <div class="card-overlay">
                            <button class="card-play-btn"><i class="fas fa-play"></i></button>
                        </div>
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
    </div>
</section>
<?php endif; ?>

<!-- Trending Movies -->
<?php if (!empty($trendingMovies)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-fire" style="color:var(--accent); margin-right:6px;"></i> <?php echo t('Trending Now'); ?></h2>
            <a href="<?php e(BASE_URL); ?>/trending.php" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="scroll-row">
            <?php foreach ($trendingMovies as $m): ?>
                <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                    <div class="card-poster">
                        <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                        <?php if (!empty($m['quality'])): ?>
                            <span class="card-badge"><?php e($m['quality']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($m['rating'])): ?>
                            <span class="card-badge rating"><i class="fas fa-star"></i> <?php e($m['rating']); ?></span>
                        <?php endif; ?>
                        <div class="card-overlay">
                            <button class="card-play-btn"><i class="fas fa-play"></i></button>
                        </div>
                    </div>
                    <div class="card-info">
                        <div class="card-title"><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></div>
                        <div class="card-meta">
                            <span><i class="far fa-calendar"></i> <?php e(isset($m['year']) ? $m['year'] : ''); ?></span>
                            <?php if (!empty($m['views'])): ?>
                                <span><i class="fas fa-eye"></i> <?php echo number_format($m['views']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Recent Movies -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><?php echo t('Recent Movies'); ?></h2>
            <a href="<?php e(BASE_URL); ?>/search.php" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php if (empty($recentMovies)): ?>
            <div class="empty-state">
                <i class="fas fa-film"></i>
                <h3>No Movies Yet</h3>
                <p>New movies are coming soon. Stay tuned for updates.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($recentMovies as $m): ?>
                    <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                        <div class="card-poster">
                            <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                            <?php if (!empty($m['quality'])): ?>
                                <span class="card-badge"><?php e($m['quality']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($m['rating'])): ?>
                                <span class="card-badge rating"><i class="fas fa-star"></i> <?php e($m['rating']); ?></span>
                            <?php endif; ?>
                            <div class="card-overlay">
                                <button class="card-play-btn"><i class="fas fa-play"></i></button>
                            </div>
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
    </div>
</section>

<!-- Top Rated Movies -->
<?php if (!empty($topRatedMovies)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-star" style="color:#FFD700; margin-right:6px;"></i> <?php echo t('Top Rated'); ?></h2>
            <a href="<?php e(BASE_URL); ?>/top-rated.php" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="scroll-row">
            <?php foreach ($topRatedMovies as $m): ?>
                <?php if (empty($m['rating'])) { continue; } ?>
                <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                    <div class="card-poster">
                        <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                        <?php if (!empty($m['quality'])): ?>
                            <span class="card-badge"><?php e($m['quality']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($m['rating'])): ?>
                            <span class="card-badge rating"><i class="fas fa-star"></i> <?php e($m['rating']); ?></span>
                        <?php endif; ?>
                        <div class="card-overlay">
                            <button class="card-play-btn"><i class="fas fa-play"></i></button>
                        </div>
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
    </div>
</section>
<?php endif; ?>

<!-- Popular in Bangladesh -->
<?php if (!empty($popularBD)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-map-marker-alt" style="color:#2ecc71; margin-right:6px;"></i> <?php echo t('Popular in Bangladesh'); ?></h2>
        </div>
        <div class="scroll-row">
            <?php foreach ($popularBD as $m): ?>
                <a href="<?php e(BASE_URL); ?>/movie/<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                    <div class="card-poster">
                        <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                        <?php if (!empty($m['quality'])): ?><span class="card-badge"><?php e($m['quality']); ?></span><?php endif; ?>
                    </div>
                    <div class="card-info">
                        <div class="card-title"><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></div>
                        <div class="card-meta"><span><i class="fas fa-eye"></i> <?php echo number_format(intval(isset($m['views']) ? $m['views'] : 0)); ?> views</span></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Anime Section (always visible) -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title anime-accent"><?php echo t('Latest Anime'); ?></h2>
            <a href="<?php e(BASE_URL); ?>/anime.php" class="section-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php if (empty($latestAnime)): ?>
            <div class="empty-state">
                <i class="fas fa-tv"></i>
                <h3>No Anime Yet</h3>
                <p>New anime titles are coming soon. Stay tuned for updates.</p>
            </div>
        <?php else: ?>
            <div class="scroll-row">
                <?php foreach ($latestAnime as $a): ?>
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
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Recently Added -->
<?php if (!empty($recentlyAdded)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-plus-circle" style="color:var(--primary); margin-right:6px;"></i> Recently Added</h2>
            <a href="<?php e(BASE_URL); ?>/search.php?sort=newest" class="section-link"><?php echo t('View All'); ?> <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="scroll-row">
            <?php foreach ($recentlyAdded as $m): ?>
                <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                    <div class="card-poster">
                        <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                        <?php if (!empty($m['quality'])): ?><span class="card-badge"><?php e($m['quality']); ?></span><?php endif; ?>
                        <div class="card-overlay"><button class="card-play-btn"><i class="fas fa-play"></i></button></div>
                    </div>
                    <div class="card-info">
                        <div class="card-title"><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></div>
                        <div class="card-meta"><span><i class="far fa-calendar"></i> <?php e(isset($m['year']) ? $m['year'] : ''); ?></span></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Coming Soon -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-hourglass-half" style="color:#9b59b6; margin-right:6px;"></i> Coming Soon</h2>
        </div>
        <?php if (empty($comingSoon)): ?>
            <div class="empty-state">
                <i class="fas fa-hourglass-half"></i>
                <h3>Nothing Announced Yet</h3>
                <p>No upcoming titles have been announced. Check back soon — or <a href="<?php e(BASE_URL); ?>/request.php">request a title</a>.</p>
            </div>
        <?php else: ?>
            <div class="scroll-row">
                <?php foreach ($comingSoon as $m): ?>
                    <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" class="movie-card">
                        <div class="card-poster">
                            <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php e(isset($m['title']) ? $m['title'] : 'Movie'); ?>" loading="lazy">
                            <span class="card-badge"><?php e(isset($m['year']) ? $m['year'] : ''); ?></span>
                            <div class="card-overlay"><button class="card-play-btn"><i class="fas fa-play"></i></button></div>
                        </div>
                        <div class="card-info">
                            <div class="card-title"><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></div>
                            <div class="card-meta"><span><i class="far fa-calendar"></i> <?php e(isset($m['year']) ? $m['year'] : ''); ?></span></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
