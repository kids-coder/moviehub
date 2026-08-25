<?php
// BDMovieHub - Browse by Genre (Movies + Anime combined)
require_once __DIR__ . '/config.php';

$pageSection = 'movies';
$pageTitle   = 'Browse by Genre';

$genres = getAllGenres();
$selGenre = isset($_GET['genre']) ? trim($_GET['genre']) : '';

$movies = getPublishedMovies();
$animeList = getPublishedAnime();

if ($selGenre !== '') {
    $filteredMovies = array();
    foreach ($movies as $m) {
        $g = isset($m['genre']) ? $m['genre'] : array();
        if (is_array($g) && in_array($selGenre, $g)) { $filteredMovies[] = $m; }
    }
    $movies = $filteredMovies;

    $filteredAnime = array();
    foreach ($animeList as $a) {
        $g = isset($a['genre']) ? $a['genre'] : array();
        if (is_array($g) && in_array($selGenre, $g)) { $filteredAnime[] = $a; }
    }
    $animeList = $filteredAnime;
}

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title" style="margin-bottom: 20px;">Browse by Genre</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            Explore movies and anime by genre. Pick a category to filter the catalog.
        </p>

        <div class="genre-filter">
            <a href="<?php e(BASE_URL); ?>/genres.php" class="genre-pill <?php echo $selGenre === '' ? 'active' : ''; ?>">All</a>
            <?php if (!empty($genres)): ?>
                <?php foreach ($genres as $g): ?>
                    <a href="<?php e(BASE_URL); ?>/genres.php?genre=<?php echo urlencode($g); ?>" class="genre-pill <?php echo $selGenre === $g ? 'active' : ''; ?>">
                        <?php e($g); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($selGenre !== ''): ?>
            <h2 class="section-title" style="margin: 24px 0 16px;">
                Movies in <?php e($selGenre); ?> (<?php echo count($movies); ?>)
            </h2>
        <?php else: ?>
            <h2 class="section-title" style="margin: 24px 0 16px;">All Movies (<?php echo count($movies); ?>)</h2>
        <?php endif; ?>

        <?php if (empty($movies)): ?>
            <div class="empty-state">
                <i class="fas fa-film"></i>
                <h3>No Movies</h3>
                <p>No movies match this genre filter.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($movies as $m): ?>
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
        <?php endif; ?>

        <?php if ($selGenre !== ''): ?>
            <h2 class="section-title anime-accent" style="margin: 32px 0 16px;">
                Anime in <?php e($selGenre); ?> (<?php echo count($animeList); ?>)
            </h2>
        <?php else: ?>
            <h2 class="section-title anime-accent" style="margin: 32px 0 16px;">All Anime (<?php echo count($animeList); ?>)</h2>
        <?php endif; ?>

        <?php if (empty($animeList)): ?>
            <div class="empty-state">
                <i class="fas fa-tv"></i>
                <h3>No Anime</h3>
                <p>No anime match this genre filter.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($animeList as $a): ?>
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

<?php include __DIR__ . '/footer.php'; ?>
