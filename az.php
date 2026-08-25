<?php
// BDMovieHub - A-Z Directory (Movies + Anime)
// Inspired by movielinkbd / mlsbd alphabetical index pages.

require_once __DIR__ . '/config.php';

$pageSection = 'browse';
$pageTitle   = 'Browse A-Z';

$movies    = getPublishedMovies();
$animeList = getPublishedAnime();

// Build one combined, letter-indexed directory.
$directory = array(); // letter => array of items
foreach ($movies as $m) {
    $title = isset($m['title']) ? $m['title'] : '';
    if ($title === '') { continue; }
    $letter = strtoupper(mb_substr(trim($title), 0, 1, 'UTF-8'));
    if (!preg_match('/^[A-Z]$/', $letter)) { $letter = '#'; }
    $directory[$letter][] = array(
        'type'  => 'movie',
        'title' => $title,
        'slug'  => isset($m['slug']) ? $m['slug'] : '',
        'year'  => isset($m['year']) ? $m['year'] : '',
        'url'   => BASE_URL . '/movie.php?slug=' . urlencode(isset($m['slug']) ? $m['slug'] : ''),
    );
}
foreach ($animeList as $a) {
    $title = isset($a['title']) ? $a['title'] : '';
    if ($title === '') { continue; }
    $letter = strtoupper(mb_substr(trim($title), 0, 1, 'UTF-8'));
    if (!preg_match('/^[A-Z]$/', $letter)) { $letter = '#'; }
    $directory[$letter][] = array(
        'type'  => 'anime',
        'title' => $title,
        'slug'  => isset($a['slug']) ? $a['slug'] : '',
        'year'  => isset($a['aired']) ? $a['aired'] : '',
        'url'   => BASE_URL . '/anime.php?slug=' . urlencode(isset($a['slug']) ? $a['slug'] : ''),
    );
}

// Sort letters (# last) and each list alphabetically.
$letters = array_keys($directory);
usort($letters, function ($x, $y) {
    if ($x === '#') { return 1; }
    if ($y === '#') { return -1; }
    return strcmp($x, $y);
});
foreach ($directory as $letter => $items) {
    usort($items, function ($x, $y) { return strcasecmp($x['title'], $y['title']); });
    $directory[$letter] = $items;
}

// Active letter filter (?letter=A). Empty = show all groups.
$activeLetter = isset($_GET['letter']) ? strtoupper(substr(trim($_GET['letter']), 0, 1)) : '';
if ($activeLetter !== '' && !isset($directory[$activeLetter])) {
    $activeLetter = '';
}

include __DIR__ . '/header.php';
?>

<section class="section" style="margin-top: var(--nav-h);">
    <div class="container">
        <h1 class="section-title" style="margin-bottom: 12px;">Browse A-Z</h1>
        <p style="color: var(--muted); margin-bottom: 24px;">
            Every movie and series in the catalog, sorted alphabetically. Pick a letter to jump straight to it.
        </p>

        <!-- Letter quick-nav -->
        <div class="az-nav">
            <a href="<?php e(BASE_URL); ?>/az.php" class="az-letter<?php echo $activeLetter === '' ? ' active' : ''; ?>">All</a>
            <?php foreach ($letters as $letter): ?>
                <a href="<?php e(BASE_URL); ?>/az.php?letter=<?php echo urlencode($letter); ?>"
                   class="az-letter<?php echo $activeLetter === $letter ? ' active' : ''; ?>"><?php echo htmlspecialchars($letter, ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($directory)): ?>
            <div class="empty-state">
                <i class="fas fa-film"></i>
                <p>No titles available yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <?php foreach ($letters as $letter): ?>
                <?php if ($activeLetter !== '' && $letter !== $activeLetter) { continue; } ?>
                <div class="az-group" id="letter-<?php echo htmlspecialchars($letter === '#' ? 'num' : $letter, ENT_QUOTES, 'UTF-8'); ?>">
                    <h2 class="section-title az-group-title"><?php echo htmlspecialchars($letter === '#' ? '0-9' : $letter, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <div class="az-items">
                        <?php foreach ($directory[$letter] as $item): ?>
                            <a href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" class="az-item">
                                <span class="az-item-type <?php echo $item['type'] === 'movie' ? 'is-movie' : 'is-anime'; ?>">
                                    <?php echo $item['type'] === 'movie' ? 'Movie' : 'Series'; ?>
                                </span>
                                <span class="az-item-title"><?php e($item['title']); ?></span>
                                <?php if ($item['year'] !== ''): ?>
                                    <span class="az-item-year"><?php e($item['year']); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
