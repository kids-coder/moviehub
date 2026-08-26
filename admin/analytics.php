<?php
// BDMovieHub - Admin Analytics (site statistics & top content)
require_once __DIR__ . '/../config.php';
$adminPage = 'analytics';
$pageTitle = 'Analytics';

$movies = getData(FILE_MOVIES);
$anime  = getData(FILE_ANIME);
$eps    = getData(FILE_EPISODES);
$comments = getData(FILE_COMMENTS);

// ---- Aggregate views ----
$totalMovieViews = 0;
foreach ($movies as $m) { $totalMovieViews += isset($m['views']) ? intval($m['views']) : 0; }
$totalAnimeViews = 0;
foreach ($anime as $a) { $totalAnimeViews += isset($a['views']) ? intval($a['views']) : 0; }

// ---- Top content by views (top 10 combined) ----
$topItems = array();
foreach ($movies as $m) {
    $topItems[] = array(
        'type'  => 'movie',
        'title' => isset($m['title']) ? $m['title'] : 'Untitled',
        'slug'  => isset($m['slug']) ? $m['slug'] : '',
        'views' => isset($m['views']) ? intval($m['views']) : 0,
        'rating' => isset($m['rating']) ? $m['rating'] : '',
    );
}
foreach ($anime as $a) {
    $topItems[] = array(
        'type'  => 'anime',
        'title' => isset($a['title']) ? $a['title'] : 'Untitled',
        'slug'  => isset($a['slug']) ? $a['slug'] : '',
        'views' => isset($a['views']) ? intval($a['views']) : 0,
        'rating' => isset($a['rating']) ? $a['rating'] : '',
    );
}
usort($topItems, function ($x, $y) { return $y['views'] - $x['views']; });
$topItems = array_slice($topItems, 0, 10);
$maxViews = !empty($topItems) ? max(1, $topItems[0]['views']) : 1;

// ---- Content status breakdown ----
$moviePub = 0; $movieDraft = 0;
foreach ($movies as $m) {
    if ((isset($m['status']) ? $m['status'] : '') === 'published') { $moviePub++; } else { $movieDraft++; }
}
$animePub = 0; $animeDraft = 0;
foreach ($anime as $a) {
    if ((isset($a['status_pub']) ? $a['status_pub'] : '') !== 'draft') { $animePub++; } else { $animeDraft++; }
}

// ---- Comment moderation stats ----
$cApproved = 0; $cPending = 0; $cSpam = 0;
foreach ($comments as $c) {
    $st = isset($c['status']) ? $c['status'] : 'pending';
    if ($st === 'approved') { $cApproved++; }
    elseif ($st === 'spam') { $cSpam++; }
    else { $cPending++; }
}

// ---- Genre distribution (published movies + anime) ----
$genreCounts = array();
foreach (getPublishedMovies() as $m) {
    foreach ((isset($m['genre']) && is_array($m['genre']) ? $m['genre'] : array()) as $g) {
        $g = trim((string)$g);
        if ($g !== '') { $genreCounts[$g] = (isset($genreCounts[$g]) ? $genreCounts[$g] : 0) + 1; }
    }
}
foreach (getPublishedAnime() as $a) {
    foreach ((isset($a['genre']) && is_array($a['genre']) ? $a['genre'] : array()) as $g) {
        $g = trim((string)$g);
        if ($g !== '') { $genreCounts[$g] = (isset($genreCounts[$g]) ? $genreCounts[$g] : 0) + 1; }
    }
}
arsort($genreCounts);
$genreCounts = array_slice($genreCounts, 0, 8, true);
$maxGenre = !empty($genreCounts) ? max(1, reset($genreCounts)) : 1;

// ---- Episodes per anime (busiest shows) ----
$epPerAnime = array();
foreach ($eps as $ep) {
    $aid = isset($ep['anime_id']) ? $ep['anime_id'] : '';
    if ($aid !== '') { $epPerAnime[$aid] = (isset($epPerAnime[$aid]) ? $epPerAnime[$aid] : 0) + 1; }
}
arsort($epPerAnime);
$epPerAnime = array_slice($epPerAnime, 0, 5, true);

include __DIR__ . '/header.php';
?>

<!-- Overview Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon blue"><i class="fas fa-eye"></i></div>
        <div class="value"><?php echo number_format($totalMovieViews); ?></div>
        <div class="label">Movie Views</div>
    </div>
    <div class="stat-card">
        <div class="icon purple"><i class="fas fa-eye"></i></div>
        <div class="value"><?php echo number_format($totalAnimeViews); ?></div>
        <div class="label">Anime Views</div>
    </div>
    <div class="stat-card">
        <div class="icon green"><i class="fas fa-check-circle"></i></div>
        <div class="value"><?php echo number_format($cApproved); ?></div>
        <div class="label">Approved Comments</div>
    </div>
    <div class="stat-card">
        <div class="icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div class="value"><?php echo number_format($cPending); ?></div>
        <div class="label">Pending Comments</div>
    </div>
</div>

<div class="form-row">
    <!-- Top Content -->
    <div class="admin-card">
        <h2 style="font-size:18px; margin-bottom:16px;"><i class="fas fa-fire" style="color:#ffa502;"></i> Top 10 by Views</h2>
        <?php if (empty($topItems)): ?>
            <p style="color:#a0a0b8; text-align:center; padding:20px;">No content yet.</p>
        <?php else: ?>
            <?php foreach ($topItems as $t): ?>
            <div style="margin-bottom:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <span style="font-size:13px;">
                        <i class="fas fa-<?php echo $t['type'] === 'anime' ? 'tv' : 'film'; ?>" style="color:<?php echo $t['type'] === 'anime' ? '#9b59b6' : '#469AFF'; ?>;"></i>
                        <?php e(truncate($t['title'], 40)); ?>
                    </span>
                    <strong style="font-size:12px;"><?php echo number_format($t['views']); ?></strong>
                </div>
                <div style="height:6px; background:#0a0a0f; border-radius:3px; overflow:hidden;">
                    <div style="height:100%; width:<?php echo round(($t['views'] / $maxViews) * 100, 1); ?>%; background:<?php echo $t['type'] === 'anime' ? '#9b59b6' : '#469AFF'; ?>;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div>
        <!-- Content Status -->
        <div class="admin-card" style="margin-bottom:20px;">
            <h2 style="font-size:18px; margin-bottom:16px;"><i class="fas fa-chart-pie" style="color:#469AFF;"></i> Content Status</h2>
            <table class="data-table">
                <thead><tr><th>Type</th><th>Published</th><th>Draft</th><th>Total</th></tr></thead>
                <tbody>
                    <tr><td>Movies</td><td style="color:#2ecc71;"><?php echo $moviePub; ?></td><td style="color:#ffa502;"><?php echo $movieDraft; ?></td><td><?php echo count($movies); ?></td></tr>
                    <tr><td>Anime</td><td style="color:#2ecc71;"><?php echo $animePub; ?></td><td style="color:#ffa502;"><?php echo $animeDraft; ?></td><td><?php echo count($anime); ?></td></tr>
                    <tr><td>Episodes</td><td colspan="2" style="color:#a0a0b8;">—</td><td><?php echo count($eps); ?></td></tr>
                    <tr><td>Comments</td><td style="color:#2ecc71;"><?php echo $cApproved; ?></td><td style="color:#e74c3c;"><?php echo $cSpam; ?> spam</td><td><?php echo count($comments); ?></td></tr>
                </tbody>
            </table>
        </div>

        <!-- Genre Distribution -->
        <div class="admin-card">
            <h2 style="font-size:18px; margin-bottom:16px;"><i class="fas fa-tags" style="color:#9b59b6;"></i> Top Genres</h2>
            <?php if (empty($genreCounts)): ?>
                <p style="color:#a0a0b8; text-align:center; padding:12px;">No genres assigned yet.</p>
            <?php else: ?>
                <?php foreach ($genreCounts as $g => $n): ?>
                <div style="margin-bottom:10px;">
                    <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:3px;">
                        <span><?php e($g); ?></span><strong><?php echo $n; ?></strong>
                    </div>
                    <div style="height:5px; background:#0a0a0f; border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:<?php echo round(($n / $maxGenre) * 100, 1); ?>%; background:#9b59b6;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Busiest Shows -->
<?php if (!empty($epPerAnime)): ?>
<div class="admin-card">
    <h2 style="font-size:18px; margin-bottom:16px;"><i class="fas fa-layer-group" style="color:#2ecc71;"></i> Most Episodes</h2>
    <div class="data-table-wrap">
    <table class="data-table">
        <thead><tr><th>Anime</th><th>Episodes in Library</th><th>Listed Count</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($epPerAnime as $aid => $n): ?>
            <?php $a = getById($anime, $aid); if (!$a) { continue; } ?>
            <tr>
                <td><?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?></td>
                <td><strong><?php echo $n; ?></strong></td>
                <td><?php echo isset($a['episode_count']) ? intval($a['episode_count']) : 0; ?></td>
                <td><a href="<?php e($adminUrl); ?>/episodes.php?anime_id=<?php echo urlencode($aid); ?>" class="btn-admin btn-admin-outline btn-admin-sm"><i class="fas fa-list-ol"></i> Manage</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
