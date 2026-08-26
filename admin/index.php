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

// ---- Pending work counters (badges + alert cards) ----
$__pendingComments = 0;
foreach (getData(FILE_COMMENTS) as $__c) {
    if ((isset($__c['status']) ? $__c['status'] : 'pending') === 'pending') { $__pendingComments++; }
}
$__openReports = 0;
if (file_exists(DATA_DIR . '/reports.json')) {
    $__raw = @file_get_contents(DATA_DIR . '/reports.json');
    $__decoded = $__raw ? json_decode($__raw, true) : array();
    if (is_array($__decoded)) {
        foreach ($__decoded as $__r) { if (!(isset($__r['resolved']) && $__r['resolved'])) { $__openReports++; } }
    }
}
$__unreadContacts = 0;
if (file_exists(DATA_DIR . '/contacts.json')) {
    $__raw = @file_get_contents(DATA_DIR . '/contacts.json');
    $__decoded = $__raw ? json_decode($__raw, true) : array();
    if (is_array($__decoded)) {
        foreach ($__decoded as $__ct) { if (!(isset($__ct['read']) && $__ct['read'])) { $__unreadContacts++; } }
    }
}

// ---- Feature quick toggles (same keys as Settings → Feature Toggles) ----
$__featDefs = array(
    'enable_lowdata'       => array('Low-data mode', 'fa-feather'),
    'enable_notifications' => array('Notifications', 'fa-bell'),
    'enable_top10'         => array('Top 10 page', 'fa-fire'),
    'enable_az'            => array('A-Z directory', 'fa-font'),
    'enable_tvguide'       => array('TV guide', 'fa-calendar-alt'),
    'enable_downloads'     => array('Downloads', 'fa-download'),
    'enable_comment_votes' => array('Comment votes', 'fa-thumbs-up'),
    'enable_ratings'       => array('Star ratings', 'fa-star'),
);
$__cu = currentUser();
$__isAdmin = $__cu && (isset($__cu['role']) ? $__cu['role'] : '') === 'admin';

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

<!-- Pending Work Alerts -->
<?php if ($__pendingComments > 0 || $__openReports > 0 || $__unreadContacts > 0): ?>
<div class="admin-card" style="border-left:4px solid #ffa502;">
    <h2 style="font-size:18px; margin-bottom:12px;"><i class="fas fa-bell" style="color:#ffa502;"></i> Needs Your Attention</h2>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        <?php if ($__pendingComments > 0): ?>
        <a href="<?php e($adminUrl); ?>/comments.php" class="btn-admin btn-admin-outline"><i class="fas fa-comment"></i> <?php echo $__pendingComments; ?> comment(s) awaiting approval</a>
        <?php endif; ?>
        <?php if ($__openReports > 0): ?>
        <a href="<?php e($adminUrl); ?>/comments.php" class="btn-admin btn-admin-outline"><i class="fas fa-exclamation-circle"></i> <?php echo $__openReports; ?> broken-video report(s) open</a>
        <?php endif; ?>
        <?php if ($__unreadContacts > 0): ?>
        <a href="<?php e($adminUrl); ?>/comments.php" class="btn-admin btn-admin-outline"><i class="fas fa-envelope"></i> <?php echo $__unreadContacts; ?> unread message(s)</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Feature Quick Toggles -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="font-size:18px;">Website Features</h2>
        <a href="<?php e($adminUrl); ?>/settings.php" class="btn-admin btn-admin-outline btn-admin-sm">All Settings</a>
    </div>
    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:10px;">
        <?php foreach ($__featDefs as $__key => $__def): ?>
        <?php $__on = !isset($settings[$__key]) || !empty($settings[$__key]); ?>
        <?php if ($__isAdmin): ?>
        <form method="POST" action="<?php e($adminUrl); ?>/feature-toggle.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="key" value="<?php echo $__key; ?>">
            <input type="hidden" name="back" value="index.php">
            <button type="submit" title="Click to turn <?php echo $__on ? 'off' : 'on'; ?>" style="width:100%; display:flex; align-items:center; gap:8px; padding:10px 12px; border-radius:8px; border:1px solid <?php echo $__on ? 'rgba(46,204,113,0.4)' : '#2a2a3e'; ?>; background:<?php echo $__on ? 'rgba(46,204,113,0.08)' : '#0a0a0f'; ?>; color:#fff; cursor:pointer; font-size:12px; font-family:inherit; text-align:left;">
                <i class="fas <?php echo $__def[1]; ?>" style="color:<?php echo $__on ? '#2ecc71' : '#6b6b80'; ?>;"></i>
                <span style="flex:1;"><?php echo $__def[0]; ?></span>
                <span style="padding:2px 7px; border-radius:10px; font-size:10px; font-weight:700; background:<?php echo $__on ? 'rgba(46,204,113,0.2)' : 'rgba(107,107,128,0.2)'; ?>; color:<?php echo $__on ? '#2ecc71' : '#6b6b80'; ?>;"><?php echo $__on ? 'ON' : 'OFF'; ?></span>
            </button>
        </form>
        <?php else: ?>
        <div style="display:flex; align-items:center; gap:8px; padding:10px 12px; border-radius:8px; border:1px solid <?php echo $__on ? 'rgba(46,204,113,0.4)' : '#2a2a3e'; ?>; background:<?php echo $__on ? 'rgba(46,204,113,0.08)' : '#0a0a0f'; ?>; color:#fff; font-size:12px;">
            <i class="fas <?php echo $__def[1]; ?>" style="color:<?php echo $__on ? '#2ecc71' : '#6b6b80'; ?>;"></i>
            <span style="flex:1;"><?php echo $__def[0]; ?></span>
            <span style="padding:2px 7px; border-radius:10px; font-size:10px; font-weight:700; background:<?php echo $__on ? 'rgba(46,204,113,0.2)' : 'rgba(107,107,128,0.2)'; ?>; color:<?php echo $__on ? '#2ecc71' : '#6b6b80'; ?>;"><?php echo $__on ? 'ON' : 'OFF'; ?></span>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
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
        <a href="<?php e($adminUrl); ?>/analytics.php" class="btn-admin btn-admin-outline"><i class="fas fa-chart-line"></i> Analytics</a>
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
