<?php
// BDMovieHub - Admin Dashboard
require_once __DIR__ . '/../config.php';
$adminPage = 'dashboard';
$pageTitle = 'Dashboard';

$counts = array(
    'movies'   => count(getData(FILE_MOVIES)),
    'anime'    => count(getData(FILE_ANIME)),
    'episodes' => count(getData(FILE_EPISODES)),
    'pages'    => count(getData(FILE_PAGES)),
    'users'    => count(getData(FILE_USERS)),
    'schedule' => count(getData(FILE_SCHEDULE)),
    'featured' => count(getData(FILE_FEATURED)),
    'slides'   => count(getData(FILE_SLIDES)),
);

$recentMovies = array_slice(getData(FILE_MOVIES), 0, 5);
$recentAnime  = array_slice(getData(FILE_ANIME), 0, 5);

// Content validation: published items missing key metadata
$__validationIssues = array();
foreach (getData(FILE_MOVIES) as $__m) {
    if ((isset($__m['status']) ? $__m['status'] : '') !== 'published') { continue; }
    $__missing = array();
    foreach (array('poster', 'description', 'year', 'genre', 'stream_url') as $__f) {
        $v = isset($__m[$__f]) ? $__m[$__f] : '';
        if ($v === '' || $v === array()) { $__missing[] = $__f; }
    }
    if (!empty($__missing)) {
        $__validationIssues[] = array('type' => 'movie', 'title' => isset($__m['title']) ? $__m['title'] : 'Untitled', 'id' => isset($__m['id']) ? $__m['id'] : '', 'missing' => $__missing);
    }
}
foreach (getData(FILE_ANIME) as $__a) {
    if ((isset($__a['status_pub']) ? $__a['status_pub'] : '') !== 'published') { continue; }
    $__missing = array();
    foreach (array('poster', 'description', 'episode_count') as $__f) {
        $v = isset($__a[$__f]) ? $__a[$__f] : '';
        if ($v === '' || $v === array() || $v === 0) { $__missing[] = $__f; }
    }
    if (!empty($__missing)) {
        $__validationIssues[] = array('type' => 'anime', 'title' => isset($__a['title']) ? $__a['title'] : 'Untitled', 'id' => isset($__a['id']) ? $__a['id'] : '', 'missing' => $__missing);
    }
}

include __DIR__ . '/header.php';
?>

<!-- Content Quality Validation -->
<?php if (!empty($__validationIssues)): ?>
<div class="admin-card" style="border-left:4px solid #f1c40f;">
    <h2 style="font-size:18px; margin-bottom:12px;"><i class="fas fa-exclamation-triangle" style="color:#f1c40f;"></i> Content Quality: <?php echo count($__validationIssues); ?> item(s) need attention</h2>
    <div class="data-table-wrap">
        <table class="data-table">
            <thead><tr><th>Type</th><th>Title</th><th>Missing Fields</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($__validationIssues, 0, 10) as $__vi): ?>
                <tr>
                    <td><?php e(ucfirst($__vi['type'])); ?></td>
                    <td><?php e($__vi['title']); ?></td>
                    <td><code style="color:#f1c40f;"><?php e(implode(', ', $__vi['missing'])); ?></code></td>
                    <td>
                        <a href="<?php e($adminUrl); ?>/<?php echo $__vi['type'] === 'movie' ? 'movie' : 'anime'; ?>-edit.php?id=<?php echo urlencode($__vi['id']); ?>" class="btn-admin btn-admin-outline btn-admin-sm"><i class="fas fa-edit"></i> Fix</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="icon blue"><i class="fas fa-film"></i></div>
        <div class="value"><?php echo $counts['movies']; ?></div>
        <div class="label">Total Movies</div>
    </div>
    <div class="stat-card">
        <div class="icon purple"><i class="fas fa-tv"></i></div>
        <div class="value"><?php echo $counts['anime']; ?></div>
        <div class="label">Total Anime</div>
    </div>
    <div class="stat-card">
        <div class="icon green"><i class="fas fa-list-ol"></i></div>
        <div class="value"><?php echo $counts['episodes']; ?></div>
        <div class="label">Episodes</div>
    </div>
    <div class="stat-card">
        <div class="icon orange"><i class="fas fa-file-alt"></i></div>
        <div class="value"><?php echo $counts['pages']; ?></div>
        <div class="label">Pages</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-card">
    <h2 style="font-size:18px; margin-bottom:16px;">Quick Actions</h2>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        <a href="<?php e($adminUrl); ?>/movie-add.php" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add Movie</a>
        <a href="<?php e($adminUrl); ?>/anime-add.php" class="btn-admin btn-admin-primary" style="background:#9b59b6;"><i class="fas fa-plus"></i> Add Anime</a>
        <a href="<?php e($adminUrl); ?>/episode-add.php" class="btn-admin btn-admin-success"><i class="fas fa-plus"></i> Add Episode</a>
        <a href="<?php e($adminUrl); ?>/page-add.php" class="btn-admin btn-admin-outline"><i class="fas fa-plus"></i> Add Page</a>
        <a href="<?php e($adminUrl); ?>/settings.php" class="btn-admin btn-admin-outline"><i class="fas fa-cog"></i> Settings</a>
    </div>
</div>

<!-- Recent Movies -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="font-size:18px;">Recent Movies</h2>
        <a href="<?php e($adminUrl); ?>/movies.php" class="btn-admin btn-admin-outline btn-admin-sm">View All</a>
    </div>
    <?php if (empty($recentMovies)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No movies yet. <a href="<?php e($adminUrl); ?>/movie-add.php" style="color:#469AFF;">Add one</a>.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Title</th><th>Year</th><th>Quality</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentMovies as $m): ?>
                <?php
                    $__mStatus = isset($m['status']) ? $m['status'] : 'draft';
                    $__mPubBg = $__mStatus === 'published' ? 'rgba(46,204,113,0.15)' : 'rgba(231,76,60,0.15)';
                    $__mPubFg = $__mStatus === 'published' ? '#2ecc71' : '#e74c3c';
                ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES); ?>" style="width:30px; height:45px; object-fit:cover; border-radius:4px;" alt="">
                            <span><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></span>
                        </div>
                    </td>
                    <td><?php e(isset($m['year']) ? $m['year'] : '-'); ?></td>
                    <td><?php e(isset($m['quality']) ? $m['quality'] : '-'); ?></td>
                    <td><span style="padding:2px 8px; border-radius:4px; font-size:11px; background:<?php echo $__mPubBg; ?>; color:<?php echo $__mPubFg; ?>;"><?php e(ucfirst($__mStatus)); ?></span></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?php e($adminUrl); ?>/movie-edit.php?id=<?php echo urlencode($m['id']); ?>" class="btn-admin btn-admin-outline btn-admin-sm"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?php e($adminUrl); ?>/movie-delete.php" style="display:inline;" onsubmit="return confirm('Delete this movie?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($m['id'], ENT_QUOTES); ?>">
                                <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<!-- Recent Anime -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="font-size:18px;">Recent Anime</h2>
        <a href="<?php e($adminUrl); ?>/anime.php" class="btn-admin btn-admin-outline btn-admin-sm">View All</a>
    </div>
    <?php if (empty($recentAnime)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No anime yet. <a href="<?php e($adminUrl); ?>/anime-add.php" style="color:#9b59b6;">Add one</a>.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Title</th><th>Episodes</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentAnime as $a): ?>
                <?php
                    $__aStatus = isset($a['status']) ? $a['status'] : 'ongoing';
                    $__aBg = $__aStatus === 'completed' ? 'rgba(46,204,113,0.15)' : 'rgba(155,89,182,0.15)';
                    $__aFg = $__aStatus === 'completed' ? '#2ecc71' : '#9b59b6';
                ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="<?php echo htmlspecialchars(isset($a['poster']) ? $a['poster'] : '', ENT_QUOTES); ?>" style="width:30px; height:45px; object-fit:cover; border-radius:4px;" alt="">
                            <span><?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?></span>
                        </div>
                    </td>
                    <td><?php e(isset($a['episode_count']) ? $a['episode_count'] : 0); ?></td>
                    <td><span style="padding:2px 8px; border-radius:4px; font-size:11px; background:<?php echo $__aBg; ?>; color:<?php echo $__aFg; ?>;"><?php e(ucfirst($__aStatus)); ?></span></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?php e($adminUrl); ?>/anime-edit.php?id=<?php echo urlencode($a['id']); ?>" class="btn-admin btn-admin-outline btn-admin-sm"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?php e($adminUrl); ?>/anime-delete.php" style="display:inline;" onsubmit="return confirm('Delete this anime?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($a['id'], ENT_QUOTES); ?>">
                                <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
