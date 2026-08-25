<?php
// BDMovieHub - Admin Page Edit
require_once __DIR__ . '/../config.php';
$adminPage = 'pages';
$pageTitle = 'Edit Page';

$id = isset($_GET['id']) ? $_GET['id'] : '';
$pages = getData(FILE_PAGES);
$page = getById($pages, $id);

if (!$page) {
    setFlash('error', 'Page not found.');
    adminRedirect('pages.php');
}

$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $title   = isset($_POST['title']) ? trim($_POST['title']) : '';
    $slug    = isset($_POST['slug']) ? trim($_POST['slug']) : '';
    $content = isset($_POST['content']) ? $_POST['content'] : '';
    $status  = isset($_POST['status']) ? $_POST['status'] : 'published';

    if ($title === '') { $errors[] = 'Title is required.'; }
    if ($slug === '') { $slug = slugify($title); }
    else { $slug = slugify($slug); }

    if (empty($errors)) {
        foreach ($pages as &$p) {
            if ($p['id'] === $id) {
                $p['title']   = $title;
                $p['slug']    = $slug;
                $p['content'] = $content;
                $p['status']  = $status;
                break;
            }
        }
        unset($p);
        if (saveData(FILE_PAGES, $pages)) {
            setFlash('success', 'Page updated.');
            adminRedirect('pages.php');
        } else {
            $errors[] = 'Failed to save.';
        }
    }
    $page = array_merge($page, $_POST);
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Edit Page</h2>
        <a href="<?php e($adminUrl); ?>/pages.php" class="btn-admin btn-admin-outline"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div style="background:rgba(231,76,60,0.1); border:1px solid #e74c3c; color:#e74c3c; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?php e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php e($adminUrl); ?>/page-edit.php?id=<?php echo urlencode($id); ?>">
        <?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group">
                <label>Title <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="title" required value="<?php e(isset($page['title']) ? $page['title'] : ''); ?>">
            </div>
            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" value="<?php e(isset($page['slug']) ? $page['slug'] : ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php $s = isset($page['status']) ? $page['status'] : 'published'; ?>
                <option value="published" <?php echo $s === 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo $s === 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
        </div>

        <div class="form-group">
            <label>Content (HTML allowed)</label>
            <textarea name="content" style="min-height:300px; font-family: 'Courier New', monospace; font-size:13px;"><?php e(isset($page['content']) ? $page['content'] : ''); ?></textarea>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Update Page</button>
            <a href="<?php e($adminUrl); ?>/pages.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
