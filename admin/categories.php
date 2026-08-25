<?php
// BDMovieHub - Admin Categories Manager
require_once __DIR__ . '/../config.php';
$adminPage = 'categories';
$pageTitle = 'Categories';

$categories = getData(FILE_CATEGORIES);

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Categories</h2>
        <div style="display:flex; gap:10px;">
            <a href="<?php e($adminUrl); ?>/genres.php" class="btn-admin btn-admin-outline"><i class="fas fa-tags"></i> Manage Genres</a>
            <a href="<?php e($adminUrl); ?>/category-add.php" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add Category</a>
        </div>
    </div>

    <?php if (empty($categories)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No categories yet.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Slug</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td><code><?php e(isset($c['id']) ? $c['id'] : '-'); ?></code></td>
                    <td><?php e(isset($c['name']) ? $c['name'] : '-'); ?></td>
                    <td><code style="color:#a0a0b8;"><?php e(isset($c['slug']) ? $c['slug'] : '-'); ?></code></td>
                    <td>
                        <div class="table-actions">
                            <form method="POST" action="<?php e($adminUrl); ?>/category-delete.php" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($c['id']) ? $c['id'] : '', ENT_QUOTES); ?>">
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
