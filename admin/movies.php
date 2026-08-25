<?php
// BDMovieHub - Admin Movies List
require_once __DIR__ . '/../config.php';
$adminPage = 'movies';
$pageTitle = 'Movies';

$movies = getData(FILE_MOVIES);
// Sort by created_at desc
usort($movies, function($a, $b) {
    $ta = isset($a['created_at']) ? $a['created_at'] : '';
    $tb = isset($b['created_at']) ? $b['created_at'] : '';
    return strcmp($tb, $ta);
});

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">All Movies (<?php echo count($movies); ?>)</h2>
        <a href="<?php e($adminUrl); ?>/movie-add.php" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add New Movie</a>
    </div>
    <?php if (empty($movies)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:40px;">No movies found. Click "Add New Movie" to create one.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Poster</th>
                    <th>Title</th>
                    <th>Year</th>
                    <th>Quality</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movies as $m): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars(isset($m['poster']) ? $m['poster'] : '', ENT_QUOTES); ?>" class="thumb" alt=""></td>
                    <td><?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?></td>
                    <td><?php e(isset($m['year']) ? $m['year'] : '-'); ?></td>
                    <td><?php e(isset($m['quality']) ? $m['quality'] : '-'); ?></td>
                    <td><?php e(isset($m['rating']) ? $m['rating'] : '-'); ?></td>
                    <td>
                        <span style="padding:2px 8px; border-radius:4px; font-size:11px; background:<?php echo (isset($m['status']) ? $m['status'] : 'draft') === 'published' ? 'rgba(46,204,113,0.15)' : 'rgba(231,76,60,0.15)'; ?>; color:<?php echo (isset($m['status']) ? $m['status'] : 'draft') === 'published' ? '#2ecc71' : '#e74c3c'; ?>;">
                            <?php e(ucfirst(isset($m['status']) ? $m['status'] : 'draft')); ?>
                        </span>
                    </td>
                    <td><?php echo !empty($m['featured']) ? '<i class="fas fa-star" style="color:#FFD700;"></i>' : '-'; ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?php e(BASE_URL); ?>/movie.php?slug=<?php echo urlencode(isset($m['slug']) ? $m['slug'] : ''); ?>" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?php e($adminUrl); ?>/movie-edit.php?id=<?php echo urlencode(isset($m['id']) ? $m['id'] : ''); ?>" class="btn-admin btn-admin-outline btn-admin-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?php e($adminUrl); ?>/movie-delete.php" style="display:inline;" onsubmit="return confirm('Delete movie '<?php e(isset($m['title']) ? $m['title'] : 'Untitled'); ?>'?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($m['id']) ? $m['id'] : '', ENT_QUOTES); ?>">
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
