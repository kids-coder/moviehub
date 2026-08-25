<?php
// BDMovieHub - Admin Pages List
require_once __DIR__ . '/../config.php';
$adminPage = 'pages';
$pageTitle = 'Pages';

$pages = getData(FILE_PAGES);
usort($pages, function($a, $b) {
    $ta = isset($a['created_at']) ? $a['created_at'] : '';
    $tb = isset($b['created_at']) ? $b['created_at'] : '';
    return strcmp($tb, $ta);
});

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">All Pages (<?php echo count($pages); ?>)</h2>
        <a href="<?php e($adminUrl); ?>/page-add.php" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add New Page</a>
    </div>
    <?php if (empty($pages)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:40px;">No pages yet.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $p): ?>
                <tr>
                    <td><?php e($p['title']); ?></td>
                    <td><code style="background:#0a0a0f; padding:2px 6px; border-radius:4px; font-size:12px;"><?php e($p['slug']); ?></code></td>
                    <td>
                        <span style="padding:2px 8px; border-radius:4px; font-size:11px; background:<?php echo $p['status'] === 'published' ? 'rgba(46,204,113,0.15)' : 'rgba(231,76,60,0.15)'; ?>; color:<?php echo $p['status'] === 'published' ? '#2ecc71' : '#e74c3c'; ?>;">
                            <?php e(ucfirst($p['status'])); ?>
                        </span>
                    </td>
                    <td><?php e(isset($p['created_at']) ? $p['created_at'] : '-'); ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?php e(BASE_URL); ?>/page.php?slug=<?php echo urlencode($p['slug']); ?>" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?php e($adminUrl); ?>/page-edit.php?id=<?php echo urlencode($p['id']); ?>" class="btn-admin btn-admin-outline btn-admin-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?php e($adminUrl); ?>/page-delete.php" style="display:inline;" onsubmit="return confirm('Delete page '<?php e($p['title']); ?>'?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($p['id'], ENT_QUOTES); ?>">
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
