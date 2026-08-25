<?php
// BDMovieHub - Admin Episodes List (filtered by anime_id)
require_once __DIR__ . '/../config.php';
$adminPage = 'episodes';
$pageTitle = 'Episodes';

$anime_id = isset($_GET['anime_id']) ? $_GET['anime_id'] : '';

$animeList = getData(FILE_ANIME);
$allEps = getData(FILE_EPISODES);

if ($anime_id !== '') {
    $anime = getById($animeList, $anime_id);
    if (!$anime) {
        setFlash('error', 'Anime not found.');
        adminRedirect('episodes.php');
    }
    $pageTitle = 'Episodes - ' . (isset($anime['title']) ? $anime['title'] : 'Untitled');
    $episodes = getEpisodesByAnime($anime_id);
} else {
    $anime = null;
    // Sort all episodes by anime then episode_number
    $episodes = $allEps;
    usort($episodes, function($a, $b) {
        $aa = isset($a['anime_id']) ? $a['anime_id'] : '';
        $bb = isset($b['anime_id']) ? $b['anime_id'] : '';
        if ($aa !== $bb) { return strcmp($aa, $bb); }
        $na = isset($a['episode_number']) ? intval($a['episode_number']) : 0;
        $nb = isset($b['episode_number']) ? intval($b['episode_number']) : 0;
        return $na - $nb;
    });
}

// Map anime_id -> title
$animeMap = array();
foreach ($animeList as $a) { $animeMap[isset($a['id']) ? $a['id'] : ''] = isset($a['title']) ? $a['title'] : 'Untitled'; }

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
        <h2 style="font-size:20px;">
            <?php if ($anime): ?>
                Episodes: <?php e(isset($anime['title']) ? $anime['title'] : 'Untitled'); ?> (<?php echo count($episodes); ?>)
            <?php else: ?>
                All Episodes (<?php echo count($episodes); ?>)
            <?php endif; ?>
        </h2>
        <div style="display:flex; gap:8px;">
            <a href="<?php e($adminUrl); ?>/episodes.php" class="btn-admin btn-admin-outline btn-admin-sm">All</a>
            <a href="<?php e($adminUrl); ?>/episode-add.php<?php echo $anime ? '?anime_id=' . urlencode(isset($anime['id']) ? $anime['id'] : '') : ''; ?>" class="btn-admin btn-admin-success"><i class="fas fa-plus"></i> Add Episode</a>
        </div>
    </div>

    <?php if (empty($episodes)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:40px;">No episodes found.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <?php if (!$anime): ?><th>Anime</th><?php endif; ?>
                    <th>Title</th>
                    <th>Stream URL</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($episodes as $ep): ?>
                <tr>
                    <td><?php e(isset($ep['episode_number']) ? $ep['episode_number'] : '-'); ?></td>
                    <?php if (!$anime): ?>
                    <td><?php e(isset($animeMap[$ep['anime_id']]) ? $animeMap[$ep['anime_id']] : '-'); ?></td>
                    <?php endif; ?>
                    <td><?php e(isset($ep['title']) ? $ep['title'] : '-'); ?></td>
                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php e(isset($ep['stream_url']) ? $ep['stream_url'] : '-'); ?></td>
                    <td><?php e(isset($ep['created_at']) ? $ep['created_at'] : '-'); ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?php e($adminUrl); ?>/episode-edit.php?id=<?php echo urlencode(isset($ep['id']) ? $ep['id'] : ''); ?>" class="btn-admin btn-admin-outline btn-admin-sm"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?php e($adminUrl); ?>/episode-delete.php" style="display:inline;" onsubmit="return confirm('Delete this episode?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($ep['id']) ? $ep['id'] : '', ENT_QUOTES); ?>">
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
