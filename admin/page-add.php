<?php
// BDMovieHub - Admin Page Add
require_once __DIR__ . '/../config.php';
$adminPage = 'pages';
$pageTitle = 'Add Page';
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
        $pages = getData(FILE_PAGES);
        if (getBySlug($pages, $slug)) {
            $slug = $slug . '-' . substr(md5(time()), 0, 4);
        }
        $newPage = array(
            'id'         => generateId($pages, 'pg'),
            'title'      => $title,
            'slug'       => $slug,
            'content'    => $content,
            'status'     => $status,
            'created_at' => date('Y-m-d'),
        );
        $pages[] = $newPage;
        if (saveData(FILE_PAGES, $pages)) {
            setFlash('success', 'Page created.');
            adminRedirect('pages.php');
        } else {
            $errors[] = 'Failed to save.';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="font-size:20px;">Add New Page</h2>
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

    <form method="POST" action="<?php e($adminUrl); ?>/page-add.php">
        <?php echo csrfField(); ?>
        <div class="form-row">
            <div class="form-group">
                <label>Title <span style="color:#e74c3c;">*</span></label>
                <input type="text" name="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug'], ENT_QUOTES) : ''; ?>" placeholder="auto-generated">
            </div>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <?php $s = isset($_POST['status']) ? $_POST['status'] : 'published'; ?>
                <option value="published" <?php echo $s === 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="draft" <?php echo $s === 'draft' ? 'selected' : ''; ?>>Draft</option>
            </select>
        </div>

        <div class="form-group">
            <label>Content (HTML allowed)</label>
            <textarea name="content" style="min-height:300px; font-family: 'Courier New', monospace; font-size:13px;"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content'], ENT_QUOTES) : ''; ?></textarea>
            <div class="hint">You can use HTML tags: &lt;p&gt;, &lt;h1&gt;-&lt;h6&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;a href="..."&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;img src="..."&gt;</div>
        </div>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fas fa-save"></i> Save Page</button>
            <a href="<?php e($adminUrl); ?>/pages.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
