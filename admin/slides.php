<?php
// BDMovieHub - Admin Slides Manager (Hero Slider)
require_once __DIR__ . '/../config.php';
$adminPage = 'slides';
$pageTitle = 'Hero Slides';

$slides = getData(FILE_SLIDES);

// Sort by order if exists, otherwise by id
usort($slides, function ($a, $b) {
    $oa = isset($a['order']) ? intval($a['order']) : 999;
    $ob = isset($b['order']) ? intval($b['order']) : 999;
    return $oa - $ob;
});

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Hero Slides</h2>
        <a href="<?php e($adminUrl); ?>/slide-add.php" class="btn-admin btn-admin-primary"><i class="fas fa-plus"></i> Add Slide</a>
    </div>

    <?php if (empty($slides)): ?>
        <p style="color:#a0a0b8; text-align:center; padding:20px;">No slides yet. <a href="<?php e($adminUrl); ?>/slide-add.php" style="color:#469AFF;">Add one</a>.</p>
    <?php else: ?>
        <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr><th>Order</th><th>Preview</th><th>Title</th><th>URL</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($slides as $s): ?>
                <tr>
                    <td><?php e(isset($s['order']) ? $s['order'] : '-'); ?></td>
                    <td>
                        <?php $img = isset($s['image']) ? $s['image'] : ''; ?>
                        <?php if (!empty($img)): ?>
                            <img src="<?php echo htmlspecialchars($img, ENT_QUOTES); ?>" style="width:80px; height:32px; object-fit:cover; border-radius:4px;" alt="">
                        <?php else: ?>
                            <span style="color:#6b6b80;">No image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php e(isset($s['title']) ? $s['title'] : '-'); ?></td>
                    <td><code style="font-size:11px; color:#a0a0b8;"><?php e(isset($s['url']) ? $s['url'] : ''); ?></code></td>
                    <td>
                        <div class="table-actions">
                            <a href="<?php e($adminUrl); ?>/slide-edit.php?id=<?php echo htmlspecialchars(isset($s['id']) ? $s['id'] : '', ENT_QUOTES); ?>" class="btn-admin btn-admin-primary btn-admin-sm" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?php e($adminUrl); ?>/slide-delete.php" style="display:inline;" onsubmit="return confirm('Delete this slide?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($s['id']) ? $s['id'] : '', ENT_QUOTES); ?>">
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
