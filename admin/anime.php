<?php
// BDMovieHub - Admin Anime List
require_once __DIR__ . '/../config.php';
$adminPage = 'anime';
$pageTitle = 'Anime';

$animeList = getData(FILE_ANIME);
usort($animeList, function($a, $b) {
    $ta = isset($a['created_at']) ? $a['created_at'] : '';
    $tb = isset($b['created_at']) ? $b['created_at'] : '';
    return strcmp($tb, $ta);
});

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">All Anime (<?php echo count($animeList); ?>)</h2>
        <a href="<?php e($adminUrl); ?>/anime-add.php" class="btn-admin btn-admin-primary" style="background:#9b59b6;"><i class="fas fa-plus"></i> Add New Anime</a>
    </div>
    <?php if (empty($animeList)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:40px;">No anime found. Click "Add New Anime" to create one.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Poster</th>
                    <th>Title</th>
                    <th>Episodes</th>
                    <th>Status</th>
                    <th>Rating</th>
                    <th>Studio</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($animeList as $a): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars(isset($a['poster']) ? $a['poster'] : '', ENT_QUOTES); ?>" class="thumb" alt=""></td>
                    <td><?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?></td>
                    <td><?php e(isset($a['episode_count']) ? $a['episode_count'] : 0); ?></td>
                    <td>
                        <span style="padding:2px 8px; border-radius:4px; font-size:11px; background:<?php echo (isset($a['status']) ? $a['status'] : 'ongoing') === 'completed' ? 'rgba(46,204,113,0.15)' : 'rgba(155,89,182,0.15)'; ?>; color:<?php echo (isset($a['status']) ? $a['status'] : 'ongoing') === 'completed' ? '#2ecc71' : '#9b59b6'; ?>;">
                            <?php e(ucfirst(isset($a['status']) ? $a['status'] : 'ongoing')); ?>
                        </span>
                    </td>
                    <td><?php e(isset($a['rating']) ? $a['rating'] : '-'); ?></td>
                    <td><?php e(isset($a['studio']) ? $a['studio'] : '-'); ?></td>
                    <td><?php echo !empty($a['featured']) ? '<i class="fas fa-star" style="color:#FFD700;"></i>' : '-'; ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?php e(BASE_URL); ?>/anime-watch.php?slug=<?php echo urlencode(isset($a['slug']) ? $a['slug'] : ''); ?>" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?php e($adminUrl); ?>/episodes.php?anime_id=<?php echo urlencode(isset($a['id']) ? $a['id'] : ''); ?>" class="btn-admin btn-admin-outline btn-admin-sm" title="Episodes"><i class="fas fa-list-ol"></i></a>
                            <a href="<?php e($adminUrl); ?>/anime-edit.php?id=<?php echo urlencode(isset($a['id']) ? $a['id'] : ''); ?>" class="btn-admin btn-admin-outline btn-admin-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?php e($adminUrl); ?>/anime-delete.php" style="display:inline;" onsubmit="return confirm('Delete anime '<?php e(isset($a['title']) ? $a['title'] : 'Untitled'); ?>' and all its episodes?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($a['id']) ? $a['id'] : '', ENT_QUOTES); ?>">
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
