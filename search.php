<?php
// BDMovieHub - Combined Search (Movies + Anime)

require_once __DIR__ . '/config.php';

$pageSection = 'search';
$pageTitle = 'Search';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'movies';
$genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$year = isset($_GET['year']) ? trim($_GET['year']) : '';
$lang = isset($_GET['lang']) ? trim($_GET['lang']) : '';
$quality = isset($_GET['quality']) ? trim($_GET['quality']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';

$results = array();
if ($q !== '') {
    // Normalize punctuation so "spider-man-brand-new-day" matches "Spider-Man: Brand New Day"
    $__norm = function ($s) { return preg_replace('/[^\p{L}\p{N}]+/u', ' ', strtolower(trim($s))); };
    $ql = $__norm($q);
    // Spelling tolerance: allow one character difference for words >= 5 chars
    $fuzzy = function ($haystack) use ($ql) {
        if ($ql === '') { return false; }
        if (strpos($haystack, $ql) !== false) { return true; }
        if (mb_strlen($ql, 'UTF-8') >= 5) {
            foreach (preg_split('/\s+/u', $haystack) as $word) {
                if (mb_strlen($word, 'UTF-8') >= 5 && levenshtein(mb_substr($word, 0, 255), mb_substr($ql, 0, 255)) <= 1) { return true; }
            }
        }
        return false;
    };
    foreach (getPublishedMovies() as $m) {
        $title = $__norm(isset($m['title']) ? $m['title'] : '');
        $alt   = $__norm(isset($m['alternate_title']) ? $m['alternate_title'] : '');
        $cast  = $__norm(isset($m['cast']) ? $m['cast'] : '');
        $dir   = $__norm(isset($m['director']) ? $m['director'] : '');
        $match = $fuzzy($title) || ($alt !== '' && $fuzzy($alt)) || ($cast !== '' && strpos($cast, $ql) !== false) || ($dir !== '' && strpos($dir, $ql) !== false);
        if (!$match) { continue; }
        if ($genre !== '' && !(isset($m['genre']) && is_array($m['genre']) && in_array($genre, $m['genre']))) { continue; }
        if ($year !== '' && (string)(isset($m['year']) ? $m['year'] : '') !== $year) { continue; }
        if ($lang !== '' && strtolower((string)(isset($m['language']) ? $m['language'] : '')) !== strtolower($lang)) { continue; }
        if ($quality !== '' && strtolower((string)(isset($m['quality']) ? $m['quality'] : '')) !== strtolower($quality)) { continue; }
        $m['_type'] = 'movie';
        $results['movies'][] = $m;
    }
    foreach (getPublishedAnime() as $a) {
        $title = $__norm(isset($a['title']) ? $a['title'] : '');
        $alt   = $__norm(isset($a['alternate_title']) ? $a['alternate_title'] : '');
        $match = $fuzzy($title) || ($alt !== '' && $fuzzy($alt));
        if (!$match) { continue; }
        if ($genre !== '' && !(isset($a['genre']) && is_array($a['genre']) && in_array($genre, $a['genre']))) { continue; }
        if ($year !== '') { continue; } // anime uses 'aired', not a single year filter here
        $a['_type'] = 'anime';
        $results['anime'][] = $a;
    }
}

$movies = isset($results['movies']) ? $results['movies'] : array();
$animeRes = isset($results['anime']) ? $results['anime'] : array();

// Sorting
$__sorters = array(
    'newest' => function ($a, $b) { return strcmp(isset($b['created_at']) ? $b['created_at'] : '', isset($a['created_at']) ? $a['created_at'] : ''); },
    'rating' => function ($a, $b) { return floatval(isset($b['rating']) ? $b['rating'] : 0) <=> floatval(isset($a['rating']) ? $a['rating'] : 0); },
    'views'  => function ($a, $b) { return intval(isset($b['views']) ? $b['views'] : 0) <=> intval(isset($a['views']) ? $a['views'] : 0); },
    'relevance' => null,
);
if (isset($__sorters[$sort]) && $__sorters[$sort] !== null) {
    usort($movies, $__sorters[$sort]);
    usort($animeRes, $__sorters[$sort]);
}
unset($__sorters);

// Build year list from catalog
$years = array();
foreach (getPublishedMovies() as $m) {
    if (!empty($m['year'])) { $years[(string)$m['year']] = true; }
}
$years = array_keys($years);
rsort($years);

$moviePag = paginate($movies, $page, 20);
$animePag = paginate($animeRes, $page, 20);
$qsBase = function ($overrides = array()) use ($q, $tab, $genre, $year, $lang, $quality, $sort) {
    $params = array_filter(array_merge(array('q' => $q, 'tab' => $tab, 'genre' => $genre, 'year' => $year, 'lang' => $lang, 'quality' => $quality, 'sort' => $sort), $overrides), function ($v) { return $v !== '' && $v !== null; });
    return BASE_URL . '/search.php?' . http_build_query($params);
};

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
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; max-width:900px;">
                <select name="genre" aria-label="Genre filter" style="padding:8px 10px; background:var(--card); color:var(--text); border:1px solid var(--border); border-radius:6px;">
                    <option value="">All Genres</option>
                    <?php foreach (getAllGenres() as $g): ?><option value="<?php e($g); ?>" <?php echo $g === $genre ? 'selected' : ''; ?>><?php e($g); ?></option><?php endforeach; ?>
                </select>
                <select name="year" aria-label="Year filter" style="padding:8px 10px; background:var(--card); color:var(--text); border:1px solid var(--border); border-radius:6px;">
                    <option value="">All Years</option>
                    <?php foreach ($years as $y): ?><option value="<?php e($y); ?>" <?php echo $y === $year ? 'selected' : ''; ?>><?php e($y); ?></option><?php endforeach; ?>
                </select>
                <select name="lang" aria-label="Language filter" style="padding:8px 10px; background:var(--card); color:var(--text); border:1px solid var(--border); border-radius:6px;">
                    <option value="">All Languages</option>
                    <?php foreach (array('Bangla', 'Hindi', 'English', 'Korean', 'Japanese', 'Tamil', 'Telugu') as $l): ?>
                        <option value="<?php e($l); ?>" <?php echo strcasecmp($l, $lang) === 0 ? 'selected' : ''; ?>><?php e($l); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="quality" aria-label="Quality filter" style="padding:8px 10px; background:var(--card); color:var(--text); border:1px solid var(--border); border-radius:6px;">
                    <option value="">Any Quality</option>
                    <?php foreach (array('HD', 'FHD', '4K', 'CAM', 'TS', 'SD') as $qq): ?>
                        <option value="<?php e($qq); ?>" <?php echo strcasecmp($qq, $quality) === 0 ? 'selected' : ''; ?>><?php e($qq); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" aria-label="Sort results" style="padding:8px 10px; background:var(--card); color:var(--text); border:1px solid var(--border); border-radius:6px;">
                    <option value="relevance" <?php echo $sort === 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Rating</option>
                    <option value="views" <?php echo $sort === 'views' ? 'selected' : ''; ?>>Most Viewed</option>
                </select>
            </div>
        </form>

        <?php if ($q !== ''): ?>
            <?php
            $__active = array_filter(array('genre' => $genre, 'year' => $year, 'lang' => $lang, 'quality' => $quality), function ($v) { return $v !== '' && $v !== null; });
            if (!empty($__active)):
            ?>
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px;">
                <?php foreach ($__active as $k => $v): ?>
                    <a href="<?php echo htmlspecialchars($qsBase(array($k => '')), ENT_QUOTES, 'UTF-8'); ?>" class="genre-pill active" title="Remove filter"><?php e(ucfirst($k)); ?>: <?php e($v); ?> &times;</a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
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
                        <p>Try a different search term or remove some filters. You can also explore <a href="<?php e(BASE_URL); ?>/trending.php">Trending Now</a>.</p>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; justify-content:center; margin-top:12px;">
                            <?php foreach (array_slice(getAllGenres(), 0, 6) as $g): ?>
                                <a href="<?php echo htmlspecialchars($qsBase(array('genre' => $g, 'q' => '')), ENT_QUOTES, 'UTF-8'); ?>" class="genre-pill"><?php e($g); ?></a>
                            <?php endforeach; ?>
                        </div>
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
                                <a href="<?php echo htmlspecialchars($qsBase(array('page' => $i)), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $i; ?></a>
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
                        <p>Try a different search term or explore <a href="<?php e(BASE_URL); ?>/anime.php">all anime</a>.</p>
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
                                <a href="<?php echo htmlspecialchars($qsBase(array('page' => $i)), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $i; ?></a>
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
